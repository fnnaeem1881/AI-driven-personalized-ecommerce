from fastapi import FastAPI, BackgroundTasks
from pydantic import BaseModel
from typing import Optional, Dict, Any
import redis
import clickhouse_connect
import json
import asyncio
from datetime import datetime
import pytz  # for timezone handling

dhaka_tz = pytz.timezone("Asia/Dhaka")

app = FastAPI(title="Event Collection Service")

redis_client = redis.Redis(
    host="localhost",
    port=6379,
    decode_responses=True
)

ch_client = clickhouse_connect.get_client(
    host="syym5je3fm.asia-southeast1.gcp.clickhouse.cloud",
    user="default",
    password="oR0n3ljHAc_e7",
    secure=True
)

print("Result clickhouse_connect:", ch_client.query("SELECT 1").result_set[0][0])

class Event(BaseModel):
    event_id: str
    event_type: str
    user_id: Optional[int] = None
    session_id: str
    timestamp: str  # ISO string
    data: Dict[str, Any]
    ip_address: str
    user_agent: str

def init_clickhouse():
    ch_client.command("""
        CREATE TABLE IF NOT EXISTS events (
            event_id String,
            event_type LowCardinality(String),
            user_id Nullable(UInt32),
            session_id String,
            timestamp DateTime64(3),
            product_id Nullable(UInt32),
            category_id Nullable(UInt32),
            price Nullable(Decimal(10,2)),
            quantity Nullable(UInt16),
            cart_total Nullable(Decimal(10,2)),
            product_name Nullable(String),
            ip_address IPv4,
            user_agent String,
            date Date DEFAULT toDate(toTimeZone(timestamp,'Asia/Dhaka'))
        )
        ENGINE = MergeTree()
        PARTITION BY toYYYYMM(date)
        ORDER BY (event_type, date, timestamp)
        SETTINGS index_granularity = 8192
    """)
    print("✓ ClickHouse table initialized")

@app.on_event("startup")
async def startup_event():
    init_clickhouse()
    asyncio.create_task(process_queue_worker())

async def process_queue_worker():
    print("🔄 Queue worker started")
    while True:
        try:
            event_json = await asyncio.to_thread(redis_client.lpop, "events:queue")
            if event_json:
                event_data = json.loads(event_json)
                await store_event(Event(**event_data))
            else:
                await asyncio.sleep(0.1)
        except Exception as e:
            print(f"Queue worker error: {e}")
            await asyncio.sleep(1)

async def store_event(event: Event):
    try:
        # Convert incoming timestamp to Dhaka timezone
        ts = datetime.fromisoformat(event.timestamp.replace("Z","+00:00"))
        ts_dhaka = ts.astimezone(dhaka_tz)

        row = [
            event.event_id,
            event.event_type,
            event.user_id,
            event.session_id,
            ts_dhaka,
            event.data.get("product_id"),
            event.data.get("category_id"),
            event.data.get("price"),
            event.data.get("quantity"),
            event.data.get("cart_total"),
            (event.data.get("product_name") or "")[:200],
            event.ip_address,
            event.user_agent[:500],
        ]

        # Create a separate client instance for this thread
        def insert_row():
            client = clickhouse_connect.get_client(
                host='syym5je3fm.asia-southeast1.gcp.clickhouse.cloud',
                user='default',
                password='oR0n3ljHAc_e7',
                secure=True
            )
            client.insert(
                "events",
                [row],
                column_names=[
                    "event_id",
                    "event_type",
                    "user_id",
                    "session_id",
                    "timestamp",
                    "product_id",
                    "category_id",
                    "price",
                    "quantity",
                    "cart_total",
                    "product_name",
                    "ip_address",
                    "user_agent",
                ]
            )

        await asyncio.to_thread(insert_row)

        print(f"✓ Stored event: {event.event_type}")

    except Exception as e:
        print(f"Error storing event: {e}")


@app.post("/collect")
async def collect_event(event: Event, background_tasks: BackgroundTasks):
    background_tasks.add_task(store_event, event)
    return {"status":"accepted", "event_id": event.event_id}

@app.get("/stats")
async def get_stats():
    # Use Dhaka timezone in query
    query = """
        SELECT
            event_type,
            COUNT(*) AS count,
            COUNT(DISTINCT user_id) AS unique_users,
            COUNT(DISTINCT session_id) AS unique_sessions
        FROM events
        WHERE timestamp >= now('Asia/Dhaka') - INTERVAL 7 DAY
        GROUP BY event_type
        ORDER BY count DESC
    """
    result = await asyncio.to_thread(ch_client.query, query)
    return {
        "stats": [
            {
                "event_type": r[0],
                "count": r[1],
                "unique_users": r[2],
                "unique_sessions": r[3]
            }
            for r in result.result_rows
        ]
    }

@app.get("/health")
async def health_check():
    redis_ok = await asyncio.to_thread(redis_client.ping)
    ch_ok = await asyncio.to_thread(ch_client.ping)
    return {"status":"healthy","redis":redis_ok,"clickhouse":ch_ok}

# ------------------ Local Run ------------------
if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=8000, reload=True)

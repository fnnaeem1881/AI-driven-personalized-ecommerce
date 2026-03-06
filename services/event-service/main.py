import sys
import io
# Force UTF-8 stdout on Windows to allow Unicode output
if sys.stdout.encoding and sys.stdout.encoding.lower() != 'utf-8':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

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
    host="reiq2ms4gj.germanywestcentral.azure.clickhouse.cloud",
    user="default",
    password="2Mi0VyELOs_IP",
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
    print("[Queue] Worker started")
    _redis_unavailable_logged = False
    while True:
        try:
            event_json = await asyncio.to_thread(redis_client.lpop, "events:queue")
            if event_json:
                _redis_unavailable_logged = False
                event_data = json.loads(event_json)
                await store_event(Event(**event_data))
            else:
                await asyncio.sleep(0.5)
        except Exception as e:
            if not _redis_unavailable_logged:
                print(f"[Queue] Redis unavailable - events via direct POST only. ({e})")
                _redis_unavailable_logged = True
            await asyncio.sleep(5)  # backoff when Redis is down

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

        # Reuse the global ch_client to avoid SSL issues creating new connections in threads
        col_names = [
            "event_id", "event_type", "user_id", "session_id", "timestamp",
            "product_id", "category_id", "price", "quantity", "cart_total",
            "product_name", "ip_address", "user_agent",
        ]
        ch_client.insert("events", [row], column_names=col_names)

        print(f"[OK] Stored event: {event.event_type}")

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
    redis_ok = False
    ch_ok = False
    try:
        redis_ok = await asyncio.to_thread(redis_client.ping)
    except Exception:
        pass
    try:
        ch_ok = await asyncio.to_thread(ch_client.ping)
    except Exception:
        pass
    status = "healthy" if ch_ok else "degraded"
    return {"status": status, "redis": redis_ok, "clickhouse": ch_ok}


# ═══════════════════════════════════════════════════════════
#  ANALYTICS ENDPOINTS  (ClickHouse data for admin panel)
# ═══════════════════════════════════════════════════════════

@app.get("/analytics/overview")
async def analytics_overview(days: int = 30):
    """Total event counts, unique users, sessions for the given period."""
    query = f"""
        SELECT
            COUNT(*)                            AS total_events,
            COUNT(DISTINCT user_id)             AS unique_users,
            COUNT(DISTINCT session_id)          AS unique_sessions,
            countIf(event_type='view_product')  AS views,
            countIf(event_type='add_to_cart')   AS cart_adds,
            countIf(event_type='purchase')      AS purchases,
            countIf(event_type='search')        AS searches,
            countIf(event_type='wishlist_toggle') AS wishlists,
            MIN(timestamp)                      AS first_event,
            MAX(timestamp)                      AS last_event
        FROM events
        WHERE timestamp >= now('Asia/Dhaka') - INTERVAL {int(days)} DAY
    """
    result = await asyncio.to_thread(ch_client.query, query)
    r = result.result_rows[0] if result.result_rows else [0]*10
    return {
        "days": days,
        "total_events":    int(r[0]),
        "unique_users":    int(r[1]),
        "unique_sessions": int(r[2]),
        "views":           int(r[3]),
        "cart_adds":       int(r[4]),
        "purchases":       int(r[5]),
        "searches":        int(r[6]),
        "wishlists":       int(r[7]),
        "first_event":     str(r[8]) if r[8] else None,
        "last_event":      str(r[9]) if r[9] else None,
    }


@app.get("/analytics/timeline")
async def analytics_timeline(days: int = 30):
    """Events per day broken down by event_type for the given period."""
    query = f"""
        SELECT
            toDate(toTimeZone(timestamp, 'Asia/Dhaka')) AS day,
            event_type,
            COUNT(*) AS cnt
        FROM events
        WHERE timestamp >= now('Asia/Dhaka') - INTERVAL {int(days)} DAY
        GROUP BY day, event_type
        ORDER BY day ASC, cnt DESC
    """
    result = await asyncio.to_thread(ch_client.query, query)
    rows = [{"day": str(r[0]), "event_type": r[1], "count": int(r[2])} for r in result.result_rows]
    return {"days": days, "rows": rows}


@app.get("/analytics/products")
async def analytics_products(days: int = 30, limit: int = 20):
    """Top products by views, cart adds, and purchases from ClickHouse."""
    query = f"""
        SELECT
            product_id,
            any(product_name)                       AS product_name,
            countIf(event_type='view_product')      AS views,
            countIf(event_type='add_to_cart')       AS cart_adds,
            countIf(event_type='purchase')          AS purchases,
            COUNT(DISTINCT user_id)                 AS unique_users,
            COUNT(*)                                AS total_events
        FROM events
        WHERE timestamp >= now('Asia/Dhaka') - INTERVAL {int(days)} DAY
          AND product_id IS NOT NULL
        GROUP BY product_id
        ORDER BY views DESC
        LIMIT {int(limit)}
    """
    result = await asyncio.to_thread(ch_client.query, query)
    return {
        "days": days,
        "products": [
            {
                "product_id":    int(r[0]) if r[0] else None,
                "product_name":  r[1] or "",
                "views":         int(r[2]),
                "cart_adds":     int(r[3]),
                "purchases":     int(r[4]),
                "unique_users":  int(r[5]),
                "total_events":  int(r[6]),
            }
            for r in result.result_rows
        ]
    }


@app.get("/analytics/users")
async def analytics_users(days: int = 30, limit: int = 20):
    """Top users by activity from ClickHouse."""
    query = f"""
        SELECT
            user_id,
            COUNT(*)                                AS total_events,
            countIf(event_type='view_product')      AS views,
            countIf(event_type='add_to_cart')       AS cart_adds,
            countIf(event_type='purchase')          AS purchases,
            COUNT(DISTINCT session_id)              AS sessions,
            COUNT(DISTINCT product_id)              AS unique_products,
            MIN(timestamp)                          AS first_seen,
            MAX(timestamp)                          AS last_seen
        FROM events
        WHERE timestamp >= now('Asia/Dhaka') - INTERVAL {int(days)} DAY
          AND user_id IS NOT NULL
        GROUP BY user_id
        ORDER BY total_events DESC
        LIMIT {int(limit)}
    """
    result = await asyncio.to_thread(ch_client.query, query)
    return {
        "days": days,
        "users": [
            {
                "user_id":         int(r[0]),
                "total_events":    int(r[1]),
                "views":           int(r[2]),
                "cart_adds":       int(r[3]),
                "purchases":       int(r[4]),
                "sessions":        int(r[5]),
                "unique_products": int(r[6]),
                "first_seen":      str(r[7]) if r[7] else None,
                "last_seen":       str(r[8]) if r[8] else None,
            }
            for r in result.result_rows
        ]
    }


@app.get("/analytics/recent")
async def analytics_recent(limit: int = 50, offset: int = 0, event_type: str = ""):
    """Paginated list of recent raw events from ClickHouse."""
    type_filter = f"AND event_type = '{event_type}'" if event_type else ""
    query = f"""
        SELECT
            event_id,
            event_type,
            user_id,
            session_id,
            toTimeZone(timestamp, 'Asia/Dhaka') AS ts,
            product_id,
            product_name,
            price,
            quantity,
            cart_total,
            ip_address
        FROM events
        WHERE 1=1 {type_filter}
        ORDER BY timestamp DESC
        LIMIT {int(limit)} OFFSET {int(offset)}
    """
    count_query = f"SELECT COUNT(*) FROM events WHERE 1=1 {type_filter}"
    result       = await asyncio.to_thread(ch_client.query, query)
    count_result = await asyncio.to_thread(ch_client.query, count_query)
    total = int(count_result.result_rows[0][0]) if count_result.result_rows else 0
    return {
        "total":  total,
        "limit":  limit,
        "offset": offset,
        "events": [
            {
                "event_id":    r[0],
                "event_type":  r[1],
                "user_id":     int(r[2]) if r[2] else None,
                "session_id":  r[3][:12] + "..." if r[3] and len(r[3]) > 12 else r[3],
                "timestamp":   str(r[4]),
                "product_id":  int(r[5]) if r[5] else None,
                "product_name":r[6] or "",
                "price":       float(r[7]) if r[7] else None,
                "quantity":    int(r[8]) if r[8] else None,
                "cart_total":  float(r[9]) if r[9] else None,
                "ip_address":  str(r[10]) if r[10] else "",
            }
            for r in result.result_rows
        ]
    }


@app.get("/analytics/event-types")
async def analytics_event_types():
    """All distinct event types with counts and breakdown."""
    query = """
        SELECT
            event_type,
            COUNT(*) AS total,
            COUNT(DISTINCT user_id) AS unique_users,
            COUNT(DISTINCT session_id) AS unique_sessions,
            COUNT(DISTINCT product_id) AS unique_products,
            MIN(timestamp) AS first_seen,
            MAX(timestamp) AS last_seen
        FROM events
        GROUP BY event_type
        ORDER BY total DESC
    """
    result = await asyncio.to_thread(ch_client.query, query)
    return {
        "event_types": [
            {
                "event_type":      r[0],
                "total":           int(r[1]),
                "unique_users":    int(r[2]),
                "unique_sessions": int(r[3]),
                "unique_products": int(r[4]),
                "first_seen":      str(r[5]) if r[5] else None,
                "last_seen":       str(r[6]) if r[6] else None,
            }
            for r in result.result_rows
        ]
    }


# ------------------ Local Run ------------------
if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=8000, reload=True)

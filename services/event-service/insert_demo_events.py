import requests
import random
import uuid
from datetime import datetime, timedelta
import pytz
import clickhouse_connect

# ------------------ Config ------------------
API_URL = "http://127.0.0.1:8000/collect"  # FastAPI endpoint
dhaka_tz = pytz.timezone("Asia/Dhaka")

# ------------------ Demo Data Parameters ------------------
event_types = ["product_view", "add_to_cart", "purchase"]
user_count = 10
session_count = 20
product_count = 15
category_count = 5
user_agents = ["Chrome", "Firefox", "Safari"]

# ------------------ FastAPI Insert ------------------
def insert_via_api(NUM_EVENTS=100):
    for i in range(NUM_EVENTS):
        event_type = random.choice(event_types)
        user_id = random.randint(1, user_count)
        session_id = f"sess_demo_{random.randint(1, session_count)}"
        product_id = random.randint(101, 101 + product_count - 1)
        category_id = random.randint(1, category_count)
        price = round(random.uniform(100, 600), 2)
        quantity = random.randint(1, 3)
        cart_total = round(price * quantity, 2)
        product_name = f"Demo Product {product_id}"
        ip_address = f"192.168.1.{random.randint(1,254)}"
        user_agent = random.choice(user_agents)

        # Random timestamp in last 90 days
        delta_days = random.randint(0, 90)
        delta_seconds = random.randint(0, 24*3600)
        timestamp = datetime.now(dhaka_tz) - timedelta(days=delta_days, seconds=delta_seconds)
        timestamp_iso = timestamp.isoformat()

        payload = {
            "event_id": f"evt_demo_{uuid.uuid4().hex}",
            "event_type": event_type,
            "user_id": user_id,
            "session_id": session_id,
            "timestamp": timestamp_iso,
            "data": {
                "product_id": product_id,
                "category_id": category_id,
                "price": price,
                "quantity": quantity,
                "cart_total": cart_total,
                "product_name": product_name
            },
            "ip_address": ip_address,
            "user_agent": user_agent
        }

        try:
            response = requests.post(API_URL, json=payload)
            if response.status_code == 200:
                print(f"✓ Event {i+1}/{NUM_EVENTS} inserted via API: {event_type}")
            else:
                print(f"⚠️ Failed {i+1}: {response.text}")
        except Exception as e:
            print(f"⚠️ Error {i+1}: {e}")

# ------------------ Direct ClickHouse Insert ------------------
def insert_demo_events(n=100):
    ch_client = clickhouse_connect.get_client(
        host='syym5je3fm.asia-southeast1.gcp.clickhouse.cloud',
        user='default',
        password='oR0n3ljHAc_e7',
        secure=True
    )

    for i in range(n):
        ts = datetime.now(pytz.utc) - timedelta(days=random.randint(0, 90), hours=random.randint(0,23))
        ts = ts.astimezone(dhaka_tz)
        event_type = random.choice(event_types)
        product_id = random.randint(1, 20)
        user_id = random.randint(1, 10)
        session_id = f'session_{random.randint(1, 50)}'
        price = round(random.uniform(50, 200), 2)
        quantity = random.randint(1, 5)
        cart_total = price * quantity if event_type != 'product_view' else None
        product_name = f'Product {product_id}'
        ip_address = f'192.168.1.{random.randint(1,255)}'
        user_agent = 'Mozilla/5.0 Demo'

        row = [
            f'evt_{i}',
            event_type,
            user_id,
            session_id,
            ts,
            product_id,
            random.randint(1,5),  # category_id
            price,
            quantity,
            cart_total,
            product_name,
            ip_address,
            user_agent
        ]
        ch_client.insert("events", [row], column_names=[
            "event_id","event_type","user_id","session_id","timestamp","product_id",
            "category_id","price","quantity","cart_total","product_name","ip_address","user_agent"
        ])
    print(f"Inserted {n} demo events directly into ClickHouse.")

# ------------------ Run ------------------
if __name__ == "__main__":
    # Option 1: via API (goes through Redis queue)
    insert_via_api(500)

    # Option 2: direct ClickHouse insert (bulk, faster)
    insert_demo_events(2000)

import requests
import random
import uuid
from datetime import datetime, timedelta
import pytz

# ------------------ Config ------------------
API_URL = "http://127.0.0.1:8000/collect"  # FastAPI endpoint
NUM_EVENTS = 100  # Number of demo events to insert
dhaka_tz = pytz.timezone("Asia/Dhaka")

# ------------------ Demo Data ------------------
event_types = ["product_view", "add_to_cart", "purchase"]
user_count = 10
session_count = 20
product_count = 15
category_count = 5
user_agents = ["Chrome", "Firefox", "Safari"]

# ------------------ Insert Demo Events ------------------
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
            print(f"✓ Event {i+1}/{NUM_EVENTS} inserted: {event_type} at {timestamp_iso}")
        else:
            print(f"⚠️ Failed {i+1}: {response.text}")
    except Exception as e:
        print(f"⚠️ Error {i+1}: {e}")

print(f"\n✅ {NUM_EVENTS} demo events inserted successfully!")

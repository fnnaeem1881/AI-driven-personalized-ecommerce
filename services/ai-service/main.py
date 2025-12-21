from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import Optional
import clickhouse_connect
import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
import joblib
import redis
import json


app = FastAPI(title="AI/ML Service")


ch_client = clickhouse_connect.get_client(
    host='syym5je3fm.asia-southeast1.gcp.clickhouse.cloud',
    user='default',
    password='oR0n3ljHAc_e7',
    secure=True
)
print("✅ ClickHouse connected")

redis_client = redis.Redis(host='localhost', port=6379, decode_responses=True)
print("✅ Redis connected")


try:
    cart_model = joblib.load("cart_abandonment_model.pkl")
    cart_scaler = joblib.load("cart_abandonment_scaler.pkl")
    print("✅ Cart abandonment ML model loaded")
except Exception as e:
    cart_model = None
    cart_scaler = None
    print("❌ Failed to load ML model:", e)

class RecommendationRequest(BaseModel):
    user_id: int
    limit: int = 10

class SimilarProductRequest(BaseModel):
    product_id: int
    limit: int = 6

class CartAbandonmentRequest(BaseModel):
    session_id: str
    user_id: Optional[int] = None


@app.post("/recommendations/collaborative")
async def collaborative_filtering(req: RecommendationRequest):

    cache_key = f"collab:{req.user_id}:{req.limit}"
    cached = redis_client.get(cache_key)
    if cached:
        return json.loads(cached)

    query = """
        SELECT 
            user_id,
            product_id,
            SUM(
                CASE 
                    WHEN event_type = 'purchase' THEN 3
                    WHEN event_type = 'add_to_cart' THEN 2
                    WHEN event_type = 'product_view' THEN 1
                    ELSE 0
                END
            ) AS weighted_score
        FROM events
        WHERE user_id IS NOT NULL
        AND timestamp >= now() - INTERVAL 60 DAY
        GROUP BY user_id, product_id
        HAVING weighted_score >= 2
    """

    df = ch_client.query_df(query)

    if df.empty or req.user_id not in df["user_id"].values:
        return {"recommendations": []}

    matrix = df.pivot_table(
        index="user_id",
        columns="product_id",
        values="weighted_score",
        fill_value=0
    )

    if req.user_id not in matrix.index:
        return {"recommendations": []}

    user_vector = matrix.loc[req.user_id].values.reshape(1, -1)
    similarities = cosine_similarity(user_vector, matrix.values)[0]

    similar_indices = np.argsort(similarities)[::-1][1:11]

    recommendations = {}
    user_products = set(matrix.columns[matrix.loc[req.user_id] > 0])

    for idx in similar_indices:
        sim_user = matrix.index[idx]
        for pid, score in matrix.loc[sim_user].items():
            if pid not in user_products and score > 0:
                recommendations[pid] = recommendations.get(pid, 0) + score * similarities[idx]

    top = sorted(recommendations.items(), key=lambda x: x[1], reverse=True)[:req.limit]

    result = {
        "recommendations": [
            {"product_id": int(pid), "score": round(float(score), 4)}
            for pid, score in top
        ]
    }

    redis_client.setex(cache_key, 1800, json.dumps(result))
    return result

@app.post("/recommendations/similar")
async def similar_products(req: SimilarProductRequest):

    cache_key = f"similar:{req.product_id}:{req.limit}"
    cached = redis_client.get(cache_key)
    if cached:
        return json.loads(cached)

    query = f"""
        WITH sessions AS (
            SELECT DISTINCT session_id
            FROM events
            WHERE product_id = {req.product_id}
            AND event_type = 'product_view'
            AND timestamp >= now() - INTERVAL 30 DAY
        )
        SELECT
            product_id,
            COUNT(DISTINCT session_id) AS co_views
        FROM events
        WHERE session_id IN (SELECT session_id FROM sessions)
        AND product_id != {req.product_id}
        AND event_type = 'product_view'
        GROUP BY product_id
        ORDER BY co_views DESC
        LIMIT {req.limit}
    """

    df = ch_client.query_df(query)

    result = {
        "similar_products": [
            {"product_id": int(row.product_id), "score": int(row.co_views)}
            for row in df.itertuples()
        ]
    }

    redis_client.setex(cache_key, 3600, json.dumps(result))
    return result


@app.post("/predictions/cart-abandonment")
async def predict_cart_abandonment(req: CartAbandonmentRequest):

    if cart_model is None:
        raise HTTPException(500, "ML model not loaded")

    query = f"""
        SELECT
            COUNT(*) AS event_count,
            SUM(event_type = 'add_to_cart') AS cart_adds,
            SUM(event_type = 'product_view') AS views,
            SUM(event_type = 'purchase') AS purchases,
            MAX(cart_total) AS cart_value,
            AVG(price) AS avg_price,
            MAX(price) AS max_price,
            dateDiff('second', MIN(timestamp), MAX(timestamp)) AS duration,
            toHour(MIN(timestamp)) AS start_hour,
            toDayOfWeek(MIN(timestamp)) AS day_of_week
        FROM events
        WHERE session_id = '{req.session_id}'
    """

    df = ch_client.query_df(query)

    if df.empty or df["cart_adds"].iloc[0] == 0:
        return {"risk_level": "low", "probability": 0.0}

    X = df[[
        "event_count",
        "cart_adds",
        "views",
        "cart_value",
        "avg_price",
        "max_price",
        "duration",
        "start_hour",
        "day_of_week"
    ]].fillna(0)

    X_scaled = cart_scaler.transform(X)

    prob_convert = cart_model.predict_proba(X_scaled)[0][1]
    prob_abandon = round(1 - prob_convert, 4)
    # print(X.to_dict())

    if prob_abandon >= 0.5:
        risk = "high"
    elif prob_abandon >= 0.25:
        risk = "medium"
    else:
        risk = "low"

    return {
        "session_id": req.session_id,
        "risk_level": risk,
        "probability": prob_abandon
    }


@app.get("/health")
async def health():
    return {
        "status": "healthy",
        "redis": redis_client.ping(),
        "clickhouse": ch_client.ping(),
        "model_loaded": cart_model is not None
    }


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)

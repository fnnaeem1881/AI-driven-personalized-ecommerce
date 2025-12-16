from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional
import clickhouse_connect
import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
import joblib
import redis
import json
from train import train_product_recommendation_matrix,train_cart_abandonment_model
from datetime import datetime, timedelta

app = FastAPI(title="AI/ML Service")

# Connections
ch_client = clickhouse_connect.get_client(
        host='syym5je3fm.asia-southeast1.gcp.clickhouse.cloud',
        user='default',
        password='oR0n3ljHAc_e7',
        secure=True
    )
print("Result clickhouse_connect:", ch_client.query("SELECT 1").result_set[0][0])

redis_client = redis.Redis(host='localhost', port=6379, decode_responses=True)
print("Result redis_client:", ch_client.query("SELECT 1").result_set[0][0])

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
    """Collaborative filtering: Users who liked X also liked Y"""
    
    cache_key = f"collab:{req.user_id}:{req.limit}"
    cached = redis_client.get(cache_key)
    if cached:
        return json.loads(cached)
    
    # Get user-product interaction matrix (last 60 days)
    query = """
        SELECT 
            user_id,
            product_id,
            COUNT(*) as interactions,
            SUM(CASE WHEN event_type = 'purchase' THEN 3
                     WHEN event_type = 'add_to_cart' THEN 2
                     WHEN event_type = 'product_view' THEN 1
                     ELSE 0 END) as weighted_score
        FROM events
        WHERE event_type IN ('product_view', 'add_to_cart', 'purchase')
        AND user_id IS NOT NULL
        AND timestamp >= now() - INTERVAL 60 DAY
        GROUP BY user_id, product_id
        HAVING interactions >= 2
    """
    
    df = ch_client.query_df(query)
    
    if df.empty or req.user_id not in df['user_id'].values:
        return {"recommendations": []}
    
    # Create user-product matrix
    matrix = df.pivot_table(
        index='user_id',
        columns='product_id',
        values='weighted_score',
        fill_value=0
    )
    
    # Find similar users
    if req.user_id not in matrix.index:
        return {"recommendations": []}
    
    user_vector = matrix.loc[req.user_id].values.reshape(1, -1)
    similarities = cosine_similarity(user_vector, matrix.values)[0]
    
    # Get top similar users (excluding self)
    similar_user_indices = np.argsort(similarities)[::-1][1:11]
    
    # Aggregate recommendations from similar users
    recommendations = {}
    user_products = set(matrix.columns[matrix.loc[req.user_id] > 0])
    
    for idx in similar_user_indices:
        similar_user_id = matrix.index[idx]
        user_prods = matrix.loc[similar_user_id]
        
        for prod_id, score in user_prods[user_prods > 0].items():
            if prod_id not in user_products:
                recommendations[prod_id] = recommendations.get(prod_id, 0) + score * similarities[idx]
    
    # Sort and limit
    sorted_recs = sorted(recommendations.items(), key=lambda x: x[1], reverse=True)[:req.limit]
    
    result = {
        "recommendations": [
            {"product_id": int(pid), "score": float(score)}
            for pid, score in sorted_recs
        ]
    }
    
    # Cache for 30 minutes
    redis_client.setex(cache_key, 1800, json.dumps(result))
    
    return result

@app.post("/recommendations/similar")
async def similar_products(req: SimilarProductRequest):
    """Content-based: Similar products based on category and price"""
    
    cache_key = f"similar:{req.product_id}:{req.limit}"
    cached = redis_client.get(cache_key)
    if cached:
        return json.loads(cached)
    
    # Get products that are frequently viewed together
    query = f"""
        WITH target_sessions AS (
            SELECT DISTINCT session_id
            FROM events
            WHERE product_id = {req.product_id}
            AND event_type = 'product_view'
            AND timestamp >= now() - INTERVAL 30 DAY
        )
        SELECT 
            product_id,
            COUNT(DISTINCT session_id) as co_views,
            AVG(price) as avg_price
        FROM events
        WHERE session_id IN (SELECT session_id FROM target_sessions)
        AND product_id != {req.product_id}
        AND event_type = 'product_view'
        GROUP BY product_id
        ORDER BY co_views DESC
        LIMIT {req.limit}
    """
    
    result_df = ch_client.query_df(query)
    
    result = {
        "similar_products": [
            {"product_id": int(row['product_id']), "score": float(row['co_views'])}
            for _, row in result_df.iterrows()
        ]
    }
    
    # Cache for 1 hour
    redis_client.setex(cache_key, 3600, json.dumps(result))
    
    return result

@app.post("/predictions/cart-abandonment")
async def predict_abandonment(req: CartAbandonmentRequest):
    """Predict cart abandonment probability"""
    
    # Get session features
    query = f"""
        SELECT
            COUNT(*) as event_count,
            SUM(CASE WHEN event_type = 'add_to_cart' THEN 1 ELSE 0 END) as cart_adds,
            SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END) as product_views,
            MAX(cart_total) as cart_value,
            AVG(price) as avg_price,
            dateDiff('second', MIN(timestamp), MAX(timestamp)) as session_duration
        FROM events
        WHERE session_id = '{req.session_id}'
        AND timestamp >= now() - INTERVAL 2 HOUR
    """
    
    features_df = ch_client.query_df(query)
    
    if features_df.empty or features_df['cart_adds'].iloc[0] == 0:
        return {"risk_level": "low", "probability": 0.0}
    
    # Simple rule-based prediction (replace with trained model)
    cart_value = features_df['cart_value'].iloc[0] or 0
    session_duration = features_df['session_duration'].iloc[0] or 0
    cart_adds = features_df['cart_adds'].iloc[0]
    
    # Calculate risk score
    risk_score = 0.0
    
    if cart_value > 100:
        risk_score += 0.3
    if session_duration < 60:
        risk_score += 0.2
    if cart_adds > 3:
        risk_score += 0.3
    
    probability = min(risk_score, 0.95)
    
    if probability > 0.6:
        risk_level = "high"
    elif probability > 0.3:
        risk_level = "medium"
    else:
        risk_level = "low"
    
    return {
        "risk_level": risk_level,
        "probability": probability,
        "cart_value": float(cart_value)
    }

train_cart_abandonment_model()
train_product_recommendation_matrix()
@app.get("/health")
async def health():
    return {
        "status": "healthy",
        "redis": redis_client.ping(),
        "clickhouse": ch_client.ping()
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
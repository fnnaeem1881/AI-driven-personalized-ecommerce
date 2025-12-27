from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import Optional, List
import clickhouse_connect
import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
import joblib
import redis
import json
import os
from datetime import datetime

app = FastAPI(
    title="AI/ML Service for E-commerce",
    description="Complete ML service with recommendations, predictions, and analytics",
    version="1.0.0"
)

print(f"\n Starting AI/ML Service")
print(f" Working directory: {os.getcwd()}")
print(f" Started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")


# ClickHouse connection
try:
    ch_client = clickhouse_connect.get_client(
        host='syym5je3fm.asia-southeast1.gcp.clickhouse.cloud',
        user='default',
        password='oR0n3ljHAc_e7',
        secure=True
    )
    print(" ClickHouse connected")
except Exception as e:
    print(f" ClickHouse connection failed: {e}")
    ch_client = None

# Redis connection
try:
    redis_client = redis.Redis(host='localhost', port=6379, decode_responses=True)
    redis_client.ping()
    print(" Redis connected")
except Exception as e:
    print(f" Redis connection failed: {e}")
    redis_client = None


def load_model_safe(filepath, description):
    """Safely load a model with error handling"""
    if os.path.exists(filepath):
        try:
            model = joblib.load(filepath)
            print(f" {description} loaded from {filepath}")
            return model
        except Exception as e:
            print(f" Failed to load {description}: {e}")
            return None
    else:
        print(f" {description} not found at {filepath}")
        return None

def load_csv_safe(filepath, description):
    """Safely load a CSV with error handling"""
    if os.path.exists(filepath):
        try:
            df = pd.read_csv(filepath)
            print(f" {description} loaded from {filepath} ({len(df)} rows)")
            return df
        except Exception as e:
            print(f" Failed to load {description}: {e}")
            return None
    else:
        print(f" {description} not found at {filepath}")
        return None

# Load cart abandonment model
cart_model = load_model_safe("models/cart_abandonment_model.pkl", "Cart abandonment model")
cart_scaler = load_model_safe("models/cart_abandonment_scaler.pkl", "Cart abandonment scaler")

# Load user segmentation model
segment_model = load_model_safe("models/user_segmentation_model.pkl", "User segmentation model")
segment_scaler = load_model_safe("models/user_segmentation_scaler.pkl", "User segmentation scaler")
segment_names = load_model_safe("models/segment_names.pkl", "Segment names")

# Load data files
popular_products_df = load_csv_safe("data/popular_products.csv", "Popular products")
user_product_matrix_df = load_csv_safe("data/user_product_matrix.csv", "User-product matrix")
user_segments_df = load_csv_safe("data/user_segments.csv", "User segments")

print("\n" + "="*60)
print(" MODEL STATUS SUMMARY")
print("="*60)
print(f"Cart Abandonment Model: {'✓ Loaded' if cart_model else '✗ Not Loaded'}")
print(f"User Segmentation Model: {'✓ Loaded' if segment_model else '✗ Not Loaded'}")
print(f"Popular Products Data: {'✓ Loaded' if popular_products_df is not None else '✗ Not Loaded'}")
print(f"User-Product Matrix: {'✓ Loaded' if user_product_matrix_df is not None else '✗ Not Loaded'}")
print("="*60 + "\n")

class RecommendationRequest(BaseModel):
    user_id: int
    limit: int = 10

class SimilarProductRequest(BaseModel):
    product_id: int
    limit: int = 6

class CartAbandonmentRequest(BaseModel):
    session_id: str
    user_id: Optional[int] = None

class UserSegmentRequest(BaseModel):
    user_id: int

class PopularProductsRequest(BaseModel):
    limit: int = 10
    category_id: Optional[int] = None

class TrendingProductsRequest(BaseModel):
    limit: int = 10
    days: int = 7

@app.post("/recommendations/collaborative")
async def collaborative_filtering(req: RecommendationRequest):
    """
    Collaborative filtering recommendations based on similar users
    Returns products that similar users liked
    """
    if ch_client is None:
        raise HTTPException(503, "ClickHouse not available")
    
    # Check cache
    if redis_client:
        cache_key = f"collab:{req.user_id}:{req.limit}"
        try:
            cached = redis_client.get(cache_key)
            if cached:
                return json.loads(cached)
        except:
            pass

    try:
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
            return await get_popular_products_fallback(req.limit)

        matrix = df.pivot_table(
            index="user_id",
            columns="product_id",
            values="weighted_score",
            fill_value=0
        )

        if req.user_id not in matrix.index:
            return await get_popular_products_fallback(req.limit)

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
                {"product_id": int(pid), "score": round(float(score), 4), "reason": "collaborative"}
                for pid, score in top
            ]
        }

        # Cache result
        if redis_client:
            try:
                redis_client.setex(cache_key, 1800, json.dumps(result))
            except:
                pass

        return result
    
    except Exception as e:
        print(f"Error in collaborative filtering: {e}")
        return await get_popular_products_fallback(req.limit)


@app.post("/recommendations/similar")
async def similar_products(req: SimilarProductRequest):
    """
    Content-based recommendations: Products frequently viewed together
    """
    if ch_client is None:
        raise HTTPException(503, "ClickHouse not available")
    
    # Check cache
    if redis_client:
        cache_key = f"similar:{req.product_id}:{req.limit}"
        try:
            cached = redis_client.get(cache_key)
            if cached:
                return json.loads(cached)
        except:
            pass

    try:
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
                {"product_id": int(row.product_id), "score": int(row.co_views), "reason": "frequently_viewed_together"}
                for row in df.itertuples()
            ]
        }

        # Cache result
        if redis_client:
            try:
                redis_client.setex(cache_key, 3600, json.dumps(result))
            except:
                pass

        return result
    
    except Exception as e:
        print(f"Error in similar products: {e}")
        return {"similar_products": []}


@app.post("/recommendations/popular")
async def popular_products(req: PopularProductsRequest):
    """
    Get popular/trending products (for new users or cold-start)
    """
    if ch_client is None:
        # Fallback to loaded popular products
        if popular_products_df is not None:
            df = popular_products_df.head(req.limit)
            return {
                "popular_products": [
                    {
                        "product_id": int(row.product_id),
                        "purchases": int(row.purchases),
                        "unique_users": int(row.unique_users),
                        "views": int(row.views),
                        "reason": "popular_cached"
                    }
                    for row in df.itertuples()
                ]
            }
        raise HTTPException(503, "Service not available")
    
    # Check cache
    if redis_client:
        cache_key = f"popular:{req.limit}:{req.category_id or 'all'}"
        try:
            cached = redis_client.get(cache_key)
            if cached:
                return json.loads(cached)
        except:
            pass

    try:
        category_filter = f"AND category_id = {req.category_id}" if req.category_id else ""

        query = f"""
            SELECT 
                product_id,
                COUNT(DISTINCT user_id) as unique_users,
                SUM(CASE WHEN event_type = 'purchase' THEN 1 ELSE 0 END) as purchases,
                SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END) as views
            FROM events
            WHERE timestamp >= now() - INTERVAL 30 DAY
            AND product_id IS NOT NULL
            {category_filter}
            GROUP BY product_id
            ORDER BY purchases DESC, unique_users DESC, views DESC
            LIMIT {req.limit}
        """

        df = ch_client.query_df(query)

        result = {
            "popular_products": [
                {
                    "product_id": int(row.product_id),
                    "purchases": int(row.purchases),
                    "unique_users": int(row.unique_users),
                    "views": int(row.views),
                    "reason": "popular"
                }
                for row in df.itertuples()
            ]
        }

        # Cache result
        if redis_client:
            try:
                redis_client.setex(cache_key, 3600, json.dumps(result))
            except:
                pass

        return result
    
    except Exception as e:
        print(f"Error in popular products: {e}")
        raise HTTPException(500, str(e))


@app.post("/recommendations/trending")
async def trending_products(req: TrendingProductsRequest):
    """
    Get trending products based on recent activity surge
    """
    if ch_client is None:
        raise HTTPException(503, "ClickHouse not available")
    
    # Check cache
    if redis_client:
        cache_key = f"trending:{req.limit}:{req.days}"
        try:
            cached = redis_client.get(cache_key)
            if cached:
                return json.loads(cached)
        except:
            pass

    try:
        query = f"""
            WITH current_period AS (
                SELECT 
                    product_id,
                    COUNT(*) as current_views
                FROM events
                WHERE timestamp >= now() - INTERVAL {req.days} DAY
                AND event_type = 'product_view'
                GROUP BY product_id
            ),
            previous_period AS (
                SELECT 
                    product_id,
                    COUNT(*) as previous_views
                FROM events
                WHERE timestamp >= now() - INTERVAL {req.days * 2} DAY
                AND timestamp < now() - INTERVAL {req.days} DAY
                AND event_type = 'product_view'
                GROUP BY product_id
            )
            SELECT 
                c.product_id,
                c.current_views,
                COALESCE(p.previous_views, 0) as previous_views,
                (c.current_views - COALESCE(p.previous_views, 0)) as view_increase,
                CASE 
                    WHEN COALESCE(p.previous_views, 0) > 0 
                    THEN (c.current_views - p.previous_views) / p.previous_views * 100
                    ELSE 100
                END as growth_percentage
            FROM current_period c
            LEFT JOIN previous_period p ON c.product_id = p.product_id
            ORDER BY growth_percentage DESC, view_increase DESC
            LIMIT {req.limit}
        """

        df = ch_client.query_df(query)

        result = {
            "trending_products": [
                {
                    "product_id": int(row.product_id),
                    "current_views": int(row.current_views),
                    "growth_percentage": round(float(row.growth_percentage), 2),
                    "reason": "trending"
                }
                for row in df.itertuples()
            ]
        }

        # Cache result
        if redis_client:
            try:
                redis_client.setex(cache_key, 1800, json.dumps(result))
            except:
                pass

        return result
    
    except Exception as e:
        print(f"Error in trending products: {e}")
        raise HTTPException(500, str(e))


@app.post("/predictions/cart-abandonment")
async def predict_cart_abandonment(req: CartAbandonmentRequest):
    """
    Predict cart abandonment probability using ML model
    """
    if cart_model is None or cart_scaler is None:
        raise HTTPException(503, "ML model not loaded. Please run train.py first.")
    
    if ch_client is None:
        raise HTTPException(503, "ClickHouse not available")

    try:
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
            return {"risk_level": "low", "probability": 0.0, "reason": "no_cart_activity"}

        X = df[[
            "event_count", "cart_adds", "views", "cart_value",
            "avg_price", "max_price", "duration", "start_hour", "day_of_week"
        ]].fillna(0)

        X_scaled = cart_scaler.transform(X)
        prob_convert = cart_model.predict_proba(X_scaled)[0][1]
        prob_abandon = round(1 - prob_convert, 4)

        if prob_abandon >= 0.5:
            risk = "high"
        elif prob_abandon >= 0.25:
            risk = "medium"
        else:
            risk = "low"

        return {
            "session_id": req.session_id,
            "risk_level": risk,
            "probability": prob_abandon,
            "cart_value": float(df["cart_value"].iloc[0] or 0),
            "recommendation": get_abandonment_recommendation(risk, float(df["cart_value"].iloc[0] or 0))
        }
    
    except Exception as e:
        print(f"Error in cart abandonment prediction: {e}")
        raise HTTPException(500, str(e))


@app.post("/analytics/user-segment")
async def get_user_segment(req: UserSegmentRequest):
    """
    Get user segment and characteristics
    """
    if segment_model is None or segment_scaler is None:
        raise HTTPException(503, "Segmentation model not loaded. Please run train.py first.")
    
    if ch_client is None:
        raise HTTPException(503, "ClickHouse not available")
    
    # Check cache
    if redis_client:
        cache_key = f"segment:{req.user_id}"
        try:
            cached = redis_client.get(cache_key)
            if cached:
                return json.loads(cached)
        except:
            pass

    try:
        query = f"""
            SELECT
                user_id,
                COUNT(DISTINCT session_id) as sessions,
                COUNT(*) as total_events,
                SUM(CASE WHEN event_type = 'purchase' THEN 1 ELSE 0 END) as purchases,
                SUM(CASE WHEN event_type = 'add_to_cart' THEN 1 ELSE 0 END) as cart_adds,
                SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END) as views,
                AVG(price) as avg_price,
                SUM(price) as total_spent,
                MAX(timestamp) as last_activity,
                dateDiff('day', MAX(timestamp), now()) as recency
            FROM events
            WHERE user_id = {req.user_id}
            AND timestamp >= now() - INTERVAL 90 DAY
            GROUP BY user_id
        """

        df = ch_client.query_df(query)

        if df.empty:
            return {"error": "User not found", "user_id": req.user_id}

        X = df[[
            'sessions', 'total_events', 'purchases', 'cart_adds',
            'views', 'avg_price', 'total_spent', 'recency'
        ]].fillna(0)

        X_scaled = segment_scaler.transform(X)
        segment = int(segment_model.predict(X_scaled)[0])

        result = {
            "user_id": req.user_id,
            "segment_id": segment,
            "segment_name": segment_names.get(segment, f"Segment {segment}") if segment_names else f"Segment {segment}",
            "characteristics": {
                "sessions": int(df['sessions'].iloc[0]),
                "purchases": int(df['purchases'].iloc[0]),
                "total_spent": float(df['total_spent'].iloc[0]),
                "avg_price": float(df['avg_price'].iloc[0]),
                "recency_days": int(df['recency'].iloc[0])
            }
        }

        # Cache result
        if redis_client:
            try:
                redis_client.setex(cache_key, 3600, json.dumps(result))
            except:
                pass

        return result
    
    except Exception as e:
        print(f"Error in user segment: {e}")
        raise HTTPException(500, str(e))


@app.get("/analytics/model-performance")
async def get_model_performance():
    """
    Get ML model performance metrics
    """
    return {
        "cart_abandonment_model": {
            "loaded": cart_model is not None,
            "scaler_loaded": cart_scaler is not None,
            "status": "active" if (cart_model and cart_scaler) else "not_loaded"
        },
        "user_segmentation_model": {
            "loaded": segment_model is not None,
            "scaler_loaded": segment_scaler is not None,
            "segments": len(segment_names) if segment_names else 0,
            "status": "active" if (segment_model and segment_scaler) else "not_loaded"
        },
        "recommendation_engine": {
            "collaborative_filtering": "active",
            "content_based": "active",
            "popular_products": "active",
            "trending_products": "active"
        },
        "data_files": {
            "popular_products": popular_products_df is not None,
            "user_product_matrix": user_product_matrix_df is not None,
            "user_segments": user_segments_df is not None
        },
        "services": {
            "clickhouse": ch_client is not None,
            "redis": redis_client is not None
        }
    }


@app.get("/health")
async def health():
    """
    Health check endpoint
    """
    redis_status = False
    clickhouse_status = False
    
    if redis_client:
        try:
            redis_status = redis_client.ping()
        except:
            pass
    
    if ch_client:
        try:
            clickhouse_status = ch_client.ping()
        except:
            pass
    
    return {
        "status": "healthy" if (clickhouse_status and redis_status) else "degraded",
        "services": {
            "redis": redis_status,
            "clickhouse": clickhouse_status,
        },
        "models": {
            "cart_abandonment": cart_model is not None and cart_scaler is not None,
            "user_segmentation": segment_model is not None and segment_scaler is not None
        }
    }


async def get_popular_products_fallback(limit: int):
    """Fallback to popular products when collaborative filtering fails"""
    if popular_products_df is not None and not popular_products_df.empty:
        products = popular_products_df.head(limit)
        return {
            "recommendations": [
                {"product_id": int(row.product_id), "score": 1.0, "reason": "popular_fallback"}
                for row in products.itertuples()
            ]
        }
    return {"recommendations": []}


def get_abandonment_recommendation(risk_level: str, cart_value: float):
    """Get recommendation based on abandonment risk"""
    if risk_level == "high":
        if cart_value > 100:
            return "offer_free_shipping"
        else:
            return "offer_10_percent_discount"
    elif risk_level == "medium":
        return "send_reminder_email"
    else:
        return "no_action_needed"



@app.on_event("startup")
async def startup_event():
    print("\n" + "="*60)
    print(" AI/ML Service Started Successfully!")
    print("="*60)
    print(f" API Documentation: http://localhost:8001/docs")
    print(f" Health Check: http://localhost:8001/health")
    print(f" Model Performance: http://localhost:8001/analytics/model-performance")
    print("="*60 + "\n")


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
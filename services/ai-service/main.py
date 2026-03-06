import sys
import io
# Force UTF-8 stdout on Windows to allow Unicode output
if sys.stdout.encoding and sys.stdout.encoding.lower() != 'utf-8':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

from fastapi import FastAPI, HTTPException, BackgroundTasks
from pydantic import BaseModel
from typing import Optional, List
import clickhouse_connect
import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score
from sklearn.cluster import KMeans
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
        host="reiq2ms4gj.germanywestcentral.azure.clickhouse.cloud",
        user="default",
        password="2Mi0VyELOs_IP",
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



@app.get("/users/{user_id}/profile")
async def get_user_ai_profile(user_id: int, limit: int = 10):
    """
    Full AI profile for a user — used by the admin panel.
    Returns segment, recent events, top interactions, and recommendations.
    """
    result = {
        "user_id": user_id,
        "segment": None,
        "event_summary": {},
        "recent_events": [],
        "top_interactions": [],
        "recommendations": [],
    }

    # ── Segment ───────────────────────────────────────────────
    if user_segments_df is not None and not user_segments_df.empty:
        seg_row = user_segments_df[user_segments_df["user_id"] == user_id]
        if not seg_row.empty:
            seg_id = int(seg_row.iloc[0]["segment"])
            seg_name = (segment_names or {}).get(seg_id, "Unknown")
            result["segment"] = {"id": seg_id, "name": seg_name}

    # ── Top Interactions ──────────────────────────────────────
    if user_product_matrix_df is not None and not user_product_matrix_df.empty:
        urows = user_product_matrix_df[user_product_matrix_df["user_id"] == user_id]
        urows = urows.sort_values("interaction_score", ascending=False).head(limit)
        result["top_interactions"] = [
            {"product_id": int(r["product_id"]), "interaction_score": float(r["interaction_score"])}
            for _, r in urows.iterrows()
        ]

    # ── Recent Events & Summary (from ClickHouse) ─────────────
    if ch_client is not None:
        try:
            q = f"""
                SELECT event_type, product_id, product_name, price, cart_total, timestamp
                FROM events
                WHERE user_id = {user_id}
                ORDER BY timestamp DESC
                LIMIT 30
            """
            ev_df = ch_client.query_df(q)
            if not ev_df.empty:
                result["event_summary"] = ev_df["event_type"].value_counts().to_dict()
                for _, row in ev_df.iterrows():
                    result["recent_events"].append({
                        "event_type":   row["event_type"],
                        "product_id":   int(row["product_id"]) if pd.notna(row.get("product_id")) else None,
                        "product_name": str(row.get("product_name") or ""),
                        "price":        float(row["price"]) if pd.notna(row.get("price")) else None,
                        "timestamp":    str(row["timestamp"]),
                    })
        except Exception as e:
            print(f"[WARN] User profile events query failed: {e}")

    # ── Recommendations ───────────────────────────────────────
    if user_product_matrix_df is not None and not user_product_matrix_df.empty:
        try:
            matrix = user_product_matrix_df.pivot_table(
                index="user_id", columns="product_id", values="interaction_score", fill_value=0
            )
            if user_id in matrix.index:
                user_vec = matrix.loc[user_id].values.reshape(1, -1)
                sims     = cosine_similarity(user_vec, matrix.values)[0]
                top_idx  = np.argsort(sims)[::-1][1:6]
                seen     = set(matrix.columns[matrix.loc[user_id] > 0])
                recs: dict = {}
                for idx in top_idx:
                    sim_user = matrix.index[idx]
                    for pid, score in matrix.loc[sim_user].items():
                        if score > 0 and pid not in seen:
                            recs[pid] = recs.get(pid, 0.0) + score * sims[idx]
                result["recommendations"] = [
                    {"product_id": int(pid), "score": round(float(sc), 2)}
                    for pid, sc in sorted(recs.items(), key=lambda x: -x[1])[:limit]
                ]
        except Exception as e:
            print(f"[WARN] Collab recommendations for user {user_id}: {e}")

    # Fallback to popular
    if not result["recommendations"] and popular_products_df is not None:
        result["recommendations"] = [
            {"product_id": int(r["product_id"]), "score": float(r["popularity_score"]), "reason": "popular"}
            for _, r in popular_products_df.head(limit).iterrows()
        ]

    return result


@app.post("/train")
async def trigger_training(background_tasks: BackgroundTasks):
    """
    Trigger in-process ML training using the live ClickHouse connection.
    Runs in background; reload models into memory when done.
    """
    if ch_client is None:
        raise HTTPException(503, "ClickHouse not connected — cannot train.")
    background_tasks.add_task(_run_training_pipeline)
    return {"status": "training_started", "message": "Training running in background. Poll /analytics/model-performance to see results."}


def _run_training_pipeline():
    """Full training pipeline executed inside the running service (shares ch_client)."""
    global cart_model, cart_scaler, segment_model, segment_scaler
    global segment_names, popular_products_df, user_product_matrix_df, user_segments_df

    os.makedirs('models', exist_ok=True)
    os.makedirs('data',   exist_ok=True)
    print("\n[TRAIN] ===== Training pipeline started =====")

    # ── 1. Cart Abandonment ──────────────────────────────────
    try:
        q = """
            WITH sd AS (
                SELECT session_id, user_id,
                    COUNT(*) AS event_count,
                    SUM(event_type='add_to_cart')  AS cart_adds,
                    SUM(event_type='product_view') AS views,
                    SUM(event_type='purchase')     AS purchases,
                    MAX(cart_total)                AS cart_value,
                    AVG(price)                     AS avg_price,
                    MAX(price)                     AS max_price,
                    dateDiff('second',MIN(timestamp),MAX(timestamp)) AS duration,
                    toHour(MIN(timestamp))         AS start_hour,
                    toDayOfWeek(MIN(timestamp))    AS day_of_week
                FROM events
                WHERE timestamp >= now() - INTERVAL 90 DAY
                GROUP BY session_id, user_id
            )
            SELECT * FROM sd
        """
        df = ch_client.query_df(q)
        print(f"[TRAIN] Cart model: {len(df)} sessions loaded")

        if len(df) >= 20:
            df['converted'] = (df['purchases'] > 0).astype(int)
            feat = ['event_count','cart_adds','views','cart_value','avg_price','max_price','duration','start_hour','day_of_week']
            X = df[feat].fillna(0)
            y = df['converted']
            if y.nunique() > 1:
                Xtr, Xte, ytr, yte = train_test_split(X, y, test_size=0.2, random_state=42, stratify=y)
                sc = StandardScaler(); Xtr_s = sc.fit_transform(Xtr); Xte_s = sc.transform(Xte)
                mdl = RandomForestClassifier(n_estimators=100, max_depth=10, min_samples_split=5, random_state=42, class_weight='balanced')
                mdl.fit(Xtr_s, ytr)
                acc = accuracy_score(yte, mdl.predict(Xte_s))
                joblib.dump(mdl, 'models/cart_abandonment_model.pkl')
                joblib.dump(sc,  'models/cart_abandonment_scaler.pkl')
                cart_model, cart_scaler = mdl, sc
                print(f"[TRAIN] Cart model accuracy: {acc:.2%} — saved.")
            else:
                print("[TRAIN] Cart model: only one class, skipping.")
        else:
            print(f"[TRAIN] Cart model: insufficient sessions ({len(df)}), skipping.")
    except Exception as e:
        print(f"[TRAIN] Cart model error: {e}")

    # ── 2. User Segmentation ─────────────────────────────────
    try:
        q = """
            SELECT user_id,
                COUNT(DISTINCT session_id)                                     AS sessions,
                COUNT(*)                                                       AS total_events,
                SUM(event_type='purchase')                                     AS purchases,
                SUM(event_type='add_to_cart')                                  AS cart_adds,
                SUM(event_type='product_view')                                 AS views,
                AVG(price)                                                     AS avg_price,
                SUM(CASE WHEN event_type='purchase' THEN price ELSE 0 END)     AS total_spent,
                MAX(timestamp)                                                 AS last_activity
            FROM events
            WHERE user_id IS NOT NULL
              AND timestamp >= now() - INTERVAL 90 DAY
            GROUP BY user_id
        """
        df = ch_client.query_df(q)
        print(f"[TRAIN] Segmentation: {len(df)} users loaded")

        if len(df) >= 5:
            df['last_activity'] = pd.to_datetime(df['last_activity'], utc=True)
            df['recency'] = (datetime.now(df['last_activity'].dt.tz) - df['last_activity']).dt.days
            feat = ['sessions','total_events','purchases','cart_adds','views','avg_price','total_spent','recency']
            X = df[feat].fillna(0)
            sc = StandardScaler(); Xs = sc.fit_transform(X)
            n_cl = max(2, min(5, len(df) // 3))
            km = KMeans(n_clusters=n_cl, random_state=42, n_init=10)
            df['segment'] = km.fit_predict(Xs)
            ss = df.groupby('segment').agg({'sessions':'mean','purchases':'mean','total_spent':'mean','avg_price':'mean','recency':'mean'}).round(2)
            gm = ss.mean()
            snames = {}
            for seg in ss.index:
                p=ss.loc[seg,'purchases']; ts=ss.loc[seg,'total_spent']; r=ss.loc[seg,'recency']; s=ss.loc[seg,'sessions']
                if   p  > gm['purchases']   * 1.5: snames[seg] = 'VIP Customers'
                elif ts > gm['total_spent']:        snames[seg] = 'High Spenders'
                elif r  > gm['recency']:            snames[seg] = 'At Risk'
                elif s  > gm['sessions']:           snames[seg] = 'Frequent Browsers'
                else:                               snames[seg] = 'Casual Shoppers'
            joblib.dump(km,     'models/user_segmentation_model.pkl')
            joblib.dump(sc,     'models/user_segmentation_scaler.pkl')
            joblib.dump(snames, 'models/segment_names.pkl')
            df[['user_id','segment']].to_csv('data/user_segments.csv', index=False)
            segment_model, segment_scaler, segment_names = km, sc, snames
            user_segments_df = df[['user_id','segment']]
            print(f"[TRAIN] Segmentation: {n_cl} segments — {snames}")
        else:
            print(f"[TRAIN] Segmentation: not enough users ({len(df)}), skipping.")
    except Exception as e:
        print(f"[TRAIN] Segmentation error: {e}")

    # ── 3. User-Product Matrix ───────────────────────────────
    try:
        q = """
            SELECT user_id, product_id,
                SUM(CASE WHEN event_type='purchase'     THEN 5
                         WHEN event_type='add_to_cart'  THEN 3
                         WHEN event_type='product_view' THEN 1
                         ELSE 0 END) AS interaction_score
            FROM events
            WHERE user_id IS NOT NULL AND product_id IS NOT NULL
              AND timestamp >= now() - INTERVAL 90 DAY
            GROUP BY user_id, product_id
            HAVING interaction_score > 0
        """
        df = ch_client.query_df(q)
        if not df.empty:
            df.to_csv('data/user_product_matrix.csv', index=False)
            user_product_matrix_df = df
            print(f"[TRAIN] Matrix: {len(df)} rows, {df['user_id'].nunique()} users, {df['product_id'].nunique()} products — saved.")
        else:
            print("[TRAIN] Matrix: no data.")
    except Exception as e:
        print(f"[TRAIN] Matrix error: {e}")

    # ── 4. Popular Products ──────────────────────────────────
    try:
        q = """
            SELECT product_id,
                COUNT(DISTINCT user_id)                                        AS unique_users,
                COUNT(DISTINCT session_id)                                     AS unique_sessions,
                SUM(event_type='purchase')                                     AS purchases,
                SUM(event_type='product_view')                                 AS views,
                SUM(event_type='add_to_cart')                                  AS cart_adds,
                AVG(price)                                                     AS avg_price,
                SUM(CASE WHEN event_type='purchase' THEN price ELSE 0 END)     AS revenue
            FROM events
            WHERE product_id IS NOT NULL
              AND timestamp >= now() - INTERVAL 30 DAY
            GROUP BY product_id
            ORDER BY purchases DESC, unique_users DESC
            LIMIT 100
        """
        df = ch_client.query_df(q)
        if not df.empty:
            df['popularity_score'] = df['purchases']*5 + df['unique_users']*2 + df['cart_adds']*2 + df['views']
            df = df.sort_values('popularity_score', ascending=False)
            df.to_csv('data/popular_products.csv', index=False)
            popular_products_df = df
            print(f"[TRAIN] Popular products: {len(df)} products — saved.")
            print(f"        Top 3: {df.head(3)[['product_id','purchases','revenue']].to_dict('records')}")
        else:
            print("[TRAIN] Popular products: no data.")
    except Exception as e:
        print(f"[TRAIN] Popular products error: {e}")

    print("[TRAIN] ===== Training pipeline complete =====\n")


@app.on_event("startup")
async def startup_event():
    print("\n" + "="*60)
    print(" AI/ML Service Started Successfully!")
    print("="*60)
    print(f" API Documentation: http://localhost:8001/docs")
    print(f" Health Check: http://localhost:8001/health")
    print(f" Model Performance: http://localhost:8001/analytics/model-performance")
    print(f" Train on real data: POST http://localhost:8001/train")
    print("="*60 + "\n")


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
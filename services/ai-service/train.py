import sys
import io
# Force UTF-8 stdout on Windows
if sys.stdout.encoding and sys.stdout.encoding.lower() != 'utf-8':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

import clickhouse_connect
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score, classification_report
from sklearn.cluster import KMeans
import joblib
from datetime import datetime
import os

# Directories
os.makedirs('models', exist_ok=True)
os.makedirs('data', exist_ok=True)

# ClickHouse — same host used by all services
CH_HOST     = 'syym5je3fm.asia-southeast1.gcp.clickhouse.cloud'
CH_USER     = 'default'
CH_PASSWORD = 'oR0n3ljHAc_e7'

try:
    ch_client = clickhouse_connect.get_client(
        host=CH_HOST, user=CH_USER, password=CH_PASSWORD,
        secure=True, verify=False,          # bypass SSL cert check (Python 3.14 compat)
        compress=False,                     # disable compression for stability
        send_receive_timeout=30,
    )
    total = ch_client.query("SELECT count() FROM events").result_set[0][0]
    print(f"[OK] ClickHouse connected — {total} total events in table")
except Exception as e:
    print(f"[ERR] Failed to connect: {e}")
    sys.exit(1)


# ─────────────────────────────────────────
# Helpers
# ─────────────────────────────────────────

def safe_save_csv(df, filename, description="file"):
    filepath = os.path.join('data', filename)
    try:
        df.to_csv(filepath, index=False)
        print(f"[OK] Saved {description}: {filepath} ({len(df)} rows)")
        return filepath
    except Exception as e:
        print(f"[ERR] Error saving {filename}: {e}")
        return None


def safe_save_model(model, filename, description="model"):
    filepath = os.path.join('models', filename)
    try:
        joblib.dump(model, filepath)
        print(f"[OK] Saved {description}: {filepath}")
        return filepath
    except Exception as e:
        print(f"[ERR] Error saving {filename}: {e}")
        return None


# ─────────────────────────────────────────
# Cart Abandonment Model
# ─────────────────────────────────────────

def train_cart_abandonment_model():
    print("\n" + "="*60)
    print("  Training Cart Abandonment Model (real data)")
    print("="*60)

    query = """
        WITH session_data AS (
            SELECT
                session_id,
                user_id,
                COUNT(*)                                                        AS event_count,
                SUM(CASE WHEN event_type = 'add_to_cart' THEN 1 ELSE 0 END)    AS cart_adds,
                SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END)   AS views,
                SUM(CASE WHEN event_type = 'purchase'     THEN 1 ELSE 0 END)   AS purchases,
                MAX(cart_total)                                                 AS cart_value,
                AVG(price)                                                      AS avg_price,
                MAX(price)                                                      AS max_price,
                dateDiff('second', MIN(timestamp), MAX(timestamp))              AS duration,
                toHour(MIN(timestamp))                                          AS start_hour,
                toDayOfWeek(MIN(timestamp))                                     AS day_of_week
            FROM events
            WHERE timestamp >= now() - INTERVAL 90 DAY
            GROUP BY session_id, user_id
        )
        SELECT * FROM session_data
    """

    try:
        df = ch_client.query_df(query)
        print(f"[DATA] Loaded {len(df)} sessions from ClickHouse")
    except Exception as e:
        print(f"[ERR] Query failed: {e}")
        return None, None

    if len(df) < 20:
        print(f"[WARN] Only {len(df)} sessions — need at least 20 to train. Skipping.")
        return None, None

    df['converted'] = (df['purchases'] > 0).astype(int)
    print(f"  Conversion rate : {df['converted'].mean():.2%}")
    print(f"  Converted       : {df['converted'].sum()}")
    print(f"  Abandoned       : {(df['converted'] == 0).sum()}")

    feature_cols = [
        'event_count', 'cart_adds', 'views', 'cart_value',
        'avg_price', 'max_price', 'duration', 'start_hour', 'day_of_week'
    ]
    X = df[feature_cols].fillna(0)
    y = df['converted']

    if y.nunique() == 1:
        print("[WARN] Only one class in target — cannot train. Skipping.")
        return None, None

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )

    scaler = StandardScaler()
    X_train_s = scaler.fit_transform(X_train)
    X_test_s  = scaler.transform(X_test)

    print("  Training RandomForest ...")
    model = RandomForestClassifier(
        n_estimators=100, max_depth=10,
        min_samples_split=5, random_state=42, class_weight='balanced'
    )
    model.fit(X_train_s, y_train)

    y_pred   = model.predict(X_test_s)
    accuracy = accuracy_score(y_test, y_pred)
    print(f"  Accuracy: {accuracy:.2%}")
    print(classification_report(y_test, y_pred, target_names=['Abandoned', 'Converted']))

    feat_imp = pd.DataFrame({
        'feature': feature_cols,
        'importance': model.feature_importances_
    }).sort_values('importance', ascending=False)
    print("  Top features:")
    print(feat_imp.head(5).to_string(index=False))

    safe_save_model(model,  'cart_abandonment_model.pkl',  'Cart abandonment model')
    safe_save_model(scaler, 'cart_abandonment_scaler.pkl', 'Cart abandonment scaler')
    return model, scaler


# ─────────────────────────────────────────
# User Segmentation (KMeans)
# ─────────────────────────────────────────

def train_user_segmentation():
    print("\n" + "="*60)
    print("  Training User Segmentation Model (real data)")
    print("="*60)

    query = """
        SELECT
            user_id,
            COUNT(DISTINCT session_id)                                          AS sessions,
            COUNT(*)                                                            AS total_events,
            SUM(CASE WHEN event_type = 'purchase'     THEN 1 ELSE 0 END)       AS purchases,
            SUM(CASE WHEN event_type = 'add_to_cart'  THEN 1 ELSE 0 END)       AS cart_adds,
            SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END)       AS views,
            AVG(price)                                                          AS avg_price,
            SUM(CASE WHEN event_type = 'purchase' THEN price ELSE 0 END)       AS total_spent,
            MAX(timestamp)                                                      AS last_activity
        FROM events
        WHERE user_id IS NOT NULL
          AND timestamp >= now() - INTERVAL 90 DAY
        GROUP BY user_id
    """

    try:
        df = ch_client.query_df(query)
        print(f"[DATA] Loaded {len(df)} users from ClickHouse")
    except Exception as e:
        print(f"[ERR] Query failed: {e}")
        return None, None, None

    if len(df) < 5:
        print(f"[WARN] Only {len(df)} users — need at least 5. Skipping segmentation.")
        return None, None, None

    df['last_activity'] = pd.to_datetime(df['last_activity'], utc=True)
    df['recency'] = (datetime.now(df['last_activity'].dt.tz) - df['last_activity']).dt.days

    feature_cols = [
        'sessions', 'total_events', 'purchases',
        'cart_adds', 'views', 'avg_price', 'total_spent', 'recency'
    ]
    X = df[feature_cols].fillna(0)

    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(X)

    n_clusters = max(2, min(5, len(df) // 3))
    print(f"  Clustering {len(df)} users into {n_clusters} segments ...")

    kmeans = KMeans(n_clusters=n_clusters, random_state=42, n_init=10)
    df['segment'] = kmeans.fit_predict(X_scaled)

    seg_stats = df.groupby('segment').agg({
        'sessions': 'mean', 'purchases': 'mean',
        'total_spent': 'mean', 'avg_price': 'mean', 'recency': 'mean'
    }).round(2)

    gm = seg_stats.mean()
    segment_names = {}
    for seg in seg_stats.index:
        p  = seg_stats.loc[seg, 'purchases']
        ts = seg_stats.loc[seg, 'total_spent']
        r  = seg_stats.loc[seg, 'recency']
        s  = seg_stats.loc[seg, 'sessions']
        if   p  > gm['purchases']   * 1.5:  segment_names[seg] = 'VIP Customers'
        elif ts > gm['total_spent']:         segment_names[seg] = 'High Spenders'
        elif r  > gm['recency']:             segment_names[seg] = 'At Risk'
        elif s  > gm['sessions']:            segment_names[seg] = 'Frequent Browsers'
        else:                                segment_names[seg] = 'Casual Shoppers'

    print("\n  User Segments:")
    for seg, name in segment_names.items():
        count = (df['segment'] == seg).sum()
        row = seg_stats.loc[seg]
        print(f"    Seg {seg} [{name}] — {count} users | "
              f"purchases={row['purchases']:.1f} spent=${row['total_spent']:.0f} "
              f"recency={row['recency']:.0f}d")

    safe_save_model(kmeans,        'user_segmentation_model.pkl',  'User segmentation model')
    safe_save_model(scaler,        'user_segmentation_scaler.pkl', 'User segmentation scaler')
    safe_save_model(segment_names, 'segment_names.pkl',            'Segment names')
    safe_save_csv(df[['user_id', 'segment']], 'user_segments.csv', 'User segments')
    return kmeans, scaler, segment_names


# ─────────────────────────────────────────
# User–Product Interaction Matrix
# ─────────────────────────────────────────

def build_interaction_matrix():
    print("\n" + "="*60)
    print("  Building User-Product Interaction Matrix (real data)")
    print("="*60)

    query = """
        SELECT
            user_id,
            product_id,
            SUM(CASE WHEN event_type = 'purchase'     THEN 5
                     WHEN event_type = 'add_to_cart'  THEN 3
                     WHEN event_type = 'product_view' THEN 1
                     ELSE 0 END)                                    AS interaction_score
        FROM events
        WHERE user_id IS NOT NULL
          AND product_id IS NOT NULL
          AND timestamp >= now() - INTERVAL 90 DAY
        GROUP BY user_id, product_id
        HAVING interaction_score > 0
        ORDER BY user_id, interaction_score DESC
    """

    try:
        df = ch_client.query_df(query)
        print(f"[DATA] {len(df)} user-product interactions from ClickHouse")
    except Exception as e:
        print(f"[ERR] Query failed: {e}")
        return pd.DataFrame()

    if df.empty:
        print("[WARN] No interactions found.")
        return df

    print(f"  Unique users    : {df['user_id'].nunique()}")
    print(f"  Unique products : {df['product_id'].nunique()}")
    print(f"  Avg per user    : {len(df) / df['user_id'].nunique():.1f}")

    safe_save_csv(df, 'user_product_matrix.csv', 'User-product matrix')
    return df


# ─────────────────────────────────────────
# Popular Products
# ─────────────────────────────────────────

def analyze_product_popularity():
    print("\n" + "="*60)
    print("  Analyzing Product Popularity (real data)")
    print("="*60)

    query = """
        SELECT
            product_id,
            COUNT(DISTINCT user_id)                                             AS unique_users,
            COUNT(DISTINCT session_id)                                          AS unique_sessions,
            SUM(CASE WHEN event_type = 'purchase'     THEN 1 ELSE 0 END)       AS purchases,
            SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END)       AS views,
            SUM(CASE WHEN event_type = 'add_to_cart'  THEN 1 ELSE 0 END)       AS cart_adds,
            AVG(price)                                                          AS avg_price,
            SUM(CASE WHEN event_type = 'purchase' THEN price ELSE 0 END)       AS revenue
        FROM events
        WHERE product_id IS NOT NULL
          AND timestamp >= now() - INTERVAL 30 DAY
        GROUP BY product_id
        ORDER BY purchases DESC, unique_users DESC, views DESC
        LIMIT 100
    """

    try:
        df = ch_client.query_df(query)
        print(f"[DATA] {len(df)} products from ClickHouse (last 30 days)")
    except Exception as e:
        print(f"[ERR] Query failed: {e}")
        return pd.DataFrame()

    if df.empty:
        print("[WARN] No product data found.")
        return df

    df['popularity_score'] = (
        df['purchases'] * 5 +
        df['unique_users'] * 2 +
        df['cart_adds'] * 2 +
        df['views'] * 1
    )
    df = df.sort_values('popularity_score', ascending=False)

    safe_save_csv(df, 'popular_products.csv', 'Popular products')

    print("\n  Top 5 Products:")
    print(df.head(5)[['product_id', 'purchases', 'views', 'revenue', 'popularity_score']].to_string(index=False))
    return df


# ─────────────────────────────────────────
# Summary
# ─────────────────────────────────────────

def print_summary():
    print("\n" + "="*60)
    print("  TRAINING SUMMARY")
    print("="*60)

    checks = {
        'Cart Abandonment Model':   'models/cart_abandonment_model.pkl',
        'Cart Abandonment Scaler':  'models/cart_abandonment_scaler.pkl',
        'User Segmentation Model':  'models/user_segmentation_model.pkl',
        'User Segmentation Scaler': 'models/user_segmentation_scaler.pkl',
        'Segment Names':            'models/segment_names.pkl',
        'User-Product Matrix':      'data/user_product_matrix.csv',
        'User Segments':            'data/user_segments.csv',
        'Popular Products':         'data/popular_products.csv',
    }

    for name, path in checks.items():
        if os.path.exists(path):
            size = os.path.getsize(path) / 1024
            print(f"  [OK] {name} ({size:.1f} KB)")
        else:
            print(f"  [--] {name} — not generated")

    print("="*60)


# ─────────────────────────────────────────
# Entry Point
# ─────────────────────────────────────────

if __name__ == "__main__":
    print("\n" + "="*60)
    print("  TechNova AI — ML Training Pipeline (Real ClickHouse Data)")
    print("="*60)
    print(f"  Started : {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"  Dir     : {os.getcwd()}")

    train_cart_abandonment_model()
    train_user_segmentation()
    build_interaction_matrix()
    analyze_product_popularity()
    print_summary()

    print("\n[DONE] Training pipeline complete!")
    print(f"       Finished: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

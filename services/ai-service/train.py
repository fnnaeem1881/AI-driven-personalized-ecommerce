# train.py
import clickhouse_connect
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score, classification_report
import joblib

ch_client = clickhouse_connect.get_client(
    host='syym5je3fm.asia-southeast1.gcp.clickhouse.cloud',
    user='default',
    password='oR0n3ljHAc_e7',
    secure=True
)
print("Result:", ch_client.query("SELECT 1").result_set[0][0])

def train_cart_abandonment_model():
    print("📊 Fetching training data...")

    query = """
        WITH session_data AS (
            SELECT
                session_id,
                user_id,
                COUNT(*) as event_count,
                SUM(CASE WHEN event_type = 'add_to_cart' THEN 1 ELSE 0 END) as cart_adds,
                SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END) as views,
                SUM(CASE WHEN event_type = 'purchase' THEN 1 ELSE 0 END) as purchases,
                MAX(cart_total) as cart_value,
                AVG(price) as avg_price,
                MAX(price) as max_price,
                dateDiff('second', MIN(timestamp), MAX(timestamp)) as duration,
                toHour(MIN(timestamp)) as start_hour,
                toDayOfWeek(MIN(timestamp)) as day_of_week
            FROM events
            WHERE timestamp >= now() - INTERVAL 90 DAY
            GROUP BY session_id, user_id
        )
        SELECT * FROM session_data
    """

    df = ch_client.query_df(query)
    print(f"✓ Loaded {len(df)} sessions")

    if df.empty:
        print("⚠️ No data available for training. Skipping model.")
        return None, None

    # Safe check for purchases column
    if 'purchases' not in df.columns:
        df['purchases'] = 0

    df['converted'] = (df['purchases'] > 0).astype(int)

    feature_cols = [
        'event_count', 'cart_adds', 'views', 'cart_value',
        'avg_price', 'max_price', 'duration', 'start_hour', 'day_of_week'
    ]

    X = df[feature_cols].fillna(0)
    y = df['converted']

    if y.nunique() == 1:
        print("⚠️ Only one class in target. Skipping training.")
        return None, None

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )

    scaler = StandardScaler()
    X_train_scaled = scaler.fit_transform(X_train)
    X_test_scaled = scaler.transform(X_test)

    model = RandomForestClassifier(
        n_estimators=100,
        max_depth=10,
        min_samples_split=10,
        random_state=42,
        class_weight='balanced'
    )
    model.fit(X_train_scaled, y_train)

    y_pred = model.predict(X_test_scaled)
    print(f"\n✓ Model Accuracy: {accuracy_score(y_test, y_pred):.2%}")
    print("\nClassification Report:")
    print(classification_report(y_test, y_pred))

    joblib.dump(model, 'cart_abandonment_model.pkl')
    joblib.dump(scaler, 'cart_abandonment_scaler.pkl')
    print("✅ Model saved successfully!")

    return model, scaler

def train_product_recommendation_matrix():
    print("📊 Building recommendation matrix...")

    query = """
        SELECT 
            user_id,
            product_id,
            SUM(CASE WHEN event_type = 'purchase' THEN 5
                     WHEN event_type = 'add_to_cart' THEN 3
                     WHEN event_type = 'product_view' THEN 1
                     ELSE 0 END) as interaction_score
        FROM events
        WHERE user_id IS NOT NULL
        AND timestamp >= now() - INTERVAL 90 DAY
        GROUP BY user_id, product_id
        HAVING interaction_score > 0
    """

    df = ch_client.query_df(query)

    if df.empty:
        print("⚠️ No user-product interactions found.")
        return df

    df.to_csv('user_product_matrix.csv', index=False)
    print(f"✓ Matrix saved: {len(df)} interactions")
    print(f"  Users: {df['user_id'].nunique()}")
    print(f"  Products: {df['product_id'].nunique()}")
    return df
print("\n✅ Training completed!")
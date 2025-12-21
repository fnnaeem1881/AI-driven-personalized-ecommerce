# train.py
import clickhouse_connect
import pandas as pd
import joblib
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import classification_report, accuracy_score

# ClickHouse connection
ch_client = clickhouse_connect.get_client(
    host='syym5je3fm.asia-southeast1.gcp.clickhouse.cloud',
    user='default',
    password='oR0n3ljHAc_e7',
    secure=True
)

def train_cart_abandonment_model():

    print("📊 Fetching training data...")

    query = """
        WITH session_data AS (
            SELECT
                session_id,
                user_id,
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
            WHERE timestamp >= now() - INTERVAL 90 DAY
            GROUP BY session_id, user_id
        )
        SELECT * FROM session_data
    """

    df = ch_client.query_df(query)

    if df.empty:
        raise Exception("❌ No training data found")

    # Target: 1 = converted, 0 = abandoned
    df["converted"] = (df["purchases"] > 0).astype(int)

    features = [
        "event_count",
        "cart_adds",
        "views",
        "cart_value",
        "avg_price",
        "max_price",
        "duration",
        "start_hour",
        "day_of_week"
    ]

    X = df[features].fillna(0)
    y = df["converted"]

    if y.nunique() < 2:
        raise Exception("❌ Only one class found")

    X_train, X_test, y_train, y_test = train_test_split(
        X, y,
        test_size=0.2,
        random_state=42,
        stratify=y
    )

    scaler = StandardScaler()
    X_train_scaled = scaler.fit_transform(X_train)
    X_test_scaled = scaler.transform(X_test)

    model = RandomForestClassifier(
        n_estimators=200,
        max_depth=12,
        min_samples_split=10,
        class_weight="balanced",
        random_state=42
    )

    model.fit(X_train_scaled, y_train)

    preds = model.predict(X_test_scaled)

    print("✅ Accuracy:", accuracy_score(y_test, preds))
    print(classification_report(y_test, preds))

    joblib.dump(model, "cart_abandonment_model.pkl")
    joblib.dump(scaler, "cart_abandonment_scaler.pkl")

    print("💾 Model & scaler saved")

if __name__ == "__main__":
    train_cart_abandonment_model()

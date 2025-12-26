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
import sys

# Create necessary directories
os.makedirs('models', exist_ok=True)
os.makedirs('data', exist_ok=True)

# ClickHouse connection
try:
    ch_client = clickhouse_connect.get_client(
        host='syym5je3fm.asia-southeast1.gcp.clickhouse.cloud',
        user='default',
        password='oR0n3ljHAc_e7',
        secure=True
    )
    print(" ClickHouse connected")
    print("Result:", ch_client.query("SELECT 1").result_set[0][0])
except Exception as e:
    print(f" Failed to connect to ClickHouse: {e}")
    sys.exit(1)

def safe_save_csv(df, filename, description="file"):
    """Safely save CSV with error handling"""
    filepath = os.path.join('data', filename)
    try:
        # Check if file exists and is locked
        if os.path.exists(filepath):
            try:
                os.rename(filepath, filepath)  # Test if file is accessible
            except OSError:
                print(f" Warning: {filename} is open in another program. Attempting backup save...")
                backup_path = filepath.replace('.csv', '_backup.csv')
                df.to_csv(backup_path, index=False)
                print(f" Saved to backup: {backup_path}")
                return backup_path
        
        df.to_csv(filepath, index=False)
        print(f" Saved {description}: {filepath}")
        return filepath
    except PermissionError:
        print(f" Permission denied: {filepath}")
        print(f"   Please close {filename} if it's open in Excel or another program")
        return None
    except Exception as e:
        print(f" Error saving {filename}: {e}")
        return None

def safe_save_model(model, filename, description="model"):
    """Safely save model with error handling"""
    filepath = os.path.join('models', filename)
    try:
        joblib.dump(model, filepath)
        print(f" Saved {description}: {filepath}")
        return filepath
    except Exception as e:
        print(f" Error saving {filename}: {e}")
        return None

def train_cart_abandonment_model():
    """Train cart abandonment prediction model"""
    print("\n" + "="*60)
    print(" Training Cart Abandonment Model")
    print("="*60)

    try:
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
        print(f" Loaded {len(df)} sessions")

        if df.empty:
            print(" No data available for training. Skipping model.")
            return None, None

        # Create target variable
        if 'purchases' not in df.columns:
            df['purchases'] = 0
        
        df['converted'] = (df['purchases'] > 0).astype(int)
        
        print(f"   Conversion rate: {df['converted'].mean():.2%}")
        print(f"   Converted sessions: {df['converted'].sum()}")
        print(f"   Abandoned sessions: {(~df['converted'].astype(bool)).sum()}")

        # Features
        feature_cols = [
            'event_count', 'cart_adds', 'views', 'cart_value',
            'avg_price', 'max_price', 'duration', 'start_hour', 'day_of_week'
        ]

        X = df[feature_cols].fillna(0)
        y = df['converted']

        if y.nunique() == 1:
            print(" Only one class in target. Skipping training.")
            return None, None

        # Split data
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=42, stratify=y
        )

        # Scale features
        scaler = StandardScaler()
        X_train_scaled = scaler.fit_transform(X_train)
        X_test_scaled = scaler.transform(X_test)

        # Train model
        print(" Training Random Forest model...")
        model = RandomForestClassifier(
            n_estimators=100,
            max_depth=10,
            min_samples_split=10,
            random_state=42,
            class_weight='balanced'
        )
        model.fit(X_train_scaled, y_train)

        # Evaluate
        y_pred = model.predict(X_test_scaled)
        accuracy = accuracy_score(y_test, y_pred)
        
        print(f"\n Model Accuracy: {accuracy:.2%}")
        print("\nClassification Report:")
        print(classification_report(y_test, y_pred, target_names=['Abandoned', 'Converted']))

        # Feature importance
        feature_importance = pd.DataFrame({
            'feature': feature_cols,
            'importance': model.feature_importances_
        }).sort_values('importance', ascending=False)
        
        print("\n Top Features:")
        print(feature_importance.head(5).to_string(index=False))

        # Save models
        safe_save_model(model, 'cart_abandonment_model.pkl', 'Cart abandonment model')
        safe_save_model(scaler, 'cart_abandonment_scaler.pkl', 'Cart abandonment scaler')

        return model, scaler

    except Exception as e:
        print(f" Error training cart abandonment model: {e}")
        import traceback
        traceback.print_exc()
        return None, None


def train_user_segmentation():
    """Train user segmentation using K-Means clustering"""
    print("\n" + "="*60)
    print(" Training User Segmentation Model")
    print("="*60)

    try:
        query = """
            SELECT
                user_id,
                COUNT(DISTINCT session_id) as sessions,
                COUNT(*) as total_events,
                SUM(CASE WHEN event_type = 'purchase' THEN 1 ELSE 0 END) as purchases,
                SUM(CASE WHEN event_type = 'add_to_cart' THEN 1 ELSE 0 END) as cart_adds,
                SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END) as views,
                AVG(price) as avg_price,
                SUM(price) as total_spent,
                MAX(timestamp) as last_activity
            FROM events
            WHERE user_id IS NOT NULL
            AND timestamp >= now() - INTERVAL 90 DAY
            GROUP BY user_id
            HAVING purchases > 0 OR sessions > 1
        """

        df = ch_client.query_df(query)
        
        if df.empty or len(df) < 10:
            print(" Insufficient user data for segmentation.")
            return None, None, None

        print(f" Loaded {len(df)} users")

        # Calculate recency
        df['last_activity'] = pd.to_datetime(df['last_activity'])
        df['recency'] = (datetime.now() - df['last_activity']).dt.days

        # Features
        feature_cols = [
            'sessions', 'total_events', 'purchases', 
            'cart_adds', 'views', 'avg_price', 'total_spent', 'recency'
        ]
        
        X = df[feature_cols].fillna(0)

        # Scale
        scaler = StandardScaler()
        X_scaled = scaler.fit_transform(X)

        # Cluster
        n_clusters = min(5, len(df) // 5)
        
        print(f"🤖 Clustering into {n_clusters} segments...")
        
        kmeans = KMeans(n_clusters=n_clusters, random_state=42, n_init=10)
        df['segment'] = kmeans.fit_predict(X_scaled)

        # Analyze segments
        segment_analysis = df.groupby('segment').agg({
            'sessions': 'mean',
            'purchases': 'mean',
            'total_spent': 'mean',
            'avg_price': 'mean',
            'recency': 'mean'
        }).round(2)

        # Name segments
        segment_names = {}
        for seg in segment_analysis.index:
            if segment_analysis.loc[seg, 'purchases'] > segment_analysis['purchases'].mean() * 1.5:
                segment_names[seg] = 'VIP Customers'
            elif segment_analysis.loc[seg, 'total_spent'] > segment_analysis['total_spent'].mean():
                segment_names[seg] = 'High Spenders'
            elif segment_analysis.loc[seg, 'recency'] > segment_analysis['recency'].mean():
                segment_names[seg] = 'At Risk'
            elif segment_analysis.loc[seg, 'sessions'] > segment_analysis['sessions'].mean():
                segment_names[seg] = 'Frequent Browsers'
            else:
                segment_names[seg] = 'Casual Shoppers'

        print("\n User Segments:")
        print("-" * 80)
        for seg in segment_analysis.index:
            print(f"\nSegment {seg}: {segment_names[seg]}")
            print(f"  Avg Sessions: {segment_analysis.loc[seg, 'sessions']:.1f}")
            print(f"  Avg Purchases: {segment_analysis.loc[seg, 'purchases']:.1f}")
            print(f"  Avg Spent: ${segment_analysis.loc[seg, 'total_spent']:.2f}")
            print(f"  Avg Product Price: ${segment_analysis.loc[seg, 'avg_price']:.2f}")
            print(f"  Days Since Last Visit: {segment_analysis.loc[seg, 'recency']:.0f}")

        # Save models
        safe_save_model(kmeans, 'user_segmentation_model.pkl', 'User segmentation model')
        safe_save_model(scaler, 'user_segmentation_scaler.pkl', 'User segmentation scaler')
        safe_save_model(segment_names, 'segment_names.pkl', 'Segment names')
        
        # Save user segments
        safe_save_csv(df[['user_id', 'segment']], 'user_segments.csv', 'User segments')

        return kmeans, scaler, segment_names

    except Exception as e:
        print(f" Error training user segmentation: {e}")
        import traceback
        traceback.print_exc()
        return None, None, None


def train_product_recommendation_matrix():
    """Build user-product interaction matrix"""
    print("\n" + "="*60)
    print(" Building Product Recommendation Matrix")
    print("="*60)

    try:
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
            print(" No user-product interactions found.")
            return df

        # Save
        safe_save_csv(df, 'user_product_matrix.csv', 'User-product matrix')
        
        print(f"   Unique Users: {df['user_id'].nunique()}")
        print(f"   Unique Products: {df['product_id'].nunique()}")
        print(f"   Avg Interactions per User: {len(df) / df['user_id'].nunique():.1f}")
        
        return df

    except Exception as e:
        print(f" Error building recommendation matrix: {e}")
        import traceback
        traceback.print_exc()
        return pd.DataFrame()


def analyze_product_popularity():
    """Analyze popular products"""
    print("\n" + "="*60)
    print(" Analyzing Product Popularity")
    print("="*60)

    try:
        query = """
            SELECT 
                product_id,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT session_id) as unique_sessions,
                SUM(CASE WHEN event_type = 'purchase' THEN 1 ELSE 0 END) as purchases,
                SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END) as views,
                AVG(price) as avg_price
            FROM events
            WHERE timestamp >= now() - INTERVAL 30 DAY
            AND product_id IS NOT NULL
            GROUP BY product_id
            ORDER BY purchases DESC, views DESC
            LIMIT 100
        """

        df = ch_client.query_df(query)
        
        if df.empty:
            print(" No product data found.")
            return df

        # Calculate popularity score
        df['popularity_score'] = (
            df['purchases'] * 5 + 
            df['unique_users'] * 2 + 
            df['views'] * 1
        )
        
        df = df.sort_values('popularity_score', ascending=False)
        
        safe_save_csv(df, 'popular_products.csv', 'Popular products')
        
        print(f"\nTop 5 Products:")
        print(df.head(5)[['product_id', 'purchases', 'views', 'popularity_score']].to_string(index=False))
        
        return df

    except Exception as e:
        print(f" Error analyzing product popularity: {e}")
        import traceback
        traceback.print_exc()
        return pd.DataFrame()


def generate_training_report():
    """Generate training summary"""
    print("\n" + "="*60)
    print("TRAINING SUMMARY REPORT")
    print("="*60)
    
    models = {
        'Cart Abandonment Model': 'models/cart_abandonment_model.pkl',
        'Cart Abandonment Scaler': 'models/cart_abandonment_scaler.pkl',
        'User Segmentation Model': 'models/user_segmentation_model.pkl',
        'User Segmentation Scaler': 'models/user_segmentation_scaler.pkl',
        'Segment Names': 'models/segment_names.pkl',
    }
    
    data_files = {
        'User-Product Matrix': 'data/user_product_matrix.csv',
        'User Segments': 'data/user_segments.csv',
        'Popular Products': 'data/popular_products.csv',
    }
    
    print("\n Trained Models:")
    for name, file in models.items():
        status = " Exists" if os.path.exists(file) else "✗ Missing"
        size = f"({os.path.getsize(file) / 1024:.1f} KB)" if os.path.exists(file) else ""
        print(f"  {status} - {name} {size}")
    
    print("\n Generated Data Files:")
    for name, file in data_files.items():
        status = " Exists" if os.path.exists(file) else "✗ Missing"
        size = f"({os.path.getsize(file) / 1024:.1f} KB)" if os.path.exists(file) else ""
        print(f"  {status} - {name} {size}")
    
    print("\n" + "="*60)


if __name__ == "__main__":
    print("\n" + "="*60)
    print("🚀 AI E-commerce ML Training Pipeline")
    print("="*60)
    print(f"Started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"Working directory: {os.getcwd()}\n")
    
    try:
        # Check write permissions
        test_file = 'data/test_write.txt'
        try:
            with open(test_file, 'w') as f:
                f.write('test')
            os.remove(test_file)
            print(" Write permissions verified\n")
        except:
            print(" No write permissions in current directory!")
            print("   Please run as administrator or check folder permissions\n")
        
        # Train models
        cart_model, cart_scaler = train_cart_abandonment_model()
        segment_model, segment_scaler, segment_names = train_user_segmentation()
        recommendation_matrix = train_product_recommendation_matrix()
        popular_products = analyze_product_popularity()
        
        # Generate report
        generate_training_report()
        
        print("\n" + "="*60)
        print(" ALL TRAINING COMPLETED SUCCESSFULLY!")
        print("="*60)
        print(f"Finished at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        
    except KeyboardInterrupt:
        print("\n\n Training interrupted by user")
    except Exception as e:
        print(f"\n Training failed with error: {e}")
        import traceback
        traceback.print_exc()
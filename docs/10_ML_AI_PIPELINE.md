# ML / AI Pipeline Documentation

## Overview

The AI layer consists of two main ML pipelines:
1. **Recommendation Engine** — real-time product recommendations
2. **Predictive Analytics** — cart abandonment risk & user segmentation

---

## Recommendation Pipeline

```mermaid
flowchart TD
    subgraph DATA_COLLECTION["Data Collection"]
        EV["User Behavior Events\n(ClickHouse)\nproduct_view | add_to_cart | purchase"]
    end

    subgraph PREPROCESSING["Preprocessing"]
        MATRIX["Build User-Product\nInteraction Matrix\n(user × product counts)"]
        COVIEW["Build Co-View\nFrequency Matrix\n(product × product)"]
    end

    subgraph MODELS["Recommendation Models"]
        CF["Collaborative Filtering\nCosine Similarity\non User-Product Matrix"]
        CB["Content-Based\nCo-View Frequency\n(products viewed together)"]
        POP["Popularity Model\nPurchase Count\nfrom ClickHouse"]
        TREND["Trending Model\nGrowth Rate =\ncurrent / previous period"]
    end

    subgraph SERVING["Real-Time Serving"]
        CACHE["Redis Cache\nTTL: 30min (similar)\nTTL: 1hr (collaborative)"]
        API["FastAPI Endpoints\n/recommendations/*"]
    end

    subgraph RESULT["Output"]
        RECS["Ranked Product ID List\n→ Laravel fetches details\n→ Displayed in UI"]
    end

    EV --> MATRIX & COVIEW
    MATRIX --> CF
    COVIEW --> CB
    EV --> POP & TREND
    CF & CB & POP & TREND --> API
    API --> CACHE
    CACHE --> RECS
```

---

## Cart Abandonment Prediction Pipeline

```mermaid
flowchart TD
    subgraph TRAIN["Training (train.py)"]
        RAW["ClickHouse Events\n(historical)"]
        FEAT["Feature Engineering\ncart_total\nitem_count\ntime_on_site\nprevious_purchases\nsession_views"]
        LABEL["Label Generation\n1 = abandoned cart\n0 = completed purchase"]
        SCALE["StandardScaler\n(fit on train set)"]
        FIT["RandomForestClassifier\n(100 estimators)\nFit on training data"]
        SAVE["joblib.dump()\n→ cart_abandonment_model.pkl\n→ cart_abandonment_scaler.pkl"]
    end

    subgraph INFERENCE["Inference (main.py)"]
        INPUT["POST /predictions/cart-abandonment\n{cart_total, item_count, time_on_site,\nprevious_purchases, session_views}"]
        LOAD["Load .pkl models"]
        TRANS["scaler.transform(features)"]
        PRED["model.predict_proba(X)\n→ abandonment probability"]
        OUT["Response:\n{risk_score: 0.78,\noffer_discount: true/false,\nsegment: 'at_risk'}"]
    end

    RAW --> FEAT --> LABEL
    FEAT --> SCALE
    SCALE --> FIT
    FIT --> SAVE
    SAVE -.->|"loaded at startup"| LOAD

    INPUT --> LOAD --> TRANS --> PRED --> OUT
```

---

## User Segmentation Pipeline

```mermaid
flowchart TD
    subgraph TRAIN_SEG["Training (train.py)"]
        EV2["ClickHouse Events\n(user behavior)"]
        FEAT2["Feature Engineering per User\n- total_purchases\n- total_spent\n- avg_order_value\n- view_count\n- cart_count\n- purchase_rate\n- days_since_last_activity"]
        SCALE2["StandardScaler"]
        KMEANS["KMeans(n_clusters=5)\nFit on user features"]
        NAME["Assign Segment Names:\n0 → VIP Customers\n1 → High Spenders\n2 → At Risk\n3 → Frequent Browsers\n4 → Casual Shoppers"]
        SAVE2["Save:\n- user_segmentation_model.pkl\n- user_segmentation_scaler.pkl\n- segment_names.pkl"]
    end

    subgraph INFER_SEG["Inference"]
        INPUT2["POST /analytics/user-segment\n{user_id}"]
        LOAD2["Load KMeans + Scaler + Names"]
        FETCH["Fetch user features\nfrom ClickHouse"]
        TRANS2["scaler.transform(features)"]
        PRED2["model.predict(X)"]
        MAP["Map cluster_id → segment_name"]
        OUT2["Response:\n{segment: 'VIP Customers',\ncluster_id: 0,\nfeatures: {...}}"]
    end

    EV2 --> FEAT2 --> SCALE2 --> KMEANS --> NAME --> SAVE2
    SAVE2 -.-> LOAD2
    INPUT2 --> LOAD2
    LOAD2 --> FETCH
    FETCH --> TRANS2 --> PRED2 --> MAP --> OUT2
```

---

## User Segmentation Definitions

| Segment | Behavior Profile | Marketing Action |
|---|---|---|
| **VIP Customers** | High spend, frequent purchases, recent activity | Loyalty rewards, early access |
| **High Spenders** | High order values, moderate frequency | Upsell premium products |
| **At Risk** | Previously active, now dormant | Win-back campaigns, discounts |
| **Frequent Browsers** | High view count, low purchases | Conversion nudges, discounts |
| **Casual Shoppers** | Low activity across all metrics | Awareness campaigns |

---

## AI Feature Importance & Model Metrics

```mermaid
graph LR
    subgraph CART_FEAT["Cart Abandonment Features"]
        F1["cart_total (weight: high)"]
        F2["item_count (weight: medium)"]
        F3["time_on_site (weight: medium)"]
        F4["previous_purchases (weight: high)"]
        F5["session_views (weight: low)"]
    end

    subgraph SEG_FEAT["Segmentation Features"]
        S1["total_purchases"]
        S2["total_spent"]
        S3["avg_order_value"]
        S4["view_count"]
        S5["cart_count"]
        S6["purchase_rate"]
        S7["days_since_last_activity"]
    end

    RF["RandomForest\nCart Abandonment\nModel"]
    KM["KMeans\nUser Segmentation\nModel"]

    CART_FEAT --> RF
    SEG_FEAT --> KM
```

---

## Recommendation Algorithm Decision Logic

```mermaid
flowchart TD
    REQ["Recommendation Request\narrives at Laravel"]

    AUTH{"Is user\nlogged in?"}
    HIST{"User has\npurchase history?"}

    COLLAB["Collaborative Filtering\n(personalized for this user)"]
    CONTENT["Content-Based\n(similar to viewed product)"]
    POPULAR["Popular Products\n(best sellers)"]
    COLD["Cold Start:\nFeatured Products\nfrom MySQL"]

    REQ --> AUTH
    AUTH -- "Yes" --> HIST
    AUTH -- "No" --> POPULAR
    HIST -- "Yes (≥3 interactions)" --> COLLAB
    HIST -- "No" --> CONTENT
    CONTENT --> POPULAR

    COLLAB --> CACHE_CHECK{"Redis\ncache hit?"}
    POPULAR --> CACHE_CHECK
    CACHE_CHECK -- "Yes" --> SERVE["Return cached IDs\n→ Fetch from MySQL"]
    CACHE_CHECK -- "No" --> COMPUTE["Query ClickHouse\n+ Run Algorithm"]
    COMPUTE --> STORE["Store in Redis\n(TTL 30-60min)"]
    STORE --> SERVE
    POPULAR -- "Empty result" --> COLD
```

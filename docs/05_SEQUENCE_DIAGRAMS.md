# Sequence Diagrams

## 1. User Registration & Login Flow

```mermaid
sequenceDiagram
    actor U as User
    participant B as Browser
    participant L as Laravel (9090)
    participant DB as MySQL

    U->>B: Navigate to /register
    B->>L: GET /register
    L-->>B: Registration Form

    U->>B: Fill name, email, password
    B->>L: POST /register
    L->>DB: INSERT users (hashed password)
    DB-->>L: User created
    L->>L: Auth::login(user)
    L-->>B: Redirect → /account

    Note over U,DB: Login Flow
    U->>B: Navigate to /login
    B->>L: GET /login
    L-->>B: Login Form

    U->>B: Enter email, password
    B->>L: POST /login
    L->>DB: SELECT user WHERE email=?
    DB-->>L: User record
    L->>L: Hash::check(password)
    L->>L: Session::put(user_id)
    L-->>B: Redirect → /account
```

---

## 2. Product Browse & Recommendation Flow

```mermaid
sequenceDiagram
    actor U as User
    participant B as Browser
    participant L as Laravel (9090)
    participant E as Event Service (8000)
    participant A as AI Service (8001)
    participant R as Redis
    participant C as ClickHouse
    participant DB as MySQL

    U->>B: Click product
    B->>L: GET /products/{slug}

    L->>DB: SELECT * FROM products WHERE slug=?
    DB-->>L: Product data

    L->>DB: SELECT * FROM reviews WHERE product_id=? AND is_approved=1
    DB-->>L: Reviews

    par Track Event
        L->>E: POST /collect {event_type: product_view, product_id, user_id}
        E->>C: INSERT INTO events (async)
    and Get Recommendations
        L->>R: GET rec:similar:{product_id}
        alt Cache hit
            R-->>L: Cached similar products
        else Cache miss
            L->>A: POST /recommendations/similar {product_id}
            A->>C: SELECT co-view data
            C-->>A: Co-view counts
            A->>A: Compute cosine similarity
            A->>R: SET rec:similar:{product_id} TTL 30min
            A-->>L: Similar product IDs
            L->>DB: SELECT products WHERE id IN (...)
            DB-->>L: Product details
        end
    end

    L-->>B: Render product page + recommendations
```

---

## 3. Add to Cart & Cart Abandonment Prediction

```mermaid
sequenceDiagram
    actor U as User
    participant B as Browser
    participant L as Laravel (9090)
    participant E as Event Service (8000)
    participant A as AI Service (8001)
    participant S as Session Store

    U->>B: Click "Add to Cart"
    B->>L: POST /cart/add {product_id, quantity}

    L->>S: ShoppingCart::add(product)
    S-->>L: Cart updated

    L->>E: POST /collect {event_type: add_to_cart, product_id, price}
    E-->>L: 200 OK

    L->>A: POST /predictions/cart-abandonment\n{cart_total, item_count, time_on_site, ...}
    A->>A: Load RandomForest model
    A->>A: Predict abandonment probability
    A-->>L: {risk: 0.78, offer_discount: true}

    alt High abandonment risk
        L-->>B: Cart page + 5% discount banner
    else Low risk
        L-->>B: Cart page (normal)
    end
```

---

## 4. Checkout & Order Creation Flow

```mermaid
sequenceDiagram
    actor U as User
    participant B as Browser
    participant L as Laravel (9090)
    participant DB as MySQL
    participant E as Event Service (8000)
    participant S as Session Store

    U->>B: Go to /checkout
    B->>L: GET /checkout
    L->>S: Get cart items
    S-->>L: Cart contents
    L-->>B: Checkout form with cart summary

    U->>B: Fill address, select payment
    B->>L: POST /checkout\n{address, payment_method}

    L->>L: Validate inputs
    L->>DB: BEGIN TRANSACTION
    L->>DB: INSERT orders\n{order_number, user_id, subtotal, tax, shipping, total}
    DB-->>L: Order ID

    loop For each cart item
        L->>DB: INSERT order_items\n{order_id, product_id, name, price, qty}
        L->>DB: UPDATE products SET stock = stock - qty
    end

    L->>DB: COMMIT

    L->>S: ShoppingCart::clear()

    L->>E: POST /collect\n{event_type: purchase, order_total, items[]}
    E-->>L: 200 OK

    L-->>B: Redirect /checkout/success/{order_id}
    B->>L: GET /checkout/success/{order_id}
    L->>DB: SELECT order + items
    L-->>B: Order confirmation page
```

---

## 5. Homepage Personalization Flow

```mermaid
sequenceDiagram
    actor U as User
    participant B as Browser
    participant L as Laravel (9090)
    participant A as AI Service (8001)
    participant E as Event Service (8000)
    participant R as Redis
    participant DB as MySQL

    U->>B: Navigate to /
    B->>L: GET /

    L->>DB: SELECT settings (which sections to show)

    par Collaborative Recs (logged in only)
        L->>R: GET rec:collab:{user_id}
        note right of R: Cache miss path →
        L->>A: POST /recommendations/collaborative\n{user_id}
        A->>A: Cosine similarity on user-product matrix
        A->>R: Cache 1hr
        A-->>L: Recommended product IDs
    and Popular Products
        L->>A: POST /recommendations/popular
        A->>E: GET /analytics/products (top purchased)
        A-->>L: Popular product IDs
    and Trending Products
        L->>A: POST /recommendations/trending
        A-->>L: Trending product IDs
    and Flash Deals
        L->>DB: SELECT flash_deals WHERE active + products
        DB-->>L: Active flash deals
    and Hero Slides
        L->>DB: SELECT hero_slides WHERE active ORDER BY sort_order
        DB-->>L: Slides
    end

    L->>DB: SELECT products WHERE id IN (all_ids)
    DB-->>L: Product details
    L-->>B: Rendered homepage
```

---

## 6. Admin Analytics Flow

```mermaid
sequenceDiagram
    actor A as Admin
    participant B as Browser
    participant L as Laravel (9090)
    participant E as Event Service (8000)
    participant AI as AI Service (8001)
    participant DB as MySQL
    participant C as ClickHouse

    A->>B: Navigate to /admin/analytics
    B->>L: GET /admin/analytics
    L->>L: Middleware: auth + isAdmin

    par Fetch Overview Stats
        L->>E: GET /analytics/overview?days=30
        E->>C: SELECT count(*), countDistinct(user_id)...
        C-->>E: Aggregated stats
        E-->>L: {total_events, unique_users, sessions}
    and Fetch Timeline
        L->>E: GET /analytics/timeline?days=30
        E->>C: SELECT date, event_type, count(*) GROUP BY date, type
        C-->>E: Timeline data
        E-->>L: [{date, event_type, count}]
    and Fetch Top Products
        L->>E: GET /analytics/products
        E->>C: SELECT product_id, COUNT(*) by type
        C-->>E: Top product IDs
        E-->>L: [{product_id, views, carts, purchases}]
    and DB Revenue Stats
        L->>DB: SELECT SUM(total) FROM orders WHERE status != 'cancelled'
        L->>DB: SELECT COUNT(*) FROM orders GROUP BY status
        DB-->>L: Revenue + status counts
    end

    L-->>B: Analytics dashboard

    Note over A,C: AJAX event log refresh
    A->>B: Scroll / paginate events table
    B->>L: GET /admin/analytics/events?page=2
    L->>E: GET /analytics/recent?page=2&per_page=50
    E->>C: SELECT * FROM events ORDER BY timestamp DESC LIMIT 50 OFFSET 50
    C-->>E: Event rows
    E-->>L: Paginated events JSON
    L-->>B: JSON response → update table
```

---

## 7. AI Model Retraining Flow

```mermaid
sequenceDiagram
    actor ADM as Admin
    participant L as Laravel (9090)
    participant A as AI Service (8001)
    participant C as ClickHouse
    participant FS as File System (models/)

    ADM->>L: POST /admin/ai-retrain
    L->>L: Check isAdmin()
    L->>A: POST /train

    A->>A: Spawn background thread

    A->>C: SELECT product_view events → user-product matrix
    C-->>A: Interaction data

    A->>C: SELECT purchase + cart events → cart abandonment training data
    C-->>A: Abandonment data

    A->>C: SELECT user behavior → user features
    C-->>A: User feature data

    A->>A: Fit RandomForestClassifier (cart abandonment)
    A->>A: Fit KMeans (user segmentation)
    A->>A: Build user-product interaction matrix

    A->>FS: Save cart_abandonment_model.pkl
    A->>FS: Save cart_abandonment_scaler.pkl
    A->>FS: Save user_segmentation_model.pkl
    A->>FS: Save user_segmentation_scaler.pkl
    A->>FS: Save segment_names.pkl

    A-->>L: {status: "retraining started"}
    L-->>ADM: Success notification
```

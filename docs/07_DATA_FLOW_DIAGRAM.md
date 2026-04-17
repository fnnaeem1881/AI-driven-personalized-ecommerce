# Data Flow Diagrams

## Level 0 — Context Diagram (System Overview)

```mermaid
flowchart LR
    CUST(["👤 Customer"])
    ADMIN(["👨‍💼 Admin"])
    SYS[["TechNova\nE-Commerce\nSystem"]]
    PAY(["💳 Payment\nGateway"])
    CH(["📊 ClickHouse\nAnalytics"])

    CUST -- "Browse, Search, Buy, Review" --> SYS
    SYS -- "Product pages, Order confirms, Recommendations" --> CUST
    ADMIN -- "Manage products, orders, users, settings" --> SYS
    SYS -- "Analytics reports, AI health status" --> ADMIN
    SYS -- "Payment request" --> PAY
    PAY -- "Payment result" --> SYS
    SYS -- "Behavior events" --> CH
    CH -- "Analytics data" --> SYS
```

---

## Level 1 — Major Process Decomposition

```mermaid
flowchart TD
    subgraph INPUTS["External Inputs"]
        U1["Customer Request"]
        U2["Admin Request"]
    end

    subgraph PROCESSES["Core Processes"]
        P1["1.0\nAuthentication\n& Authorization"]
        P2["2.0\nProduct\nManagement"]
        P3["3.0\nCart &\nCheckout"]
        P4["4.0\nOrder\nManagement"]
        P5["5.0\nAI\nRecommendations"]
        P6["6.0\nEvent\nTracking"]
        P7["7.0\nAdmin\nAnalytics"]
    end

    subgraph STORES["Data Stores"]
        D1[("D1: Users")]
        D2[("D2: Products")]
        D3[("D3: Categories")]
        D4[("D4: Orders")]
        D5[("D5: Cart Session")]
        D6[("D6: ClickHouse Events")]
        D7[("D7: Redis Cache")]
        D8[("D8: ML Models")]
        D9[("D9: Settings")]
    end

    U1 --> P1 --> D1
    P1 -- "Auth token" --> P2 & P3 & P4

    U1 --> P2
    P2 --> D2 & D3
    D2 --> P2

    U1 --> P3
    P3 --> D5
    P3 --> D4
    P3 --> P6

    U1 --> P5
    P5 --> D7
    P5 --> D6
    P5 --> D8

    P6 --> D6

    U2 --> P7
    P7 --> D6

    U2 --> P2
    U2 --> P4
    P4 --> D4
```

---

## Level 2 — AI Recommendation Process (Process 5.0)

```mermaid
flowchart TD
    IN1["User ID (logged in)"]
    IN2["Product ID (current page)"]
    IN3["Session context"]

    subgraph P5["5.0 AI Recommendation Engine"]
        P51["5.1 Check Redis Cache"]
        P52["5.2 Collaborative Filter\n(user-product matrix)"]
        P53["5.3 Content-Based Filter\n(co-view similarity)"]
        P54["5.4 Popular Products\n(ClickHouse counts)"]
        P55["5.5 Trending Products\n(growth rate)"]
        P56["5.6 Merge & Rank\nResults"]
        P57["5.7 Fetch Product Details\nfrom MySQL"]
        P58["5.8 Write to\nRedis Cache"]
    end

    D1[("Redis Cache")]
    D2[("ClickHouse Events")]
    D3[("User-Product Matrix CSV")]
    D4[("MySQL Products")]

    OUT["Ranked Product\nRecommendations"]

    IN1 & IN2 & IN3 --> P51
    P51 -- "Cache hit" --> P57
    P51 -- "Cache miss" --> P52 & P53 & P54 & P55
    P52 --> D3
    P53 --> D2
    P54 --> D2
    P55 --> D2
    P52 & P53 & P54 & P55 --> P56
    P56 --> P58
    P58 --> D1
    P56 --> P57
    P57 --> D4
    P57 --> OUT
```

---

## Level 2 — Event Tracking & Analytics (Process 6.0 & 7.0)

```mermaid
flowchart TD
    subgraph EVENTS_IN["Event Sources"]
        EV1["product_view\n(ProductController)"]
        EV2["add_to_cart\n(CartController)"]
        EV3["purchase\n(CheckoutController)"]
        EV4["search\n(SearchController)"]
        EV5["wishlist_toggle\n(WishlistController)"]
    end

    subgraph P6["6.0 Event Collection"]
        P61["6.1 EventTracker::track()\n(Laravel Service)"]
        P62["6.2 HTTP POST /collect\n(Event Service)"]
        P63["6.3 Build Event Payload\n(UUID, timestamp,\nuser_id, session_id)"]
        P64["6.4 Async Queue\n(Redis optional)"]
        P65["6.5 Write to ClickHouse"]
    end

    subgraph P7["7.0 Analytics Processing"]
        P71["7.1 Overview Aggregation\n(total events, users)"]
        P72["7.2 Timeline\n(daily by type)"]
        P73["7.3 Top Products\n(by purchase/view)"]
        P74["7.4 Top Users\n(by activity)"]
        P75["7.5 Paginated Event Log"]
    end

    D6[("ClickHouse\nevents table")]

    ADMIN["Admin Dashboard"]

    EV1 & EV2 & EV3 & EV4 & EV5 --> P61
    P61 --> P62 --> P63 --> P64 & P65
    P64 --> P65
    P65 --> D6

    D6 --> P71 & P72 & P73 & P74 & P75
    P71 & P72 & P73 & P74 & P75 --> ADMIN
```

---

## Level 2 — Order Processing (Process 3.0 & 4.0)

```mermaid
flowchart TD
    U["Customer"]

    subgraph P3["3.0 Cart & Checkout"]
        P31["3.1 Add to Cart\n(Session-based)"]
        P32["3.2 View Cart\n+ Abandonment Prediction"]
        P33["3.3 Checkout Form\nValidation"]
        P34["3.4 Create Order\n(Transaction)"]
        P35["3.5 Create Order Items\n+ Update Stock"]
        P36["3.6 Clear Cart\n+ Track Purchase"]
    end

    subgraph P4["4.0 Order Management"]
        P41["4.1 View Orders\n(Customer)"]
        P42["4.2 View Order Detail"]
        P43["4.3 Update Status\n(Admin)"]
        P44["4.4 Update Payment\n(Admin)"]
    end

    D4[("Orders")]
    D5[("Cart Session")]
    D2[("Products")]
    D6[("Events / ClickHouse")]
    D7[("AI Service")]

    U --> P31 --> D5
    D5 --> P32
    P32 --> D7
    D7 -- "abandonment risk" --> P32
    P32 --> P33 --> P34
    P34 --> D4
    P34 --> P35
    P35 --> D2
    P35 --> P36
    P36 --> D5
    P36 --> D6

    U --> P41 --> D4
    D4 --> P42
    D4 --> P43 --> D4
    D4 --> P44 --> D4
```

# Use Case Diagrams

## System Actors

| Actor | Description |
|---|---|
| **Guest** | Unauthenticated visitor |
| **Customer** | Registered, logged-in user |
| **Admin** | User with `role = admin` or Spatie admin role |
| **AI Service** | ML microservice (automated actor) |
| **Event Service** | Analytics microservice (automated actor) |

---

## Customer Use Cases

```mermaid
graph LR
    subgraph SYSTEM["TechNova Store System"]
        UC1["Browse Products"]
        UC2["Search Products"]
        UC3["View Product Detail"]
        UC4["View Recommendations"]
        UC5["Add to Cart"]
        UC6["View Cart"]
        UC7["Checkout"]
        UC8["View Orders"]
        UC9["Track Order"]
        UC10["Register Account"]
        UC11["Login / Logout"]
        UC12["Update Profile"]
        UC13["Manage Wishlist"]
        UC14["Write Review"]
        UC15["View Flash Deals"]
        UC16["Filter by Category"]
        UC17["Filter by Price / Brand"]
    end

    GUEST(("Guest"))
    CUSTOMER(("Customer"))

    GUEST --> UC1 & UC2 & UC3 & UC15 & UC10 & UC11 & UC5 & UC6
    CUSTOMER --> UC7 & UC8 & UC9 & UC12 & UC13 & UC14
    CUSTOMER --> UC1 & UC2 & UC3 & UC15 & UC16 & UC17

    UC3 --> UC4
    UC1 --> UC16 & UC17
    UC7 --> UC8
```

---

## Admin Use Cases

```mermaid
graph LR
    subgraph ADMIN_SYSTEM["Admin Panel (/admin)"]
        direction TB
        subgraph PRODUCTS["Product Management"]
            A1["Create Product"]
            A2["Edit Product"]
            A3["Delete Product"]
            A4["Upload Product Images"]
            A5["Manage Categories"]
        end
        subgraph ORDERS["Order Management"]
            A6["View All Orders"]
            A7["Update Order Status"]
            A8["Update Payment Status"]
        end
        subgraph USERS["User Management"]
            A9["View All Users"]
            A10["Assign User Role"]
            A11["Create User"]
        end
        subgraph MARKETING["Marketing"]
            A12["Manage Flash Deals"]
            A13["Manage Hero Slides"]
            A14["Configure Settings"]
        end
        subgraph ANALYTICS_UC["Analytics & AI"]
            A15["View Dashboard"]
            A16["View Analytics"]
            A17["View AI Health"]
            A18["Trigger Model Retrain"]
            A19["View Event Log"]
        end
        subgraph RBAC["Access Control"]
            A20["Manage Roles"]
            A21["Manage Permissions"]
            A22["Assign Permissions to Roles"]
        end
    end

    ADMIN(("Admin"))
    ADMIN --> A1 & A2 & A3 & A4 & A5
    ADMIN --> A6 & A7 & A8
    ADMIN --> A9 & A10 & A11
    ADMIN --> A12 & A13 & A14
    ADMIN --> A15 & A16 & A17 & A18 & A19
    ADMIN --> A20 & A21 & A22
```

---

## Automated Actor Use Cases

```mermaid
graph LR
    subgraph AI_CASES["AI Service Interactions"]
        AI1["Serve Collaborative\nRecommendations"]
        AI2["Serve Content-Based\nRecommendations"]
        AI3["Serve Popular Products"]
        AI4["Serve Trending Products"]
        AI5["Predict Cart Abandonment"]
        AI6["Segment User"]
        AI7["Report Model Performance"]
        AI8["Retrain ML Models"]
    end

    subgraph EVENT_CASES["Event Service Interactions"]
        EV1["Collect product_view"]
        EV2["Collect add_to_cart"]
        EV3["Collect purchase"]
        EV4["Collect search"]
        EV5["Collect wishlist_toggle"]
        EV6["Serve Timeline Analytics"]
        EV7["Serve Top Products Analytics"]
        EV8["Serve Event Overview"]
    end

    AI_SVC(("AI Service"))
    EV_SVC(("Event Service"))
    LARAVEL(("Laravel"))

    LARAVEL --> AI1 & AI2 & AI3 & AI4 & AI5 & AI6
    LARAVEL --> EV1 & EV2 & EV3 & EV4 & EV5
    LARAVEL --> EV6 & EV7 & EV8
    LARAVEL --> AI7

    AI_SVC --> AI8
    AI_SVC --> AI1 & AI2 & AI3 & AI4 & AI5 & AI6 & AI7
    EV_SVC --> EV1 & EV2 & EV3 & EV4 & EV5 & EV6 & EV7 & EV8
```

---

## Full System Use Case Table

| Use Case | Actor | Route | Method |
|---|---|---|---|
| Browse Products | Guest/Customer | /products | GET |
| Search Products | Guest/Customer | /search | GET |
| View Product | Guest/Customer | /products/{slug} | GET |
| View Flash Deals | Guest/Customer | /flash-deals | GET |
| Add to Cart | Guest/Customer | /cart/add | POST |
| View Cart | Guest/Customer | /cart | GET |
| Update Cart | Guest/Customer | /cart/update | POST |
| Remove from Cart | Guest/Customer | /cart/remove | POST |
| Register | Guest | /register | POST |
| Login | Guest | /login | POST |
| Logout | Customer | /logout | POST |
| Checkout | Customer | /checkout | GET/POST |
| View Orders | Customer | /orders | GET |
| View Order Detail | Customer | /orders/{id} | GET |
| Update Profile | Customer | /account/profile | POST |
| Toggle Wishlist | Customer | /wishlist/toggle | POST |
| Admin Dashboard | Admin | /admin | GET |
| Manage Products | Admin | /admin/products | CRUD |
| Manage Categories | Admin | /admin/categories | CRUD |
| Manage Orders | Admin | /admin/orders | GET/PATCH |
| Manage Users | Admin | /admin/users | GET/POST/PATCH |
| View Analytics | Admin | /admin/analytics | GET |
| AI Health Check | Admin | /admin/ai-health | GET |
| Retrain AI | Admin | /admin/ai-retrain | POST |
| Manage Flash Deals | Admin | /admin/flash-deals | CRUD |
| Manage Hero Slides | Admin | /admin/slides | CRUD |
| Manage Settings | Admin | /admin/settings | GET/POST |
| Manage Roles | Admin | /admin/roles | CRUD |
| Manage Permissions | Admin | /admin/permissions | CRUD |

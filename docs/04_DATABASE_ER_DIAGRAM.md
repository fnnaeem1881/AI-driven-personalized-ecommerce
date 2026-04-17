# Database ER Diagram

## Entity-Relationship Diagram (MySQL — technova_store)

```mermaid
erDiagram

    USERS {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        varchar phone
        text address
        varchar city
        varchar state
        varchar zip
        varchar country
        varchar avatar
        varchar role
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    CATEGORIES {
        bigint id PK
        varchar name
        varchar slug UK
        varchar icon
        text description
        varchar image
        bigint parent_id FK
        int sort_order
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }

    PRODUCTS {
        bigint id PK
        bigint category_id FK
        varchar name
        varchar slug UK
        text description
        text short_description
        decimal price
        decimal compare_price
        int stock
        varchar sku UK
        varchar brand
        decimal rating
        int reviews_count
        varchar image
        json specs
        tinyint is_featured
        tinyint is_active
        tinyint is_flash_deal
        timestamp flash_deal_ends_at
        decimal flash_deal_discount
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_IMAGES {
        bigint id PK
        bigint product_id FK
        varchar image_path
        int sort_order
        timestamp created_at
        timestamp updated_at
    }

    ORDERS {
        bigint id PK
        bigint user_id FK
        varchar order_number UK
        enum status
        decimal subtotal
        decimal tax
        decimal shipping
        decimal total
        varchar payment_method
        enum payment_status
        json shipping_address
        varchar tracking_number
        text notes
        timestamp shipped_at
        timestamp delivered_at
        timestamp created_at
        timestamp updated_at
    }

    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        varchar product_name
        varchar product_image
        decimal price
        int quantity
        decimal total
        timestamp created_at
        timestamp updated_at
    }

    WISHLISTS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        timestamp created_at
        timestamp updated_at
    }

    REVIEWS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        int rating
        varchar title
        text body
        tinyint is_approved
        timestamp created_at
        timestamp updated_at
    }

    FLASH_DEALS {
        bigint id PK
        varchar title
        text description
        decimal discount_percent
        timestamp starts_at
        timestamp ends_at
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }

    FLASH_DEAL_PRODUCT {
        bigint flash_deal_id FK
        bigint product_id FK
    }

    HERO_SLIDES {
        bigint id PK
        varchar badge
        varchar badge_color
        varchar title
        text subtitle
        text description
        varchar image
        varchar cta_text
        varchar cta_link
        varchar cta_secondary_text
        varchar cta_secondary_link
        int sort_order
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }

    SETTINGS {
        bigint id PK
        varchar key UK
        text value
        timestamp created_at
        timestamp updated_at
    }

    ROLES {
        bigint id PK
        varchar name
        varchar guard_name
        timestamp created_at
        timestamp updated_at
    }

    PERMISSIONS {
        bigint id PK
        varchar name
        varchar guard_name
        varchar group_name
        timestamp created_at
        timestamp updated_at
    }

    MODEL_HAS_ROLES {
        bigint role_id FK
        varchar model_type
        bigint model_id
    }

    MODEL_HAS_PERMISSIONS {
        bigint permission_id FK
        varchar model_type
        bigint model_id
    }

    ROLE_HAS_PERMISSIONS {
        bigint permission_id FK
        bigint role_id FK
    }

    PASSWORD_RESET_TOKENS {
        varchar email PK
        varchar token
        timestamp created_at
    }

    CACHE {
        varchar key PK
        mediumtext value
        int expiration
    }

    USERS ||--o{ ORDERS : "places"
    USERS ||--o{ WISHLISTS : "saves"
    USERS ||--o{ REVIEWS : "writes"
    USERS ||--o{ MODEL_HAS_ROLES : "has"
    USERS ||--o{ MODEL_HAS_PERMISSIONS : "has"

    CATEGORIES ||--o{ PRODUCTS : "contains"
    CATEGORIES ||--o{ CATEGORIES : "parent_of"

    PRODUCTS ||--o{ PRODUCT_IMAGES : "has"
    PRODUCTS ||--o{ ORDER_ITEMS : "included_in"
    PRODUCTS ||--o{ WISHLISTS : "wishlisted_by"
    PRODUCTS ||--o{ REVIEWS : "reviewed_in"
    PRODUCTS }o--o{ FLASH_DEALS : "flash_deal_product"

    ORDERS ||--o{ ORDER_ITEMS : "contains"

    FLASH_DEALS ||--o{ FLASH_DEAL_PRODUCT : "has"
    PRODUCTS ||--o{ FLASH_DEAL_PRODUCT : "has"

    ROLES ||--o{ MODEL_HAS_ROLES : "assigned_via"
    ROLES ||--o{ ROLE_HAS_PERMISSIONS : "has"

    PERMISSIONS ||--o{ MODEL_HAS_PERMISSIONS : "assigned_via"
    PERMISSIONS ||--o{ ROLE_HAS_PERMISSIONS : "belongs_to"
```

---

## Table Descriptions

### Core Tables

| Table | Purpose | Key Columns |
|---|---|---|
| `users` | Customer & admin accounts | `role` (admin/user), `avatar`, address fields |
| `categories` | Product taxonomy (hierarchical) | `parent_id` → self-join, `slug` |
| `products` | Product catalog | `slug` (route key), `specs` (JSON), `is_flash_deal`, `rating` |
| `product_images` | Multiple images per product | `sort_order` for ordering |
| `orders` | Purchase records | `status` enum, `shipping_address` JSON, `order_number` |
| `order_items` | Snapshot of products at purchase | Denormalized `product_name`, `price` |
| `wishlists` | Saved products | Junction: user ↔ product |
| `reviews` | Product ratings & text | `is_approved` moderation flag |

### Marketing Tables

| Table | Purpose |
|---|---|
| `flash_deals` | Time-limited promotion metadata |
| `flash_deal_product` | M:N junction: flash deal ↔ products |
| `hero_slides` | Homepage carousel slides |
| `settings` | Key-value site configuration |

### RBAC Tables (Spatie)

| Table | Purpose |
|---|---|
| `roles` | Named roles (admin, user, etc.) |
| `permissions` | Named permissions with `group_name` |
| `model_has_roles` | Polymorphic: model → role assignment |
| `model_has_permissions` | Polymorphic: model → permission assignment |
| `role_has_permissions` | Junction: role ↔ permissions |

### System Tables

| Table | Purpose |
|---|---|
| `cache` | Laravel file/database cache storage |
| `password_reset_tokens` | Email-based password reset flow |

---

## Order Status State Machine

```mermaid
stateDiagram-v2
    [*] --> pending : Order Created
    pending --> processing : Admin Processes
    processing --> shipped : Tracking Added
    shipped --> delivered : Delivery Confirmed
    pending --> cancelled : Cancel Request
    processing --> cancelled : Cancel Request

    state "Payment Status" as PS {
        [*] --> payment_pending
        payment_pending --> paid : Payment Confirmed
        payment_pending --> failed : Payment Failed
        paid --> refunded : Refund Issued
    }
```

# API Reference

## Laravel Web Routes

### Public Routes (No Authentication Required)

| Method | Route | Controller | Action |
|---|---|---|---|
| GET | `/` | HomeController | Homepage with recommendations |
| GET | `/products` | ProductController | Product listing with filters |
| GET | `/products/{slug}` | ProductController | Single product detail |
| GET | `/category/{slug}` | ProductController | Products by category |
| GET | `/search` | SearchController | Full-text search |
| GET | `/flash-deals` | FlashDealController | Active flash deals |
| GET | `/cart` | CartController | View cart |
| POST | `/cart/add` | CartController | Add product to cart |
| POST | `/cart/update` | CartController | Update cart quantity |
| POST | `/cart/remove` | CartController | Remove cart item |
| POST | `/cart/clear` | CartController | Clear entire cart |
| GET | `/login` | AuthController | Login form |
| POST | `/login` | AuthController | Process login |
| GET | `/register` | AuthController | Registration form |
| POST | `/register` | AuthController | Process registration |

### Authenticated Routes (Requires Login)

| Method | Route | Controller | Action |
|---|---|---|---|
| POST | `/logout` | AuthController | Logout |
| GET | `/checkout` | CheckoutController | Checkout form |
| POST | `/checkout` | CheckoutController | Process checkout |
| GET | `/checkout/success/{order}` | CheckoutController | Order success page |
| GET | `/orders` | OrderController | Order history |
| GET | `/orders/{id}` | OrderController | Order detail |
| GET | `/account` | AccountController | Account dashboard |
| GET | `/account/profile` | AccountController | Edit profile form |
| POST | `/account/profile` | AccountController | Update profile |
| GET | `/wishlist` | WishlistController | Wishlist page |
| POST | `/wishlist/toggle` | WishlistController | Add/remove from wishlist |

### Admin Routes (Requires Auth + Admin Role, Prefix: `/admin`)

| Method | Route | Controller | Action |
|---|---|---|---|
| GET | `/admin` | AdminController | Admin dashboard |
| GET | `/admin/analytics` | AdminController | Analytics overview |
| GET | `/admin/analytics/events` | AdminController | Events (AJAX JSON) |
| GET | `/admin/ai-health` | AdminController | AI health status |
| POST | `/admin/ai-retrain` | AdminController | Trigger model retrain |
| GET | `/admin/profile` | AdminProfileController | Admin profile page |
| POST | `/admin/profile` | AdminProfileController | Update admin profile |
| POST | `/admin/profile/password` | AdminProfileController | Change password |
| POST | `/admin/upload-image` | ImageUploadController | Upload image (AJAX) |
| **Products** | | | |
| GET | `/admin/products` | Admin\ProductController | Product list |
| GET | `/admin/products/create` | Admin\ProductController | Create form |
| POST | `/admin/products` | Admin\ProductController | Store product |
| GET | `/admin/products/{product}/edit` | Admin\ProductController | Edit form |
| PUT | `/admin/products/{product}` | Admin\ProductController | Update product |
| DELETE | `/admin/products/{product}` | Admin\ProductController | Delete product |
| **Categories** | | | |
| GET/POST | `/admin/categories` | Admin\CategoryController | CRUD (same pattern) |
| **Orders** | | | |
| GET | `/admin/orders` | Admin\OrderController | Order list |
| GET | `/admin/orders/{order}` | Admin\OrderController | Order detail |
| PATCH | `/admin/orders/{order}/status` | Admin\OrderController | Update order status |
| PATCH | `/admin/orders/{order}/payment` | Admin\OrderController | Update payment status |
| **Users** | | | |
| GET | `/admin/users` | Admin\UserController | User list |
| GET | `/admin/users/create` | Admin\UserController | Create form |
| POST | `/admin/users` | Admin\UserController | Store user |
| GET | `/admin/users/{user}` | Admin\UserController | User detail |
| PATCH | `/admin/users/{user}/role` | Admin\UserController | Update role (basic) |
| PATCH | `/admin/users/{user}/spatie-role` | Admin\UserController | Assign Spatie role |
| **Settings** | | | |
| GET | `/admin/settings` | Admin\SettingController | Settings page |
| POST | `/admin/settings` | Admin\SettingController | Update settings |
| **RBAC** | | | |
| GET/POST/PUT/DELETE | `/admin/roles` | Admin\RoleController | Roles CRUD |
| GET/POST/DELETE | `/admin/permissions` | Admin\PermissionController | Permissions CRUD |
| PATCH | `/admin/roles/{role}/permissions` | Admin\PermissionController | Assign permissions |
| **Marketing** | | | |
| GET/POST/PUT/DELETE | `/admin/flash-deals` | Admin\FlashDealController | Flash Deals CRUD |
| GET/POST/PUT/DELETE | `/admin/slides` | Admin\HeroSlideController | Hero Slides CRUD |

---

## AI Service API (FastAPI — Port 8001)

Interactive docs: `http://localhost:8001/docs`

### Recommendation Endpoints

| Method | Endpoint | Request Body | Response |
|---|---|---|---|
| POST | `/recommendations/collaborative` | `{user_id: int}` | `{recommendations: [{product_id, score}]}` |
| POST | `/recommendations/similar` | `{product_id: int}` | `{recommendations: [{product_id, similarity}]}` |
| POST | `/recommendations/popular` | `{limit: int}` | `{products: [{product_id, purchase_count}]}` |
| POST | `/recommendations/trending` | `{limit: int, days: int}` | `{products: [{product_id, growth_rate}]}` |

### Prediction Endpoints

| Method | Endpoint | Request Body | Response |
|---|---|---|---|
| POST | `/predictions/cart-abandonment` | `{cart_total, item_count, time_on_site, previous_purchases, session_views}` | `{risk_score: float, offer_discount: bool}` |
| POST | `/analytics/user-segment` | `{user_id: int}` | `{segment: string, cluster_id: int, features: {...}}` |

### Management Endpoints

| Method | Endpoint | Response |
|---|---|---|
| GET | `/health` | `{status, mysql, redis, clickhouse}` |
| GET | `/analytics/model-performance` | `{cart_abandonment: {...}, segmentation: {...}}` |
| POST | `/train` | `{status: "retraining started"}` |
| GET | `/users/{user_id}/profile` | `{segment, recommendations, cart_risk, features}` |

---

## Event Service API (FastAPI — Port 8000)

### Collection Endpoints

| Method | Endpoint | Request Body | Response |
|---|---|---|---|
| POST | `/collect` | Event payload (see below) | `{status: "ok", event_id}` |

**Event Payload Schema:**
```json
{
  "event_type": "product_view|add_to_cart|purchase|search|wishlist_toggle",
  "user_id": 123,
  "session_id": "sess_abc",
  "product_id": 456,
  "category_id": 7,
  "price": 99.99,
  "quantity": 1,
  "cart_total": 149.99,
  "product_name": "Laptop Pro",
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0..."
}
```

### Analytics Endpoints

| Method | Endpoint | Query Params | Response |
|---|---|---|---|
| GET | `/stats` | — | `{event_counts_by_type: {...}}` (last 7d) |
| GET | `/health` | — | `{clickhouse, redis, status}` |
| GET | `/analytics/overview` | `?days=30` | `{total_events, unique_users, unique_sessions}` |
| GET | `/analytics/timeline` | `?days=30` | `[{date, event_type, count}]` |
| GET | `/analytics/products` | `?limit=10` | `[{product_id, views, carts, purchases}]` |
| GET | `/analytics/users` | `?limit=10` | `[{user_id, events, views, purchases}]` |
| GET | `/analytics/recent` | `?page=1&per_page=50` | `{events: [...], total, page}` |
| GET | `/analytics/event-types` | — | `[{event_type, count, last_seen}]` |

# TechNova Store — Project Overview

## Project Name
**AI-Driven Personalized E-Commerce Platform** ("TechNova Store")

## Purpose
A full-stack e-commerce system with AI/ML-powered product recommendations, real-time user event tracking, behavioral analytics, and role-based administration — built as a microservices-based architecture.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend Web Framework | Laravel 11 (PHP 8.1+) |
| Templating | Blade (server-side rendering) |
| Styling | Tailwind CSS 3.4 |
| Frontend Build | Vite 6.0 |
| Primary Database | MySQL 8.0 |
| ORM | Laravel Eloquent |
| Session / Cache | Redis |
| AI/ML Service | Python 3.11 + FastAPI |
| Event Collection Service | Python 3.11 + FastAPI |
| Analytics Database | ClickHouse Cloud (Azure) |
| ML Libraries | scikit-learn, pandas, numpy, joblib |
| RBAC | spatie/laravel-permission |
| Cart | gloudemans/shoppingcart |
| HTTP Client | Illuminate\Support\Facades\Http |

---

## Runtime Ports

| Service | Port | URL |
|---|---|---|
| Laravel Backend | 9090 | http://localhost:9090 |
| AI Service | 8001 | http://localhost:8001 |
| Event Service | 8000 | http://localhost:8000 |
| MySQL | 3306 | localhost:3306 |
| Redis | 6379 | localhost:6379 |
| ClickHouse | 8443 (HTTPS) | Azure Cloud |

---

## Key Features

| Feature | Description |
|---|---|
| E-Commerce Core | Products, categories, cart (session-based), checkout, order lifecycle |
| User Authentication | Laravel session auth (register, login, logout, password reset) |
| Admin Panel | CRUD for products, categories, orders, users; analytics dashboard |
| AI Recommendations | Collaborative filtering, content-based, popular, trending |
| ML Predictions | Cart abandonment risk, user segmentation (5 segments) |
| Event Tracking | Real-time behavioral events stored in ClickHouse |
| Flash Deals | Time-limited percentage-off promotions |
| Wishlist | User-saved products |
| Reviews | Approved product ratings |
| RBAC | Spatie roles & permissions, custom permission groups |
| Hero Slider | Admin-managed homepage carousel |
| Search | Full-text + filter (category, brand, price range) |

---

## Microservice Architecture Summary

```
Browser / Client
      │
      ▼
Laravel (Port 9090)  ──── MySQL (port 3306)
      │
      ├──► AI Service (Port 8001)  ──── Redis + ClickHouse
      │
      └──► Event Service (Port 8000) ── Redis + ClickHouse
```

---

## Startup

```bash
# All-in-one (Windows)
start-all.bat

# Manual (3 terminals)
cd backend && php artisan serve --port=9090
cd services/ai-service && python main.py
cd services/event-service && python main.py
```

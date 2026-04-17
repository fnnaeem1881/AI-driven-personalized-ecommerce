# High-Level Architecture Diagram

> Rendered with [Mermaid](https://mermaid.js.org). Paste into any Mermaid-compatible viewer (GitHub, Notion, mermaid.live).

## System Architecture Overview

```mermaid
graph TB
    subgraph CLIENT["🌐 Client Layer"]
        BR["Browser\n(Desktop / Mobile)"]
    end

    subgraph LARAVEL["🟠 Laravel Backend  (Port 9090)"]
        direction TB
        WEB["Web Routes\n(routes/web.php)"]
        MW["Middleware\nIsAdmin | CheckMaintenance | Auth"]
        CTRL["Controllers\nHome | Product | Cart | Checkout\nOrder | Account | Wishlist | Search\nAdmin/* Controllers"]
        SVC["Services\nAIService | EventTracker"]
        MODEL["Eloquent Models\nUser | Product | Category\nOrder | Review | Wishlist\nFlashDeal | HeroSlide | Setting"]
        BLADE["Blade Views\n+ Tailwind CSS"]
        VITE["Vite Build\n(CSS/JS assets)"]
    end

    subgraph DB["🗄️ Primary Database (MySQL 8.0 — Port 3306)"]
        MYSQL[("technova_store\nDatabase")]
    end

    subgraph CACHE["⚡ Cache Layer"]
        REDIS[("Redis\nPort 6379\nSessions | Model Cache\nRec Cache")]
    end

    subgraph AI["🤖 AI Service (Python FastAPI — Port 8001)"]
        direction TB
        AIREC["Recommendation Engine\nCollaborative Filtering\nContent-Based\nPopular | Trending"]
        AIPRED["ML Predictions\nCart Abandonment\nUser Segmentation"]
        AIMOD["Serialized Models\n.pkl files\n(scikit-learn)"]
    end

    subgraph EVENT["📊 Event Service (Python FastAPI — Port 8000)"]
        direction TB
        ECOL["Event Collector\nPOST /collect"]
        EANAL["Analytics Engine\nTimeline | Top Products\nTop Users | Event Types"]
    end

    subgraph ANALYTICS["📈 Analytics Database (ClickHouse Cloud — Azure)"]
        CH[("ClickHouse\nevents table\npartitioned by month")]
    end

    BR -- "HTTP Requests" --> WEB
    WEB --> MW --> CTRL
    CTRL --> MODEL
    CTRL --> SVC
    CTRL --> BLADE
    BLADE -- "HTML Response" --> BR
    VITE -- "CSS/JS" --> BLADE
    MODEL <--> MYSQL
    MODEL <--> REDIS
    SVC -- "HTTP POST /recommendations/*\n/predictions/*" --> AI
    SVC -- "HTTP POST /collect" --> EVENT
    AIREC <--> REDIS
    AIREC <--> CH
    AIPRED <--> AIMOD
    ECOL --> CH
    ECOL --> REDIS
    EANAL <--> CH
    CTRL -- "Admin Analytics\nGET /analytics/*" --> EVENT
    CTRL -- "Admin AI Health\nGET /health" --> AI
```

---

## Layered Architecture Description

| Layer | Components | Responsibility |
|---|---|---|
| **Presentation** | Blade Views, Tailwind CSS, Vite | Render HTML, handle CSS/JS assets |
| **Routing** | routes/web.php | Map HTTP verbs + URLs to controllers |
| **Middleware** | IsAdmin, CheckMaintenance, Auth | Guard routes, check roles |
| **Controller** | 20+ controllers | Handle HTTP logic, orchestrate services |
| **Service** | AIService, EventTracker | HTTP clients to microservices |
| **Model** | 12 Eloquent models | ORM, relationships, business rules |
| **Database** | MySQL, Redis | Persistent storage, caching |
| **AI Microservice** | FastAPI + scikit-learn | ML models, recommendation algorithms |
| **Event Microservice** | FastAPI + ClickHouse | Behavior event ingestion & analytics |

---

## Communication Patterns

```mermaid
sequenceDiagram
    participant B as Browser
    participant L as Laravel (9090)
    participant A as AI Service (8001)
    participant E as Event Service (8000)
    participant M as MySQL
    participant R as Redis
    participant C as ClickHouse

    B->>L: GET /products/{slug}
    L->>M: SELECT product + reviews
    L->>E: POST /collect {event_type: product_view}
    E->>C: INSERT event (async)
    L->>R: Check recommendation cache
    alt Cache miss
        L->>A: POST /recommendations/similar
        A->>C: Query co-view events
        A->>R: Cache result (30 min)
    end
    L-->>B: Render product page + recommendations
```

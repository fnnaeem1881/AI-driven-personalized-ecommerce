# TechNova Store — Full Project Documentation

**AI-Driven Personalized E-Commerce Platform**

> All diagrams use [Mermaid](https://mermaid.js.org) syntax.
> Render on: [mermaid.live](https://mermaid.live), GitHub (native), VS Code (`Markdown Preview Mermaid Support` extension), or Notion.

---

## Documentation Index

| # | Document | Contents |
|---|---|---|
| 01 | [Project Overview](01_PROJECT_OVERVIEW.md) | Tech stack, ports, features summary |
| 02 | [High-Level Architecture](02_HIGH_LEVEL_ARCHITECTURE.md) | System architecture diagram, layered description, communication patterns |
| 03 | [Low-Level Architecture](03_LOW_LEVEL_ARCHITECTURE.md) | Laravel internal components, AI/Event service internals, request lifecycle |
| 04 | [Database ER Diagram](04_DATABASE_ER_DIAGRAM.md) | Full ER diagram (all 15+ tables), table descriptions, order state machine |
| 05 | [Sequence Diagrams](05_SEQUENCE_DIAGRAMS.md) | 7 sequence diagrams: login, browse, cart, checkout, homepage, admin analytics, retrain |
| 06 | [Class Diagram](06_CLASS_DIAGRAM.md) | All Eloquent models, service classes, traits |
| 07 | [Data Flow Diagram](07_DATA_FLOW_DIAGRAM.md) | L0 context, L1 decomposition, L2: recommendations, events, orders |
| 08 | [Deployment Diagram](08_DEPLOYMENT_DIAGRAM.md) | Infrastructure, network map, version matrix, startup sequence |
| 09 | [Use Case Diagram](09_USE_CASE_DIAGRAM.md) | Guest, Customer, Admin, AI/Event service use cases |
| 10 | [ML/AI Pipeline](10_ML_AI_PIPELINE.md) | Recommendation pipeline, cart abandonment, user segmentation, algorithm decision |
| 11 | [API Reference](11_API_REFERENCE.md) | All routes (Laravel + AI Service + Event Service) |
| 12 | [RBAC Diagram](12_RBAC_DIAGRAM.md) | Roles, permissions, groups, authorization flow |
| 13 | [Component Interaction](13_COMPONENT_INTERACTION_DIAGRAM.md) | Blade view map, controller-model interactions, cart & event state machines |

---

## Quick Architecture Summary

```
Browser
  │
  ▼
Laravel (PHP / Blade / Tailwind)  ← Port 9090
  │           │
  │           └─── MySQL (technova_store)
  │           └─── Redis (sessions, cache)
  │
  ├──► AI Service (Python FastAPI)  ← Port 8001
  │       ├── Collaborative Filtering
  │       ├── Content-Based Filtering
  │       ├── Cart Abandonment (RandomForest)
  │       ├── User Segmentation (KMeans)
  │       └── Redis + ClickHouse Cloud
  │
  └──► Event Service (Python FastAPI)  ← Port 8000
          ├── Behavior Event Ingestion
          ├── Analytics Aggregation
          └── Redis + ClickHouse Cloud
```

---

## Diagrams Included

| Diagram Type | Location |
|---|---|
| High-Level Architecture | [02](02_HIGH_LEVEL_ARCHITECTURE.md) |
| Low-Level Architecture | [03](03_LOW_LEVEL_ARCHITECTURE.md) |
| Database ER Diagram | [04](04_DATABASE_ER_DIAGRAM.md) |
| Order Status State Machine | [04](04_DATABASE_ER_DIAGRAM.md#order-status-state-machine) |
| Sequence: User Registration | [05](05_SEQUENCE_DIAGRAMS.md) |
| Sequence: Product Browse + Recs | [05](05_SEQUENCE_DIAGRAMS.md) |
| Sequence: Add to Cart + AI Predict | [05](05_SEQUENCE_DIAGRAMS.md) |
| Sequence: Checkout & Order | [05](05_SEQUENCE_DIAGRAMS.md) |
| Sequence: Homepage Personalization | [05](05_SEQUENCE_DIAGRAMS.md) |
| Sequence: Admin Analytics | [05](05_SEQUENCE_DIAGRAMS.md) |
| Sequence: AI Retraining | [05](05_SEQUENCE_DIAGRAMS.md) |
| Class Diagram (Models) | [06](06_CLASS_DIAGRAM.md) |
| Class Diagram (Services) | [06](06_CLASS_DIAGRAM.md) |
| Data Flow (L0 Context) | [07](07_DATA_FLOW_DIAGRAM.md) |
| Data Flow (L1 Decomposition) | [07](07_DATA_FLOW_DIAGRAM.md) |
| Data Flow (L2 AI Recs) | [07](07_DATA_FLOW_DIAGRAM.md) |
| Data Flow (L2 Events/Analytics) | [07](07_DATA_FLOW_DIAGRAM.md) |
| Data Flow (L2 Orders) | [07](07_DATA_FLOW_DIAGRAM.md) |
| Deployment Diagram | [08](08_DEPLOYMENT_DIAGRAM.md) |
| Network Communication Map | [08](08_DEPLOYMENT_DIAGRAM.md) |
| Startup Sequence Diagram | [08](08_DEPLOYMENT_DIAGRAM.md) |
| Use Case: Customer | [09](09_USE_CASE_DIAGRAM.md) |
| Use Case: Admin | [09](09_USE_CASE_DIAGRAM.md) |
| Use Case: AI/Event Services | [09](09_USE_CASE_DIAGRAM.md) |
| ML Recommendation Pipeline | [10](10_ML_AI_PIPELINE.md) |
| ML Cart Abandonment Pipeline | [10](10_ML_AI_PIPELINE.md) |
| ML User Segmentation Pipeline | [10](10_ML_AI_PIPELINE.md) |
| Recommendation Decision Logic | [10](10_ML_AI_PIPELINE.md) |
| RBAC Architecture | [12](12_RBAC_DIAGRAM.md) |
| Authorization Flow | [12](12_RBAC_DIAGRAM.md) |
| Role Hierarchy | [12](12_RBAC_DIAGRAM.md) |
| Frontend Component Map | [13](13_COMPONENT_INTERACTION_DIAGRAM.md) |
| Controller-Model-Service Map | [13](13_COMPONENT_INTERACTION_DIAGRAM.md) |
| Cart Lifecycle State Machine | [13](13_COMPONENT_INTERACTION_DIAGRAM.md) |
| Event Flow State Diagram | [13](13_COMPONENT_INTERACTION_DIAGRAM.md) |

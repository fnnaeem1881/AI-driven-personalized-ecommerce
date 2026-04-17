# Deployment Diagram

## Infrastructure Deployment Overview

```mermaid
graph TB
    subgraph LOCAL["💻 Local Development Machine (Windows 11)"]
        subgraph LARAVEL_NODE["Laravel Application Server"]
            PHP["PHP 8.1+ Runtime"]
            ARTISAN["php artisan serve\n:9090"]
            BLADE_VIEWS["Blade Templates\n+ Compiled Blade Cache"]
            VITE_BUILD["Vite Build Output\n(CSS/JS assets in public/)"]
        end

        subgraph AINODE["AI Service (Python)"]
            UVICORN1["Uvicorn ASGI Server\n:8001"]
            FASTAPI1["FastAPI Application\nmain.py"]
            PKL_FILES["Serialized Models\n*.pkl files"]
            CSV_DATA["Training Data\n*.csv files"]
        end

        subgraph EVNODE["Event Service (Python)"]
            UVICORN2["Uvicorn ASGI Server\n:8000"]
            FASTAPI2["FastAPI Application\nmain.py"]
        end

        subgraph DBNODE["Local Databases"]
            MYSQL_LOCAL[("MySQL 8.0\n:3306\ntechnova_store")]
            REDIS_LOCAL[("Redis 7\n:6379\nSessions + Cache")]
        end

        STARTUP["start-all.bat / start-all.ps1\n(Orchestration Script)"]
    end

    subgraph CLOUD["☁️ Cloud Services"]
        subgraph AZURE["Microsoft Azure"]
            CH_CLOUD[("ClickHouse Cloud\ngermanywestcentral\n.azure.clickhouse.cloud\n:8443 (HTTPS TLS)")]
        end
    end

    subgraph BROWSER["🌐 Client"]
        USER_BROWSER["Web Browser\nChrome / Firefox / Safari"]
    end

    STARTUP --> ARTISAN & UVICORN1 & UVICORN2

    USER_BROWSER -- "HTTP :9090" --> ARTISAN
    ARTISAN --> PHP --> BLADE_VIEWS
    PHP --> MYSQL_LOCAL
    PHP --> REDIS_LOCAL
    PHP -- "HTTP :8001" --> UVICORN1
    PHP -- "HTTP :8000" --> UVICORN2

    UVICORN1 --> FASTAPI1
    FASTAPI1 --> PKL_FILES & CSV_DATA
    FASTAPI1 --> REDIS_LOCAL
    FASTAPI1 -- "HTTPS :8443" --> CH_CLOUD

    UVICORN2 --> FASTAPI2
    FASTAPI2 --> REDIS_LOCAL
    FASTAPI2 -- "HTTPS :8443" --> CH_CLOUD
```

---

## Network Communication Map

```mermaid
flowchart LR
    subgraph EXTERNAL["External Traffic"]
        BROWSER["Browser\n(any IP)"]
    end

    subgraph APP_LAYER["Application Layer (localhost)"]
        L["Laravel\n0.0.0.0:9090"]
        AI["AI Service\n0.0.0.0:8001"]
        EV["Event Service\n0.0.0.0:8000"]
    end

    subgraph DATA_LAYER["Data Layer (localhost)"]
        MYSQL["MySQL\n127.0.0.1:3306"]
        REDIS["Redis\n127.0.0.1:6379"]
    end

    subgraph CLOUD_LAYER["Cloud (Azure)"]
        CH["ClickHouse Cloud\nPort 8443 TLS"]
    end

    BROWSER -- "HTTP GET/POST" --> L
    L -- "SQL queries" --> MYSQL
    L -- "GET/SET session\nModel cache" --> REDIS
    L -- "HTTP POST (sync)" --> AI
    L -- "HTTP POST (sync)" --> EV
    AI -- "GET/SET rec cache" --> REDIS
    AI -- "SQL (ClickHouse)" --> CH
    EV -- "GET/SET event queue" --> REDIS
    EV -- "INSERT events\nSELECT analytics" --> CH
```

---

## Component Version Matrix

| Component | Version | Runtime |
|---|---|---|
| PHP | 8.1+ | php-cgi / cli |
| Laravel | 11.x | PHP framework |
| MySQL | 8.0 | mysqld |
| Redis | 7.x | redis-server |
| Python | 3.11 | CPython |
| FastAPI | 0.115+ | uvicorn |
| scikit-learn | 1.3+ | pip |
| pandas | 2.x | pip |
| ClickHouse Driver | clickhouse-connect | pip |
| Node.js | 18+ | npm (build only) |
| Vite | 6.0 | npm dev/build |
| Tailwind CSS | 3.4 | npm |

---

## Startup Sequence

```mermaid
sequenceDiagram
    participant OS as Windows OS
    participant SCRIPT as start-all.bat
    participant PHP as PHP (Laravel)
    participant PY1 as Python (AI)
    participant PY2 as Python (Event)
    participant MYSQL as MySQL
    participant REDIS as Redis
    participant CH as ClickHouse

    Note over MYSQL,REDIS: Pre-running (system services)
    Note over CH: Pre-running (cloud)

    OS->>SCRIPT: Execute start-all.bat

    SCRIPT->>PHP: cd backend && php artisan serve --port=9090
    PHP->>MYSQL: Connect (3306)
    PHP->>REDIS: Connect (6379)
    PHP-->>SCRIPT: Listening on :9090

    SCRIPT->>PY1: cd services/ai-service && python main.py
    PY1->>REDIS: Connect (6379)
    PY1->>CH: Test connection
    PY1->>PY1: Load .pkl models
    PY1-->>SCRIPT: Listening on :8001

    SCRIPT->>PY2: cd services/event-service && python main.py
    PY2->>REDIS: Connect (6379)
    PY2->>CH: Test connection
    PY2-->>SCRIPT: Listening on :8000

    Note over PHP,PY2: All services ready
```

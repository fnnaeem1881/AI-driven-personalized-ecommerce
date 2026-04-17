# Role-Based Access Control (RBAC) Diagram

## RBAC Architecture

```mermaid
graph TD
    subgraph USERS["Users"]
        U1["User A\n(role: user)"]
        U2["User B\n(role: admin)"]
        U3["User C\n(role: admin)"]
    end

    subgraph ROLES["Spatie Roles"]
        R1["Role: admin"]
        R2["Role: user"]
        R3["Role: manager\n(custom)"]
    end

    subgraph PERM_GROUPS["Permission Groups"]
        subgraph PG1["Group: products"]
            P1["products.index"]
            P2["products.create"]
            P3["products.edit"]
            P4["products.delete"]
        end
        subgraph PG2["Group: orders"]
            P5["orders.index"]
            P6["orders.update-status"]
            P7["orders.update-payment"]
        end
        subgraph PG3["Group: users"]
            P8["users.index"]
            P9["users.create"]
            P10["users.update-role"]
        end
        subgraph PG4["Group: settings"]
            P11["settings.index"]
            P12["settings.update"]
        end
        subgraph PG5["Group: analytics"]
            P13["analytics.view"]
            P14["analytics.ai-health"]
        end
    end

    subgraph GUARDS["Route Guards"]
        M1["auth middleware\n(must be logged in)"]
        M2["admin middleware\n(role == admin)"]
        M3["can: permission\n(Spatie check)"]
    end

    U1 --> R2
    U2 --> R1
    U3 --> R1

    R1 --> P1 & P2 & P3 & P4
    R1 --> P5 & P6 & P7
    R1 --> P8 & P9 & P10
    R1 --> P11 & P12
    R1 --> P13 & P14

    R3 --> P1 & P2 & P3
    R3 --> P5 & P6

    M2 --> R1
    M3 --> PERM_GROUPS
```

---

## Authorization Flow

```mermaid
flowchart TD
    REQ["HTTP Request to /admin/*"]

    A1{"auth middleware\nIs user logged in?"}
    A2{"admin middleware\nuser->isAdmin()\nor hasRole('admin')"}
    A3{"Optional: can(permission)\nSpatie permission check"}

    DENY1["Redirect to /login"]
    DENY2["403 Forbidden\nAccess Denied"]
    ALLOW["Controller Method Executes"]

    REQ --> A1
    A1 -- "Not logged in" --> DENY1
    A1 -- "Logged in" --> A2
    A2 -- "Not admin" --> DENY2
    A2 -- "Is admin" --> A3
    A3 -- "Has permission" --> ALLOW
    A3 -- "Missing permission" --> DENY2
```

---

## User Role Hierarchy

```mermaid
graph TD
    subgraph ROLE_HIER["Role Hierarchy (Laravel + Spatie)"]
        SUPERADMIN["Super Admin\n(all permissions)"]
        ADMIN["Admin\n(standard admin rights)"]
        MANAGER["Manager\n(limited admin rights)"]
        USER["User\n(customer only)"]
        GUEST["Guest\n(unauthenticated)"]
    end

    GUEST -- "can register/login" --> USER
    USER -- "role upgrade" --> MANAGER
    MANAGER -- "role upgrade" --> ADMIN
    ADMIN -- "role upgrade" --> SUPERADMIN

    subgraph ACCESS["Access Levels"]
        L1["Public routes: ALL"]
        L2["Protected routes: USER+"]
        L3["Admin panel: ADMIN+"]
        L4["Permission CRUD: SUPERADMIN"]
    end

    GUEST --> L1
    USER --> L1 & L2
    ADMIN --> L1 & L2 & L3
    SUPERADMIN --> L1 & L2 & L3 & L4
```

---

## Permission Group Mapping

| Group | Permissions | Assigned to Role |
|---|---|---|
| `products` | index, create, edit, delete | admin, manager |
| `orders` | index, update-status, update-payment | admin, manager |
| `users` | index, create, update-role | admin |
| `settings` | index, update | admin |
| `analytics` | view, ai-health | admin |
| `flash-deals` | index, create, edit, delete | admin, manager |
| `slides` | index, create, edit, delete | admin |
| `roles` | index, create, edit, delete | admin |
| `permissions` | index, create, destroy, assign | admin (super) |

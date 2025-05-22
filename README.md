### ER Diagram
```mermaid
erDiagram
    SERVICE ||--o{ CONSTRAINTS : ""
    SERVICE ||--o{ TICKET : ""
    USER ||--o{ TRANSACTION : ""
    USER |o--o| COUNTER : ""
    COUNTER ||--o{ TRANSACTION : ""
    TRANSACTION ||--o{ LOG : ""
    TICKET ||--|| TRANSACTION : ""

SERVICE {
    ulid id PK
    string name "unique"
    text description "nullable"
    string prefix "nullable"
    boolean active
    json requirements "nullable"
    timestamp deleted_at "nullable"
    timestamp created_at "nullable"
    timestamp updated_at "nullable"
}

TICKET {
    ulid id PK
    string number "index"
    boolean priority
    ulid service_id FK "cascade"
    timestamp created_at "nullable"
    timestamp updated_at "nullable"
}

COUNTER {
    ulid id PK
    string name "unique"
    text description "nullable"
    boolean active
    ulid user_id FK "nullable"
    timestamp deleted_at "nullable"
    timestamp created_at "nullable"
    timestamp updated_at "nullable"
}

USER {
    ulid id PK
    string name
    string email "unique"
    string password
    string role
    timestamp email_verified_at "nullable"
    timestamp created_at "nullable"
    timestamp updated_at "nullable"
}

TRANSACTION {
    ulid id PK
    string remarks "nullable"
    ulid ticket_id FK "cascade"
    ulid counter_id FK "nullable"
    ulid user_id FK "nullable"
    timestamp created_at "nullable"
    timestamp updated_at "nullable"
}

LOG {
    ulid id PK
    string status
    ulid transaction_id FK "cascade"
    timestamp created_at "nullable"
    timestamp updated_at "nullable"
}

CONSTRAINTS {
    ulid id PK
    string name "unique"
    string description "nullable"
    json value
    boolean active
    ulid service_id FK "cascade"
    timestamp created_at "nullable"
    timestamp updated_at "nullable"
}

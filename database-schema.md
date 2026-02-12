# Database Schema Diagram - Nexora Service Suite Plugin

## Main Tables Structure

```mermaid
erDiagram
    wp_users {
        bigint ID PK
        varchar user_login
        varchar user_email
        varchar user_pass
        varchar user_nicename
        datetime user_registered
        varchar user_activation_key
        int user_status
        varchar display_name
    }

    wp_usermeta {
        bigint umeta_id PK
        bigint user_id FK
        varchar meta_key
        longtext meta_value
    }

    nexora_service_status {
        int id PK
        varchar title
        varchar color
        tinyint is_default
        datetime created_at
        datetime updated_at
    }

    nexora_services {
        int id PK
        varchar title
        text description
        decimal cost
        enum status
        bigint user_id FK
        datetime created_at
        datetime updated_at
    }

    nexora_service_requests {
        mediumint id PK
        varchar serial
        varchar model
        text description
        text service_description
        int service_quantity
        bigint user_id FK
        bigint service_id FK
        bigint status_id FK
        bigint order_id
        int brand_level_1_id FK
        int brand_level_2_id FK
        int brand_level_3_id FK
        varchar benefit_type
        decimal commission_percentage
        decimal discount_percentage
        datetime created_at
        datetime updated_at
    }

    nexora_devices {
        int id PK
        varchar name
        varchar slug
        int parent_id FK
        enum type
        datetime created_at
        datetime updated_at
    }

    nexora_service_details {
        bigint id PK
        bigint request_id FK
        bigint service_id FK
        varchar service_title
        int quantity
        text note
        datetime created_at
        datetime updated_at
    }

    nexora_customer_info {
        mediumint id PK
        bigint user_id FK
        enum customer_type
        varchar customer_number
        varchar company_name
        varchar company_name_2
        varchar street
        varchar address_addition
        varchar postal_code
        varchar city
        varchar country
        varchar industry
        varchar vat_id
        enum salutation
        varchar phone
        tinyint newsletter
        datetime created_at
        datetime updated_at
    }

    nexora_admin_notifications {
        bigint id PK
        varchar type
        text message
        bigint related_id
        bigint user_id FK
        enum status
        datetime created_at
    }

    nexora_activity_logs {
        bigint id PK
        bigint request_id FK
        bigint user_id FK
        varchar action_type
        text action_description
        text old_value
        text new_value
        varchar ip_address
        text user_agent
        datetime created_at
    }

    nexora_request_comments {
        bigint id PK
        bigint request_id FK
        bigint user_id FK
        text comment_text
        datetime created_at
    }

    nexora_logs {
        mediumint id PK
        varchar salt
        text description
        bigint creator_user_id FK
        datetime created_at
    }

    nexora_viewed_logs {
        mediumint id PK
        bigint creator_user_id FK
        bigint creator_log_id FK
        datetime created_at
    }

    nexora_invoices {
        bigint id PK
        varchar invoice_number
        enum invoice_type
        bigint related_id
        bigint user_id FK
        decimal total_amount
        enum status
        datetime invoice_date
        datetime due_date
        datetime created_at
        datetime updated_at
    }

    nexora_invoice_items {
        bigint id PK
        bigint invoice_id FK
        varchar item_name
        text description
        decimal quantity
        decimal unit_price
        decimal total_price
        datetime created_at
    }

    nexora_file_attachments {
        bigint id PK
        varchar file_name
        varchar original_name
        varchar file_path
        bigint file_size
        varchar file_type
        bigint uploaded_by FK
        datetime uploaded_at
    }

    nexora_request_attachments {
        bigint id PK
        bigint request_id FK
        varchar file_name
        varchar file_path
        bigint file_size
        varchar file_type
        datetime uploaded_at
    }

    nexora_request_invoices {
        bigint id PK
        bigint request_id FK
        varchar file_name
        varchar file_path
        bigint file_size
        varchar file_type
        datetime uploaded_at
    }

    %% Relationships
    wp_users ||--o{ wp_usermeta : "has"
    wp_users ||--o{ nexora_services : "creates"
    wp_users ||--o{ nexora_service_requests : "creates"
    wp_users ||--o{ nexora_customer_info : "has"
    wp_users ||--o{ nexora_admin_notifications : "receives"
    wp_users ||--o{ nexora_activity_logs : "performs"
    wp_users ||--o{ nexora_request_comments : "writes"
    wp_users ||--o{ nexora_logs : "creates"
    wp_users ||--o{ nexora_viewed_logs : "views"
    wp_users ||--o{ nexora_invoices : "receives"
    wp_users ||--o{ nexora_file_attachments : "uploads"

    nexora_service_status ||--o{ nexora_service_requests : "has"
    nexora_services ||--o{ nexora_service_requests : "used_in"
    nexora_services ||--o{ nexora_service_details : "detailed_in"
    nexora_devices ||--o{ nexora_devices : "parent_of"
    nexora_devices ||--o{ nexora_service_requests : "brand_level_1"
    nexora_devices ||--o{ nexora_service_requests : "brand_level_2"
    nexora_devices ||--o{ nexora_service_requests : "brand_level_3"

    nexora_service_requests ||--o{ nexora_service_details : "has"
    nexora_service_requests ||--o{ nexora_activity_logs : "tracked_in"
    nexora_service_requests ||--o{ nexora_request_comments : "has"
    nexora_service_requests ||--o{ nexora_request_attachments : "has"
    nexora_service_requests ||--o{ nexora_request_invoices : "has"

    nexora_invoices ||--o{ nexora_invoice_items : "contains"
    nexora_logs ||--o{ nexora_viewed_logs : "viewed_in"
```

## Key Relationships

### User Management
- `wp_users` - WordPress core users table
- `wp_usermeta` - User metadata (benefit_type, payment_status, etc.)
- `nexora_customer_info` - Extended customer information

### Service Management
- `nexora_services` - Available services
- `nexora_service_status` - Service statuses (Neu, In Bearbeitung, etc.)
- `nexora_service_requests` - Main service requests table
- `nexora_service_details` - Multiple services per request

### Device Management
- `nexora_devices` - Hierarchical device structure (type > brand > series > model)
- Connected to service requests via brand_level_1_id, brand_level_2_id, brand_level_3_id

### Financial Management
- `benefit_type` in usermeta: 'commission' or 'discount'
- `commission_percentage` and `discount_percentage` in service_requests
- `payment_status` in usermeta: 'paid' or 'unpaid'
- `nexora_invoices` - Invoice management

### Activity Tracking
- `nexora_activity_logs` - All user actions
- `nexora_admin_notifications` - Admin notifications
- `nexora_logs` - System logs
- `nexora_viewed_logs` - Log viewing tracking

### File Management
- `nexora_file_attachments` - General file attachments
- `nexora_request_attachments` - Request-specific attachments
- `nexora_request_invoices` - Invoice file attachments

## Important Notes

1. **Benefit System**: Users with `benefit_type = 'commission'` can see financial accounts
2. **Payment Tracking**: Payment status is stored in usermeta, not per request
3. **Device Hierarchy**: Devices are organized in a tree structure
4. **Multiple Services**: One request can have multiple services via service_details table
5. **Activity Logging**: All actions are logged for audit purposes

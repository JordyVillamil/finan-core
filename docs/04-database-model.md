# Modelo de Base de Datos

## 🗄️ Database Design Document

**Proyecto:** Sistema Contable y Administrativo  
**DBMS:** PostgreSQL 15+  
**Versión:** 1.0  
**Fecha:** Noviembre 2025  

---

## 1. VISIÓN GENERAL

### 1.1 Propósito
Este documento detalla el diseño completo de la base de datos del sistema, incluyendo tablas, relaciones, índices, restricciones y consideraciones de performance.

### 1.2 Principios de Diseño

1. **Normalización**: Base de datos normalizada hasta 3FN
2. **Integridad Referencial**: FK con cascadas apropiadas
3. **Auditoría**: Timestamps en todas las tablas
4. **Soft Deletes**: Borrado lógico en tablas críticas
5. **Indexación**: Índices en columnas de búsqueda frecuente
6. **Performance**: Desnormalización estratégica donde necesario

---

## 2. DIAGRAMA ENTIDAD-RELACIÓN

```
┌─────────────────────────────────────────────────────────────┐
│                    DOMINIO: SHARED                           │
└─────────────────────────────────────────────────────────────┘

    users                     roles                companies
┌──────────┐            ┌──────────┐          ┌──────────────┐
│ id       │◄───┐       │ id       │          │ id           │
│ name     │    │       │ name     │          │ name         │
│ email    │    │       │ guard    │          │ tax_id       │
│ password │    │       │ created  │          │ address      │
│ active   │    │       └──────────┘          │ city         │
│ created  │    │              ▲              │ country      │
│ updated  │    │              │              │ phone        │
└──────────┘    │              │              │ email        │
                │       ┌──────────────┐      │ logo_url     │
                │       │role_user     │      │ currency     │
                └───────│(pivot)       │      │ settings     │
                        │user_id    FK │      │ created      │
                        │role_id    FK │      │ updated      │
                        └──────────────┘      └──────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   DOMINIO: BILLING                           │
└─────────────────────────────────────────────────────────────┘

      clients                 invoices              invoice_items
┌──────────────┐        ┌──────────────┐        ┌──────────────┐
│ id           │◄───┐   │ id           │◄───┐   │ id           │
│ company_id FK│    └───│ client_id FK │    └───│ invoice_id FK│
│ name         │        │ company_id FK│        │ product_id FK│
│ tax_id       │        │ inv_number   │        │ description  │
│ email        │        │ date         │        │ quantity     │
│ phone        │        │ due_date     │        │ unit_price   │
│ address      │        │ subtotal     │        │ tax_rate     │
│ city         │        │ tax_amount   │        │ discount     │
│ type         │        │ discount_amt │        │ line_total   │
│ active       │        │ total        │        │ created      │
│ created      │        │ status       │        │ updated      │
│ updated      │        │ notes        │        └──────────────┘
│ deleted      │        │ paid_at      │
└──────────────┘        │ cancelled_at │              payments
                        │ dian_cufe    │        ┌──────────────┐
                        │ dian_qr      │    ┌───│ id           │
                        │ created      │    │   │ invoice_id FK│
                        │ updated      │    │   │ amount       │
                        │ deleted      │────┘   │ paid_date    │
                        └──────────────┘        │ method       │
                                                 │ reference    │
    quotations          Similar structure       │ created      │
┌──────────────┐        to invoices but:        │ updated      │
│ id           │        - no stock impact        └──────────────┘
│ client_id FK │        - has expiry_date
│ company_id FK│        - convertible to invoice
│ ...          │        - status: draft/sent/approved/rejected
└──────────────┘

┌─────────────────────────────────────────────────────────────┐
│                  DOMINIO: INVENTORY                          │
└─────────────────────────────────────────────────────────────┘

    categories             products              warehouses
┌──────────────┐    ┌──────────────┐        ┌──────────────┐
│ id           │◄───│ id           │        │ id           │
│ company_id FK│    │ company_id FK│        │ company_id FK│
│ name         │    │ category_id  │        │ name         │
│ parent_id    │    │ sku          │        │ address      │
│ active       │    │ name         │        │ active       │
│ created      │    │ description  │        │ created      │
│ updated      │    │ unit_price   │        │ updated      │
└──────────────┘    │ cost         │        └──────────────┘
                    │ unit_measure │                ▲
                    │ barcode      │                │
                    │ tax_rate     │        ┌───────┴──────┐
                    │ type         │        │              │
                    │ active       │   stock           stock_movements
                    │ image_url    │┌──────────────┐ ┌──────────────┐
                    │ created      ││ id           │ │ id           │
                    │ updated      ││ product_id FK│ │ product_id FK│
                    │ deleted      ││ warehouse_IDFK│warehouse_id FK│
                    └──────────────┘│ quantity     │ │ type         │
                          ▲         │ min_quantity │ │ quantity     │
                          │         │ created      │ │ reference_type│
                          │         │ updated      │ │ reference_id │
                          └─────────└──────────────┘ │ user_id FK   │
                                                      │ notes        │
                                                      │ created      │
                                                      └──────────────┘

┌─────────────────────────────────────────────────────────────┐
│                  DOMINIO: PURCHASING                         │
└─────────────────────────────────────────────────────────────┘

     suppliers            purchases            purchase_items
┌──────────────┐    ┌──────────────┐      ┌──────────────┐
│ id           │◄───│ id           │◄─────│ id           │
│ company_id FK│    │ company_id FK│      │ purchase_id  │
│ name         │    │ supplier_id  │      │ product_id FK│
│ tax_id       │    │ purch_number │      │ quantity     │
│ email        │    │ date         │      │ unit_price   │
│ phone        │    │ due_date     │      │ tax_amount   │
│ address      │    │ subtotal     │      │ line_total   │
│ city         │    │ tax_amount   │      │ created      │
│ payment_terms│    │ total        │      │ updated      │
│ active       │    │ status       │      └──────────────┘
│ created      │    │ notes        │
│ updated      │    │ received_at  │
│ deleted      │    │ created      │
└──────────────┘    │ updated      │
                    │ deleted      │
                    └──────────────┘

    purchase_orders    (Optional - similar to purchases)
┌──────────────┐      but with status: draft/sent/received/cancelled
│ id           │
│ ...          │
└──────────────┘

┌─────────────────────────────────────────────────────────────┐
│                 DOMINIO: ACCOUNTING                          │
└─────────────────────────────────────────────────────────────┘

      accounts           journal_entries    journal_entry_lines
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ id           │     │ id           │◄────│ id           │
│ company_id FK│     │ company_id FK│     │ entry_id FK  │
│ code         │◄─┐  │ entry_number │     │ account_id FK│
│ name         │  │  │ date         │     │ debit        │
│ type         │  │  │ description  │     │ credit       │
│ parent_id FK ├──┘  │ ref_type     │     │ description  │
│ level        │     │ ref_id       │     │ created      │
│ active       │     │ status       │     │ updated      │
│ created      │     │ posted_at    │     └──────────────┘
│ updated      │     │ created_by FK│
└──────────────┘     │ created      │
                     │ updated      │
     fiscal_periods  │ deleted      │
┌──────────────┐     └──────────────┘
│ id           │
│ company_id FK│         tax_settings
│ year         │     ┌──────────────┐
│ start_date   │     │ id           │
│ end_date     │     │ company_id FK│
│ status       │     │ name         │
│ closed_at    │     │ rate         │
│ created      │     │ type         │
│ updated      │     │ active       │
└──────────────┘     │ created      │
                     │ updated      │
                     └──────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   MÓDULOS AUXILIARES                         │
└─────────────────────────────────────────────────────────────┘

    audit_logs             notifications        settings
┌──────────────┐      ┌──────────────┐    ┌──────────────┐
│ id           │      │ id           │    │ id           │
│ user_id FK   │      │ user_id FK   │    │ company_id FK│
│ auditable_type│     │ type         │    │ key          │
│ auditable_id │      │ title        │    │ value        │
│ event        │      │ message      │    │ created      │
│ old_values   │      │ data         │    │ updated      │
│ new_values   │      │ read_at      │    └──────────────┘
│ ip_address   │      │ created      │
│ user_agent   │      │ updated      │
│ created      │      └──────────────┘
└──────────────┘

    email_logs           file_attachments
┌──────────────┐      ┌──────────────┐
│ id           │      │ id           │
│ to           │      │ attachable_t │
│ from         │      │ attachable_id│
│ subject      │      │ filename     │
│ body         │      │ path         │
│ sent_at      │      │ mime_type    │
│ status       │      │ size         │
│ error        │      │ uploaded_by  │
│ created      │      │ created      │
└──────────────┘      │ updated      │
                      └──────────────┘
```

---

## 3. DEFINICIÓN DE TABLAS

### 3.1 Dominio: Shared

#### Tabla: users
```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP,
    last_login_ip INET,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_active ON users(active);
```

#### Tabla: roles
```sql
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    guard_name VARCHAR(50) NOT NULL DEFAULT 'web',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE role_user (
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);
```

#### Tabla: permissions
```sql
CREATE TABLE permissions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    guard_name VARCHAR(50) NOT NULL DEFAULT 'web',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE permission_role (
    permission_id BIGINT NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, role_id)
);
```

#### Tabla: companies
```sql
CREATE TABLE companies (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255) NOT NULL,
    tax_id VARCHAR(50) UNIQUE NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(2) DEFAULT 'CO',
    postal_code VARCHAR(20),
    phone VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    logo_url VARCHAR(500),
    currency VARCHAR(3) DEFAULT 'COP',
    fiscal_year_start DATE,
    settings JSONB DEFAULT '{}',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_companies_tax_id ON companies(tax_id);
CREATE INDEX idx_companies_active ON companies(active);
```

---

### 3.2 Dominio: Billing

#### Tabla: clients
```sql
CREATE TABLE clients (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255),
    tax_id VARCHAR(50),
    id_type VARCHAR(20), -- NIT, CC, CE, Pasaporte
    email VARCHAR(255),
    phone VARCHAR(50),
    mobile VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(2) DEFAULT 'CO',
    postal_code VARCHAR(20),
    type VARCHAR(20) DEFAULT 'individual', -- individual, business
    payment_terms INTEGER DEFAULT 30, -- días
    credit_limit NUMERIC(15,2) DEFAULT 0,
    notes TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE INDEX idx_clients_company ON clients(company_id);
CREATE INDEX idx_clients_tax_id ON clients(company_id, tax_id);
CREATE INDEX idx_clients_email ON clients(email);
CREATE INDEX idx_clients_active ON clients(active);
```

#### Tabla: invoices
```sql
CREATE TABLE invoices (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    client_id BIGINT NOT NULL REFERENCES clients(id) ON DELETE RESTRICT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    prefix VARCHAR(10),
    sequence_number INTEGER,
    
    -- Fechas
    date DATE NOT NULL,
    due_date DATE NOT NULL,
    
    -- Montos
    subtotal NUMERIC(15,2) NOT NULL DEFAULT 0,
    discount_amount NUMERIC(15,2) DEFAULT 0,
    discount_percentage NUMERIC(5,2) DEFAULT 0,
    tax_amount NUMERIC(15,2) NOT NULL DEFAULT 0,
    total NUMERIC(15,2) NOT NULL DEFAULT 0,
    paid_amount NUMERIC(15,2) DEFAULT 0,
    balance NUMERIC(15,2) NOT NULL DEFAULT 0,
    
    -- Estado
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    -- draft, sent, viewed, partial, paid, overdue, cancelled
    
    -- Información adicional
    notes TEXT,
    terms TEXT,
    footer TEXT,
    internal_notes TEXT,
    
    -- Facturación electrónica DIAN
    dian_cufe VARCHAR(255), -- Código Único de Factura Electrónica
    dian_qr_code TEXT,
    dian_sent_at TIMESTAMP,
    dian_status VARCHAR(50),
    dian_response JSONB,
    
    -- Metadatos
    sent_at TIMESTAMP,
    viewed_at TIMESTAMP,
    paid_at TIMESTAMP,
    cancelled_at TIMESTAMP,
    cancellation_reason TEXT,
    
    -- Auditoría
    created_by BIGINT REFERENCES users(id),
    updated_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    
    CONSTRAINT chk_invoice_status CHECK (status IN 
        ('draft', 'sent', 'viewed', 'partial', 'paid', 'overdue', 'cancelled'))
);

CREATE INDEX idx_invoices_company ON invoices(company_id);
CREATE INDEX idx_invoices_client ON invoices(client_id);
CREATE INDEX idx_invoices_number ON invoices(invoice_number);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_date ON invoices(date);
CREATE INDEX idx_invoices_due_date ON invoices(due_date);
CREATE INDEX idx_invoices_company_date ON invoices(company_id, date);
```

#### Tabla: invoice_items
```sql
CREATE TABLE invoice_items (
    id BIGSERIAL PRIMARY KEY,
    invoice_id BIGINT NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
    product_id BIGINT REFERENCES products(id) ON DELETE RESTRICT,
    
    -- Descripción
    description TEXT NOT NULL,
    
    -- Cantidades y precios
    quantity NUMERIC(15,4) NOT NULL,
    unit_price NUMERIC(15,2) NOT NULL,
    
    -- Descuentos
    discount_amount NUMERIC(15,2) DEFAULT 0,
    discount_percentage NUMERIC(5,2) DEFAULT 0,
    
    -- Impuestos
    tax_rate NUMERIC(5,2) DEFAULT 0, -- 19.00 para IVA del 19%
    tax_amount NUMERIC(15,2) DEFAULT 0,
    
    -- Totales
    line_total NUMERIC(15,2) NOT NULL,
    
    -- Orden
    sort_order INTEGER DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_invoice_items_invoice ON invoice_items(invoice_id);
CREATE INDEX idx_invoice_items_product ON invoice_items(product_id);
```

#### Tabla: payments
```sql
CREATE TABLE payments (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    invoice_id BIGINT NOT NULL REFERENCES invoices(id) ON DELETE RESTRICT,
    
    -- Monto
    amount NUMERIC(15,2) NOT NULL,
    
    -- Fecha y método
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) NOT NULL, -- cash, transfer, card, check, online
    
    -- Referencia
    reference VARCHAR(100),
    transaction_id VARCHAR(255),
    
    -- Banco (si aplica)
    bank_name VARCHAR(100),
    bank_account VARCHAR(50),
    
    -- Notas
    notes TEXT,
    
    -- Gateway de pago
    gateway VARCHAR(50), -- wompi, mercadopago, paypal
    gateway_response JSONB,
    
    -- Auditoría
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_payment_method CHECK (payment_method IN 
        ('cash', 'transfer', 'card', 'check', 'online', 'other'))
);

CREATE INDEX idx_payments_invoice ON payments(invoice_id);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_payments_method ON payments(payment_method);
```

#### Tabla: quotations
```sql
CREATE TABLE quotations (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    client_id BIGINT NOT NULL REFERENCES clients(id) ON DELETE RESTRICT,
    quotation_number VARCHAR(50) UNIQUE NOT NULL,
    
    -- Fechas
    date DATE NOT NULL,
    expiry_date DATE,
    
    -- Montos (similar a invoices)
    subtotal NUMERIC(15,2) NOT NULL DEFAULT 0,
    discount_amount NUMERIC(15,2) DEFAULT 0,
    tax_amount NUMERIC(15,2) NOT NULL DEFAULT 0,
    total NUMERIC(15,2) NOT NULL DEFAULT 0,
    
    -- Estado
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    -- draft, sent, viewed, approved, rejected, expired, converted
    
    -- Información adicional
    notes TEXT,
    terms TEXT,
    internal_notes TEXT,
    
    -- Conversión a factura
    converted_to_invoice_id BIGINT REFERENCES invoices(id),
    converted_at TIMESTAMP,
    
    -- Metadatos
    sent_at TIMESTAMP,
    viewed_at TIMESTAMP,
    
    -- Auditoría
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE INDEX idx_quotations_company ON quotations(company_id);
CREATE INDEX idx_quotations_client ON quotations(client_id);
CREATE INDEX idx_quotations_status ON quotations(status);
CREATE INDEX idx_quotations_expiry ON quotations(expiry_date);
```

#### Tabla: quotation_items
```sql
CREATE TABLE quotation_items (
    id BIGSERIAL PRIMARY KEY,
    quotation_id BIGINT NOT NULL REFERENCES quotations(id) ON DELETE CASCADE,
    product_id BIGINT REFERENCES products(id) ON DELETE RESTRICT,
    description TEXT NOT NULL,
    quantity NUMERIC(15,4) NOT NULL,
    unit_price NUMERIC(15,2) NOT NULL,
    discount_amount NUMERIC(15,2) DEFAULT 0,
    tax_rate NUMERIC(5,2) DEFAULT 0,
    tax_amount NUMERIC(15,2) DEFAULT 0,
    line_total NUMERIC(15,2) NOT NULL,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_quotation_items_quotation ON quotation_items(quotation_id);
```

---

### 3.3 Dominio: Inventory

#### Tabla: categories
```sql
CREATE TABLE categories (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    parent_id BIGINT REFERENCES categories(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    sort_order INTEGER DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categories_company ON categories(company_id);
CREATE INDEX idx_categories_parent ON categories(parent_id);
```

#### Tabla: products
```sql
CREATE TABLE products (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    category_id BIGINT REFERENCES categories(id) ON DELETE SET NULL,
    
    -- Identificación
    sku VARCHAR(100) NOT NULL,
    barcode VARCHAR(100),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    
    -- Tipo
    type VARCHAR(20) NOT NULL DEFAULT 'product', -- product, service, bundle
    
    -- Precios
    unit_price NUMERIC(15,2) NOT NULL,
    cost NUMERIC(15,2) DEFAULT 0,
    
    -- Inventario
    track_inventory BOOLEAN DEFAULT TRUE,
    unit_of_measure VARCHAR(20) DEFAULT 'unit', -- unit, kg, l, m, etc.
    
    -- Impuestos
    taxable BOOLEAN DEFAULT TRUE,
    tax_rate NUMERIC(5,2) DEFAULT 19.00,
    
    -- Información adicional
    brand VARCHAR(100),
    model VARCHAR(100),
    weight NUMERIC(10,4),
    dimensions VARCHAR(100),
    
    -- Media
    image_url VARCHAR(500),
    images JSONB, -- Array de URLs
    
    -- Estado
    active BOOLEAN DEFAULT TRUE,
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    
    CONSTRAINT uq_product_sku UNIQUE (company_id, sku),
    CONSTRAINT chk_product_type CHECK (type IN ('product', 'service', 'bundle'))
);

CREATE INDEX idx_products_company ON products(company_id);
CREATE INDEX idx_products_sku ON products(company_id, sku);
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_active ON products(active);
CREATE INDEX idx_products_name ON products USING gin(to_tsvector('spanish', name));
```

#### Tabla: warehouses
```sql
CREATE TABLE warehouses (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    manager_id BIGINT REFERENCES users(id),
    is_default BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_warehouses_company ON warehouses(company_id);
CREATE INDEX idx_warehouses_default ON warehouses(is_default);
```

#### Tabla: stock
```sql
CREATE TABLE stock (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    warehouse_id BIGINT NOT NULL REFERENCES warehouses(id) ON DELETE CASCADE,
    quantity NUMERIC(15,4) NOT NULL DEFAULT 0,
    min_quantity NUMERIC(15,4) DEFAULT 0,
    max_quantity NUMERIC(15,4),
    reorder_point NUMERIC(15,4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT uq_product_warehouse UNIQUE (product_id, warehouse_id),
    CONSTRAINT chk_quantity_positive CHECK (quantity >= 0)
);

CREATE INDEX idx_stock_product ON stock(product_id);
CREATE INDEX idx_stock_warehouse ON stock(warehouse_id);
CREATE INDEX idx_stock_low_quantity ON stock(product_id) 
    WHERE quantity <= min_quantity;
```

#### Tabla: stock_movements
```sql
CREATE TABLE stock_movements (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    warehouse_id BIGINT NOT NULL REFERENCES warehouses(id) ON DELETE RESTRICT,
    
    -- Tipo de movimiento
    type VARCHAR(20) NOT NULL, -- in, out, adjustment, transfer
    
    -- Cantidad
    quantity NUMERIC(15,4) NOT NULL,
    
    -- Stock antes y después
    stock_before NUMERIC(15,4),
    stock_after NUMERIC(15,4),
    
    -- Referencia al documento origen
    reference_type VARCHAR(100), -- App\Models\Invoice, App\Models\Purchase
    reference_id BIGINT,
    
    -- Para transferencias
    from_warehouse_id BIGINT REFERENCES warehouses(id),
    to_warehouse_id BIGINT REFERENCES warehouses(id),
    
    -- Información adicional
    notes TEXT,
    cost_per_unit NUMERIC(15,2),
    
    -- Auditoría
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_movement_type CHECK (type IN 
        ('in', 'out', 'adjustment', 'transfer'))
);

CREATE INDEX idx_stock_movements_product ON stock_movements(product_id);
CREATE INDEX idx_stock_movements_warehouse ON stock_movements(warehouse_id);
CREATE INDEX idx_stock_movements_type ON stock_movements(type);
CREATE INDEX idx_stock_movements_date ON stock_movements(created_at);
CREATE INDEX idx_stock_movements_reference ON stock_movements(reference_type, reference_id);
```

---

### 3.4 Dominio: Purchasing

#### Tabla: suppliers
```sql
CREATE TABLE suppliers (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255),
    tax_id VARCHAR(50),
    email VARCHAR(255),
    phone VARCHAR(50),
    mobile VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(2) DEFAULT 'CO',
    website VARCHAR(255),
    contact_person VARCHAR(255),
    payment_terms INTEGER DEFAULT 30,
    notes TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE INDEX idx_suppliers_company ON suppliers(company_id);
CREATE INDEX idx_suppliers_tax_id ON suppliers(company_id, tax_id);
CREATE INDEX idx_suppliers_active ON suppliers(active);
```

#### Tabla: purchases
```sql
CREATE TABLE purchases (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    supplier_id BIGINT NOT NULL REFERENCES suppliers(id) ON DELETE RESTRICT,
    warehouse_id BIGINT REFERENCES warehouses(id) ON DELETE RESTRICT,
    
    purchase_number VARCHAR(50) UNIQUE NOT NULL,
    supplier_invoice_number VARCHAR(100),
    
    -- Fechas
    date DATE NOT NULL,
    due_date DATE,
    received_date DATE,
    
    -- Montos
    subtotal NUMERIC(15,2) NOT NULL DEFAULT 0,
    discount_amount NUMERIC(15,2) DEFAULT 0,
    tax_amount NUMERIC(15,2) NOT NULL DEFAULT 0,
    total NUMERIC(15,2) NOT NULL DEFAULT 0,
    paid_amount NUMERIC(15,2) DEFAULT 0,
    balance NUMERIC(15,2) NOT NULL DEFAULT 0,
    
    -- Estado
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    -- draft, ordered, partial, received, cancelled
    
    -- Información adicional
    notes TEXT,
    internal_notes TEXT,
    
    -- Auditoría
    created_by BIGINT REFERENCES users(id),
    received_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    
    CONSTRAINT chk_purchase_status CHECK (status IN 
        ('draft', 'ordered', 'partial', 'received', 'cancelled'))
);

CREATE INDEX idx_purchases_company ON purchases(company_id);
CREATE INDEX idx_purchases_supplier ON purchases(supplier_id);
CREATE INDEX idx_purchases_status ON purchases(status);
CREATE INDEX idx_purchases_date ON purchases(date);
```

#### Tabla: purchase_items
```sql
CREATE TABLE purchase_items (
    id BIGSERIAL PRIMARY KEY,
    purchase_id BIGINT NOT NULL REFERENCES purchases(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    
    description TEXT NOT NULL,
    quantity NUMERIC(15,4) NOT NULL,
    received_quantity NUMERIC(15,4) DEFAULT 0,
    unit_price NUMERIC(15,2) NOT NULL,
    discount_amount NUMERIC(15,2) DEFAULT 0,
    tax_rate NUMERIC(5,2) DEFAULT 0,
    tax_amount NUMERIC(15,2) DEFAULT 0,
    line_total NUMERIC(15,2) NOT NULL,
    
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_purchase_items_purchase ON purchase_items(purchase_id);
CREATE INDEX idx_purchase_items_product ON purchase_items(product_id);
```

#### Tabla: expenses
```sql
CREATE TABLE expenses (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    category_id BIGINT REFERENCES expense_categories(id),
    supplier_id BIGINT REFERENCES suppliers(id),
    
    expense_number VARCHAR(50) UNIQUE NOT NULL,
    date DATE NOT NULL,
    amount NUMERIC(15,2) NOT NULL,
    tax_amount NUMERIC(15,2) DEFAULT 0,
    total NUMERIC(15,2) NOT NULL,
    
    payment_method VARCHAR(50),
    reference VARCHAR(100),
    
    description TEXT NOT NULL,
    notes TEXT,
    
    -- Contabilidad
    account_id BIGINT REFERENCES accounts(id),
    
    -- Auditoría
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE INDEX idx_expenses_company ON expenses(company_id);
CREATE INDEX idx_expenses_category ON expenses(category_id);
CREATE INDEX idx_expenses_date ON expenses(date);
```

#### Tabla: expense_categories
```sql
CREATE TABLE expense_categories (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    account_id BIGINT REFERENCES accounts(id),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 3.5 Dominio: Accounting

#### Tabla: accounts
```sql
CREATE TABLE accounts (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    parent_id BIGINT REFERENCES accounts(id) ON DELETE RESTRICT,
    
    -- Identificación
    code VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    
    -- Tipo
    type VARCHAR(20) NOT NULL,
    -- asset, liability, equity, income, expense, cost
    
    -- Jerarquía
    level INTEGER NOT NULL DEFAULT 1,
    is_header BOOLEAN DEFAULT FALSE, -- Cuenta de agrupación
    
    -- Información adicional
    description TEXT,
    tax_deductible BOOLEAN DEFAULT FALSE,
    
    -- Estado
    active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT uq_account_code UNIQUE (company_id, code),
    CONSTRAINT chk_account_type CHECK (type IN 
        ('asset', 'liability', 'equity', 'income', 'expense', 'cost'))
);

CREATE INDEX idx_accounts_company ON accounts(company_id);
CREATE INDEX idx_accounts_code ON accounts(company_id, code);
CREATE INDEX idx_accounts_type ON accounts(type);
CREATE INDEX idx_accounts_parent ON accounts(parent_id);
```

#### Tabla: journal_entries
```sql
CREATE TABLE journal_entries (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    
    entry_number VARCHAR(50) UNIQUE NOT NULL,
    date DATE NOT NULL,
    
    description TEXT NOT NULL,
    
    -- Referencia al documento origen
    reference_type VARCHAR(100),
    reference_id BIGINT,
    
    -- Estado
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    -- draft, posted, void
    
    -- Totales (debe coincidir debits = credits)
    total_debit NUMERIC(15,2) NOT NULL DEFAULT 0,
    total_credit NUMERIC(15,2) NOT NULL DEFAULT 0,
    
    -- Información adicional
    notes TEXT,
    
    -- Auditoría
    posted_at TIMESTAMP,
    posted_by BIGINT REFERENCES users(id),
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    
    CONSTRAINT chk_entry_status CHECK (status IN ('draft', 'posted', 'void')),
    CONSTRAINT chk_entry_balanced CHECK (total_debit = total_credit)
);

CREATE INDEX idx_journal_entries_company ON journal_entries(company_id);
CREATE INDEX idx_journal_entries_date ON journal_entries(date);
CREATE INDEX idx_journal_entries_status ON journal_entries(status);
CREATE INDEX idx_journal_entries_reference ON journal_entries(reference_type, reference_id);
```

#### Tabla: journal_entry_lines
```sql
CREATE TABLE journal_entry_lines (
    id BIGSERIAL PRIMARY KEY,
    journal_entry_id BIGINT NOT NULL REFERENCES journal_entries(id) ON DELETE CASCADE,
    account_id BIGINT NOT NULL REFERENCES accounts(id) ON DELETE RESTRICT,
    
    description TEXT,
    
    debit NUMERIC(15,2) NOT NULL DEFAULT 0,
    credit NUMERIC(15,2) NOT NULL DEFAULT 0,
    
    sort_order INTEGER DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_line_debit_or_credit CHECK (
        (debit > 0 AND credit = 0) OR (credit > 0 AND debit = 0)
    )
);

CREATE INDEX idx_journal_entry_lines_entry ON journal_entry_lines(journal_entry_id);
CREATE INDEX idx_journal_entry_lines_account ON journal_entry_lines(account_id);
```

#### Tabla: fiscal_periods
```sql
CREATE TABLE fiscal_periods (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    
    name VARCHAR(100) NOT NULL,
    year INTEGER NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    -- open, closed
    
    closed_at TIMESTAMP,
    closed_by BIGINT REFERENCES users(id),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT uq_fiscal_period UNIQUE (company_id, year),
    CONSTRAINT chk_period_status CHECK (status IN ('open', 'closed'))
);

CREATE INDEX idx_fiscal_periods_company ON fiscal_periods(company_id);
CREATE INDEX idx_fiscal_periods_year ON fiscal_periods(year);
```

#### Tabla: tax_settings
```sql
CREATE TABLE tax_settings (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE RESTRICT,
    
    name VARCHAR(100) NOT NULL,
    rate NUMERIC(5,2) NOT NULL,
    type VARCHAR(20) NOT NULL, -- sales, purchase, withholding
    
    -- Cuentas contables asociadas
    collected_account_id BIGINT REFERENCES accounts(id),
    paid_account_id BIGINT REFERENCES accounts(id),
    
    active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_tax_type CHECK (type IN ('sales', 'purchase', 'withholding'))
);

CREATE INDEX idx_tax_settings_company ON tax_settings(company_id);
```

---

### 3.6 Tablas Auxiliares

#### Tabla: audit_logs
```sql
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    
    -- Entidad auditada
    auditable_type VARCHAR(255) NOT NULL,
    auditable_id BIGINT NOT NULL,
    
    -- Acción
    event VARCHAR(50) NOT NULL, -- created, updated, deleted, viewed
    
    -- Valores
    old_values JSONB,
    new_values JSONB,
    
    -- Contexto
    ip_address INET,
    user_agent TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_audit_logs_auditable ON audit_logs(auditable_type, auditable_id);
CREATE INDEX idx_audit_logs_user ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_created ON audit_logs(created_at);
```

#### Tabla: notifications
```sql
CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    
    data JSONB,
    
    read_at TIMESTAMP,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(user_id, read_at);
```

#### Tabla: file_attachments
```sql
CREATE TABLE file_attachments (
    id BIGSERIAL PRIMARY KEY,
    
    attachable_type VARCHAR(255) NOT NULL,
    attachable_id BIGINT NOT NULL,
    
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100),
    size BIGINT, -- bytes
    
    uploaded_by BIGINT REFERENCES users(id),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_file_attachments_attachable ON file_attachments(attachable_type, attachable_id);
```

#### Tabla: settings
```sql
CREATE TABLE settings (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT REFERENCES companies(id) ON DELETE CASCADE,
    
    key VARCHAR(255) NOT NULL,
    value TEXT,
    type VARCHAR(20) DEFAULT 'string', -- string, integer, boolean, json
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT uq_setting_key UNIQUE (company_id, key)
);

CREATE INDEX idx_settings_company ON settings(company_id);
```

#### Tabla: email_logs
```sql
CREATE TABLE email_logs (
    id BIGSERIAL PRIMARY KEY,
    
    to_email VARCHAR(255) NOT NULL,
    from_email VARCHAR(255) NOT NULL,
    cc TEXT,
    bcc TEXT,
    
    subject VARCHAR(500) NOT NULL,
    body TEXT,
    
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    -- pending, sent, failed, bounced
    
    sent_at TIMESTAMP,
    error_message TEXT,
    
    -- Contexto
    related_type VARCHAR(255),
    related_id BIGINT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_email_status CHECK (status IN 
        ('pending', 'sent', 'failed', 'bounced'))
);

CREATE INDEX idx_email_logs_to ON email_logs(to_email);
CREATE INDEX idx_email_logs_status ON email_logs(status);
CREATE INDEX idx_email_logs_related ON email_logs(related_type, related_id);
```

---

## 4. VISTAS ÚTILES

### Vista: Account Balances
```sql
CREATE VIEW account_balances AS
SELECT 
    a.id,
    a.company_id,
    a.code,
    a.name,
    a.type,
    COALESCE(SUM(jel.debit), 0) as total_debit,
    COALESCE(SUM(jel.credit), 0) as total_credit,
    CASE 
        WHEN a.type IN ('asset', 'expense', 'cost') 
        THEN COALESCE(SUM(jel.debit - jel.credit), 0)
        ELSE COALESCE(SUM(jel.credit - jel.debit), 0)
    END as balance
FROM accounts a
LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id AND je.status = 'posted'
GROUP BY a.id, a.company_id, a.code, a.name, a.type;
```

### Vista: Inventory Valuation
```sql
CREATE VIEW inventory_valuation AS
SELECT 
    p.id as product_id,
    p.company_id,
    p.sku,
    p.name,
    w.id as warehouse_id,
    w.name as warehouse_name,
    s.quantity,
    p.cost,
    (s.quantity * p.cost) as valuation
FROM products p
JOIN stock s ON s.product_id = p.id
JOIN warehouses w ON w.id = s.warehouse_id
WHERE p.track_inventory = true
    AND p.active = true
    AND s.quantity > 0;
```

### Vista: Accounts Receivable Aging
```sql
CREATE VIEW accounts_receivable_aging AS
SELECT 
    i.id,
    i.company_id,
    c.name as client_name,
    i.invoice_number,
    i.date,
    i.due_date,
    i.total,
    i.balance,
    CURRENT_DATE - i.due_date as days_overdue,
    CASE 
        WHEN i.balance = 0 THEN 'Paid'
        WHEN CURRENT_DATE <= i.due_date THEN 'Current'
        WHEN CURRENT_DATE - i.due_date BETWEEN 1 AND 30 THEN '1-30 days'
        WHEN CURRENT_DATE - i.due_date BETWEEN 31 AND 60 THEN '31-60 days'
        WHEN CURRENT_DATE - i.due_date BETWEEN 61 AND 90 THEN '61-90 days'
        ELSE '90+ days'
    END as aging_bucket
FROM invoices i
JOIN clients c ON c.id = i.client_id
WHERE i.status NOT IN ('cancelled', 'draft')
    AND i.deleted_at IS NULL;
```

---

## 5. FUNCIONES Y TRIGGERS

### Trigger: Update Invoice Balance
```sql
CREATE OR REPLACE FUNCTION update_invoice_balance()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE invoices
    SET 
        paid_amount = (
            SELECT COALESCE(SUM(amount), 0)
            FROM payments
            WHERE invoice_id = NEW.invoice_id
        ),
        balance = total - (
            SELECT COALESCE(SUM(amount), 0)
            FROM payments
            WHERE invoice_id = NEW.invoice_id
        ),
        status = CASE 
            WHEN total <= (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = NEW.invoice_id)
            THEN 'paid'
            WHEN (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = NEW.invoice_id) > 0
            THEN 'partial'
            ELSE status
        END
    WHERE id = NEW.invoice_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tr_payment_update_invoice
AFTER INSERT OR UPDATE OR DELETE ON payments
FOR EACH ROW
EXECUTE FUNCTION update_invoice_balance();
```

### Trigger: Update Stock on Movement
```sql
CREATE OR REPLACE FUNCTION update_stock_on_movement()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE stock
    SET quantity = CASE 
        WHEN NEW.type IN ('in', 'adjustment') THEN quantity + NEW.quantity
        WHEN NEW.type = 'out' THEN quantity - NEW.quantity
        ELSE quantity
    END,
    updated_at = CURRENT_TIMESTAMP
    WHERE product_id = NEW.product_id
        AND warehouse_id = NEW.warehouse_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tr_stock_movement_update_stock
AFTER INSERT ON stock_movements
FOR EACH ROW
EXECUTE FUNCTION update_stock_on_movement();
```

---

## 6. CONSIDERACIONES DE SEGURIDAD

### Row Level Security (RLS)
```sql
-- Habilitar RLS en tablas multi-tenant
ALTER TABLE invoices ENABLE ROW LEVEL SECURITY;

CREATE POLICY company_isolation_policy ON invoices
FOR ALL
TO authenticated_users
USING (company_id = current_setting('app.current_company_id')::bigint);
```

### Encriptación de Datos Sensibles
- Campos como números de tarjeta de crédito deben encriptarse
- Usar `pgcrypto` extension para encriptación a nivel de columna

---

## 7. BACKUPS Y MANTENIMIENTO

### Estrategia de Backup
- **Full Backup**: Diario a las 2:00 AM
- **Incremental**: Cada 6 horas
- **Retención**: 30 días

### Comandos de Backup
```bash
# Full backup
pg_dump -h localhost -U user database_name > backup_$(date +%Y%m%d).sql

# Backup comprimido
pg_dump -h localhost -U user database_name | gzip > backup_$(date +%Y%m%d).sql.gz
```

### Mantenimiento Regular
```sql
-- Vacuum y análisis (semanal)
VACUUM ANALYZE;

-- Reindex (mensual)
REINDEX DATABASE database_name;

-- Limpiar logs antiguos (trimestral)
DELETE FROM audit_logs WHERE created_at < CURRENT_DATE - INTERVAL '1 year';
DELETE FROM email_logs WHERE created_at < CURRENT_DATE - INTERVAL '6 months';
```

---

## 8. MIGRACIONES

### Estrategia de Migraciones
1. **Nunca** alterar migraciones ejecutadas en producción
2. Crear nuevas migraciones para cambios
3. Incluir `down()` method para rollback
4. Probar en staging antes de producción
5. Ejecutar en ventana de mantenimiento

### Ejemplo de Migración Laravel
```php
// database/migrations/2025_11_21_000001_create_invoices_table.php
public function up()
{
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->restrictOnDelete();
        $table->foreignId('client_id')->constrained()->restrictOnDelete();
        $table->string('invoice_number', 50)->unique();
        // ... rest of columns
        $table->timestamps();
        $table->softDeletes();
        
        $table->index(['company_id', 'date']);
    });
}

public function down()
{
    Schema::dropIfExists('invoices');
}
```

---

## 9. SCRIPTS ÚTILES

### Inicializar Plan de Cuentas NIIF
```sql
-- Ver archivo separado: seeds/niif_chart_of_accounts.sql
```

### Datos de Prueba (Seeders)
```sql
-- Insertar empresa de prueba
INSERT INTO companies (name, tax_id, email, currency) 
VALUES ('Empresa Demo S.A.S.', '900123456-1', 'demo@ejemplo.com', 'COP');

-- Insertar roles básicos
INSERT INTO roles (name, description) VALUES
('super_admin', 'Administrador con todos los permisos'),
('admin', 'Administrador de empresa'),
('accountant', 'Contador'),
('seller', 'Vendedor'),
('warehouse', 'Almacenista');
```

---

## 10. DIAGRAMA DE DEPENDENCIAS

```
companies (raíz)
    ├── users
    │   ├── audit_logs
    │   └── notifications
    ├── clients
    │   ├── invoices
    │   │   ├── invoice_items
    │   │   └── payments
    │   └── quotations
    │       └── quotation_items
    ├── categories
    │   └── products
    │       ├── stock
    │       ├── stock_movements
    │       └── invoice_items / purchase_items
    ├── warehouses
    │   ├── stock
    │   └── stock_movements
    ├── suppliers
    │   ├── purchases
    │   │   └── purchase_items
    │   └── expenses
    ├── accounts
    │   ├── journal_entry_lines
    │   └── tax_settings
    └── journal_entries
        └── journal_entry_lines
```

---

**Última actualización:** Noviembre 2025  
**Mantenido por:** [Tu Nombre]  
**Próxima revisión:** Con cada cambio de esquema
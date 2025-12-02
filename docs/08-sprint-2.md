# 🏢 SPRINT 2: EMPRESAS Y FACTURACIÓN BÁSICA

**Duración:** 2 semanas (10 días laborables)  
**Objetivo:** Implementar gestión de empresas y facturación básica funcional

---

## 📋 ÍNDICE
1. [Visión General](#visión-general)
2. [Semana 1: Empresas y Maestros](#semana-1)
3. [Semana 2: Facturación](#semana-2)
4. [Arquitectura](#arquitectura)
5. [Base de Datos](#base-de-datos)
6. [Testing](#testing)

---

## 🎯 VISIÓN GENERAL

### ¿Por qué Empresas primero?
Las facturas **necesitan** una empresa emisora porque:
- La empresa es quien emite la factura
- La empresa tiene el RFC/NIT
- La empresa tiene los certificados digitales
- La empresa define las series de facturación

Sin empresas, las facturas no tienen sentido en el mundo real.

### Entregables del Sprint
- ✅ Módulo completo de Empresas (CRUD + configuración fiscal) **COMPLETADO**
- ⏳ Maestro de Clientes
- ⏳ Catálogo de Productos/Servicios
- ⏳ Facturación básica (crear, calcular, PDF)
- ⏳ Estados de factura
- ⏳ 60+ tests automatizados (98 tests actuales)

---

## 📅 SEMANA 1: EMPRESAS Y MAESTROS

### DÍA 1-2: MÓDULO DE EMPRESAS

#### Objetivos
- CRUD completo de empresas
- Configuración fiscal
- Relación empresa-usuario

#### Componentes a Crear

**Domain Layer:**
```
app/Domain/Company/
├── Entities/
│   └── Company.php
├── ValueObjects/
│   ├── CompanyId.php
│   ├── TaxId.php (RFC/NIT)
│   ├── Address.php
│   ├── PhoneNumber.php
│   └── TaxRegime.php
├── Exceptions/
│   ├── CompanyNotFoundException.php
│   ├── InvalidTaxIdException.php
│   └── DuplicateTaxIdException.php
└── Repositories/
    └── CompanyRepositoryInterface.php
```

**Application Layer:**
```
app/Application/Company/
├── DTOs/
│   ├── CreateCompanyDTO.php
│   ├── UpdateCompanyDTO.php
│   └── CompanyResponseDTO.php
└── Services/
    ├── CreateCompanyService.php
    ├── UpdateCompanyService.php
    ├── ListCompaniesService.php
    └── DeleteCompanyService.php
```

**Infrastructure Layer:**
```
app/Infrastructure/Persistence/Eloquent/
├── Models/
│   └── CompanyModel.php
└── Repositories/
    └── EloquentCompanyRepository.php
```

**Presentation Layer:**
```
app/Http/
├── Controllers/Api/
│   └── CompanyController.php
└── Requests/
    ├── CreateCompanyRequest.php
    └── UpdateCompanyRequest.php
```

#### Base de Datos

**Tabla: companies**
```sql
- id (bigserial)
- name (varchar 255) - Nombre comercial
- legal_name (varchar 255) - Razón social
- tax_id (varchar 50) UNIQUE - RFC/NIT
- email (varchar 255)
- phone (varchar 20)
- address_street (varchar 255)
- address_city (varchar 100)
- address_state (varchar 100)
- address_country (varchar 100)
- address_postal_code (varchar 10)
- tax_regime (varchar 100)
- logo_path (varchar 255)
- invoice_series (varchar 10)
- next_invoice_number (integer)
- is_active (boolean)
- created_at, updated_at, deleted_at
```

**Tabla: company_user** (relación muchos a muchos)
```sql
- id (bigserial)
- company_id (bigint FK)
- user_id (bigint FK)
- role (varchar 50) - Rol en esta empresa específica
- created_at
```

#### Endpoints
```
POST   /api/companies
GET    /api/companies
GET    /api/companies/{id}
PUT    /api/companies/{id}
DELETE /api/companies/{id}
POST   /api/companies/{id}/activate
POST   /api/companies/{id}/deactivate
POST   /api/companies/{id}/users
DELETE /api/companies/{id}/users/{userId}
GET    /api/companies/{id}/users
```

#### Tests (15+)
- Unit: Value Objects (TaxId, Address, PhoneNumber)
- Unit: Company Entity
- Integration: CompanyRepository
- Feature: Company API endpoints

---

### DÍA 3: CLIENTES

#### Objetivos
- CRUD de clientes
- Información fiscal
- Búsqueda y filtros

#### Componentes a Crear

**Domain Layer:**
```
app/Domain/Billing/
├── Entities/
│   └── Client.php
├── ValueObjects/
│   ├── ClientId.php
│   └── ClientType.php (Persona Física/Moral)
├── Exceptions/
│   └── ClientNotFoundException.php
└── Repositories/
    └── ClientRepositoryInterface.php
```

**Application Layer:**
```
app/Application/Billing/
├── DTOs/
│   ├── CreateClientDTO.php
│   ├── UpdateClientDTO.php
│   └── ClientResponseDTO.php
└── Services/
    ├── CreateClientService.php
    ├── UpdateClientService.php
    └── ListClientsService.php
```

#### Base de Datos

**Tabla: clients**
```sql
- id (bigserial)
- company_id (bigint FK) - Pertenece a una empresa
- name (varchar 255)
- tax_id (varchar 50)
- client_type (varchar 20) - 'fisica' o 'moral'
- email (varchar 255)
- phone (varchar 20)
- address_street, address_city, address_state, etc.
- tax_regime (varchar 100)
- is_active (boolean)
- created_at, updated_at, deleted_at
```

#### Endpoints
```
POST   /api/clients
GET    /api/clients
GET    /api/clients/{id}
PUT    /api/clients/{id}
DELETE /api/clients/{id}
```

#### Tests (10+)
- Unit: Client Entity
- Integration: ClientRepository
- Feature: Client API

---

### DÍA 4-5: PRODUCTOS/SERVICIOS

#### Objetivos
- Catálogo de productos
- Categorías
- Precios e impuestos

#### Componentes a Crear

**Domain Layer:**
```
app/Domain/Billing/
├── Entities/
│   ├── Product.php
│   └── ProductCategory.php
├── ValueObjects/
│   ├── ProductId.php
│   ├── Price.php
│   ├── SKU.php
│   └── ProductType.php (producto/servicio)
└── Repositories/
    ├── ProductRepositoryInterface.php
    └── ProductCategoryRepositoryInterface.php
```

#### Base de Datos

**Tabla: product_categories**
```sql
- id (bigserial)
- company_id (bigint FK)
- name (varchar 255)
- description (text)
- created_at, updated_at
```

**Tabla: products**
```sql
- id (bigserial)
- company_id (bigint FK)
- category_id (bigint FK nullable)
- sku (varchar 50) UNIQUE
- name (varchar 255)
- description (text)
- product_type (varchar 20) - 'product' o 'service'
- price (decimal 10,2)
- cost (decimal 10,2)
- tax_rate (decimal 5,2) - % de impuesto
- unit (varchar 20) - 'pieza', 'kg', 'litro', etc.
- stock (integer) - solo para productos
- min_stock (integer)
- is_active (boolean)
- created_at, updated_at, deleted_at
```

#### Endpoints
```
POST   /api/categories
GET    /api/categories
PUT    /api/categories/{id}
DELETE /api/categories/{id}

POST   /api/products
GET    /api/products
GET    /api/products/{id}
PUT    /api/products/{id}
DELETE /api/products/{id}
```

#### Tests (15+)
- Unit: Product Entity, Price VO
- Integration: ProductRepository
- Feature: Product API

---

## 📅 SEMANA 2: FACTURACIÓN

### DÍA 1-3: CREACIÓN DE FACTURAS

#### Objetivos
- Crear facturas con múltiples items
- Cálculo automático de impuestos
- Validaciones de negocio

#### Componentes a Crear

**Domain Layer:**
```
app/Domain/Billing/
├── Entities/
│   ├── Invoice.php
│   └── InvoiceItem.php
├── ValueObjects/
│   ├── InvoiceId.php
│   ├── InvoiceNumber.php
│   ├── InvoiceStatus.php
│   └── TaxRate.php
├── Services/
│   ├── InvoiceTotalCalculator.php
│   └── TaxCalculator.php
├── Events/
│   ├── InvoiceCreated.php
│   └── InvoiceStatusChanged.php
└── Repositories/
    └── InvoiceRepositoryInterface.php
```

#### Base de Datos

**Tabla: invoices**
```sql
- id (bigserial)
- company_id (bigint FK)
- client_id (bigint FK)
- invoice_number (varchar 50) UNIQUE
- invoice_date (date)
- due_date (date)
- status (varchar 20) - 'draft', 'issued', 'paid', 'cancelled', 'voided'
- subtotal (decimal 12,2)
- tax_amount (decimal 12,2)
- total (decimal 12,2)
- notes (text)
- created_by (bigint FK users)
- created_at, updated_at, deleted_at
```

**Tabla: invoice_items**
```sql
- id (bigserial)
- invoice_id (bigint FK)
- product_id (bigint FK)
- description (varchar 255)
- quantity (decimal 10,2)
- unit_price (decimal 10,2)
- tax_rate (decimal 5,2)
- subtotal (decimal 10,2)
- tax_amount (decimal 10,2)
- total (decimal 10,2)
- created_at, updated_at
```

#### Endpoints
```
POST   /api/invoices
GET    /api/invoices
GET    /api/invoices/{id}
PUT    /api/invoices/{id}
DELETE /api/invoices/{id}
POST   /api/invoices/{id}/items
DELETE /api/invoices/{id}/items/{itemId}
```

#### Tests (20+)
- Unit: Invoice Entity
- Unit: InvoiceTotalCalculator
- Unit: TaxCalculator
- Integration: InvoiceRepository
- Feature: Invoice API

---

### DÍA 4: PDF Y ESTADOS

#### Objetivos
- Generar PDF de factura
- Implementar máquina de estados

#### Componentes a Crear

**Infrastructure:**
```
app/Infrastructure/PDF/
└── InvoicePDFGenerator.php
```

**Domain:**
```
app/Domain/Billing/
└── StateMachines/
    └── InvoiceStateMachine.php
```

#### Estados de Factura
```
draft → issued → paid
  ↓       ↓
cancelled ← voided
```

**Transiciones válidas:**
- draft → issued
- draft → cancelled
- issued → paid
- issued → voided
- paid → voided (con autorización)

#### Endpoints
```
GET  /api/invoices/{id}/pdf
POST /api/invoices/{id}/issue
POST /api/invoices/{id}/mark-as-paid
POST /api/invoices/{id}/cancel
POST /api/invoices/{id}/void
```

#### Tests (10+)
- Unit: InvoiceStateMachine
- Integration: PDF generation
- Feature: Estado transitions

---

### DÍA 5: TESTING Y REFINAMIENTO

#### Objetivos
- Completar cobertura de tests
- Refactoring
- Documentación

#### Actividades
- Tests E2E de flujo completo
- Performance testing
- Code review
- Actualizar documentación API
- Preparar demo

---

## 🏗️ ARQUITECTURA

### Módulos del Sprint 2
```
Domain/
├── Company/          (Gestión de empresas)
├── Billing/          (Facturación, clientes, productos)
└── Shared/
    └── ValueObjects/
        ├── Money.php
        ├── Email.php
        └── Address.php (compartido)
```

### Patrones Aplicados
- **Arquitectura Hexagonal**
- **Domain-Driven Design**
- **Repository Pattern**
- **Service Layer Pattern**
- **Value Objects**
- **Domain Events**
- **State Machine**

---

## 🗄️ BASE DE DATOS

### Tablas Nuevas (6)
1. `companies`
2. `company_user`
3. `clients`
4. `product_categories`
5. `products`
6. `invoices`
7. `invoice_items`

### Relaciones
```
companies 1:N clients
companies 1:N products
companies 1:N invoices
companies N:M users (company_user)
clients 1:N invoices
products 1:N invoice_items
invoices 1:N invoice_items
```

---

## 🧪 TESTING

### Objetivos de Cobertura
- **Unit Tests:** 90%+
- **Integration Tests:** 80%+
- **Feature Tests:** 100% de endpoints

### Tests Estimados
- Empresas: 15 tests
- Clientes: 10 tests
- Productos: 15 tests
- Facturas: 20 tests
- PDF/Estados: 10 tests
- **Total: 70+ tests**

---

## 📊 MÉTRICAS DE ÉXITO

### Técnicas
- [ ] 70+ tests pasando
- [ ] Cobertura > 85%
- [ ] 0 errores de linting
- [ ] 0 vulnerabilidades de seguridad

### Funcionales
- [ ] Crear empresa funcional
- [ ] Crear cliente funcional
- [ ] Crear producto funcional
- [ ] Crear factura con cálculos correctos
- [ ] Generar PDF correcto
- [ ] Transiciones de estado válidas

### No Funcionales
- [ ] Tiempo de respuesta < 200ms (promedio)
- [ ] PDF generado en < 2 segundos
- [ ] API documentada (Postman/Swagger)

---

## 📅 CRONOGRAMA DETALLADO

| Día | Tarea | Horas | Entregable |
|-----|-------|-------|------------|
| 1 | Empresas - Domain | 4h | Value Objects, Entity |
| 1 | Empresas - Infra | 4h | Model, Repository |
| 2 | Empresas - App/API | 4h | Services, Controller |
| 2 | Empresas - Tests | 4h | 15 tests |
| 3 | Clientes - Completo | 8h | CRUD + 10 tests |
| 4 | Productos - Domain/Infra | 8h | Entities, Repository |
| 5 | Productos - App/Tests | 8h | Services + 15 tests |
| 6 | Facturas - Domain | 8h | Entities, Calculators |
| 7 | Facturas - Infra/App | 8h | Repository, Services |
| 8 | Facturas - API/Tests | 8h | Controller + 20 tests |
| 9 | PDF + Estados | 8h | Generator, StateMachine |
| 10 | Testing final | 8h | E2E, refinamiento |

**Total: 80 horas (2 semanas a 40h/semana)**

---

## 🎯 DEFINICIÓN DE "HECHO" (DoD)

Una funcionalidad está "hecha" cuando:
- [ ] Código implementado siguiendo arquitectura hexagonal
- [ ] Tests unitarios pasando (>90% cobertura)
- [ ] Tests de integración pasando
- [ ] Tests de feature pasando
- [ ] Code review aprobado
- [ ] Sin deuda técnica
- [ ] Documentación actualizada
- [ ] API documentada en Postman
- [ ] Demo funcional
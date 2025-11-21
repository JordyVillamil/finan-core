# Documento de Arquitectura del Sistema

## 📐 System Architecture Document (SAD)

**Proyecto:** Sistema Contable y Administrativo  
**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Estado:** Aprobado  

---

## 1. INTRODUCCIÓN

### 1.1 Propósito
Este documento describe la arquitectura del sistema contable y administrativo, incluyendo decisiones arquitectónicas, patrones utilizados, componentes principales y su interacción.

### 1.2 Alcance
Cubre la arquitectura técnica completa: backend, frontend, base de datos, integraciones y infraestructura.

### 1.3 Definiciones

**Arquitectura Hexagonal (Ports & Adapters)**
- Patrón que separa la lógica de negocio del mundo exterior
- El dominio (núcleo) no depende de frameworks o tecnologías
- Interfaces (puertos) definen cómo interactuar con el exterior
- Implementaciones (adaptadores) conectan con tecnologías específicas

**Domain-Driven Design (DDD)**
- Enfoque de diseño centrado en el dominio del negocio
- Ubiquitous Language: lenguaje común entre técnicos y expertos del negocio
- Bounded Contexts: límites claros entre diferentes áreas del negocio
- Entities, Value Objects, Aggregates, Services, Repositories

**Clean Architecture**
- Independencia de frameworks
- Testeable
- Independiente de UI
- Independiente de base de datos
- Independiente de cualquier agente externo

---

## 2. VISIÓN ARQUITECTÓNICA

### 2.1 Principios Arquitectónicos

1. **Separación de Responsabilidades**
   - Cada capa tiene una responsabilidad única y bien definida
   - No mezclar lógica de negocio con lógica de presentación o infraestructura

2. **Independencia de Frameworks**
   - El núcleo del negocio no debe depender de Laravel, React u otras librerías
   - Los frameworks son herramientas, no la arquitectura

3. **Testeable**
   - Toda lógica de negocio debe ser testeable sin dependencias externas
   - Tests unitarios sin necesidad de base de datos o HTTP

4. **Orientado al Dominio**
   - Modelar la realidad del negocio contable
   - Código que expresa reglas de negocio claramente

5. **API First**
   - Backend como API REST pura
   - Frontend consume API como cualquier cliente externo

6. **Escalabilidad**
   - Componentes desacoplados
   - Preparado para crecimiento horizontal

---

## 3. ARQUITECTURA GENERAL

### 3.1 Diagrama de Alto Nivel

```
┌─────────────────────────────────────────────────────────────┐
│                        CAPA DE PRESENTACIÓN                  │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              FRONTEND (React + TypeScript)            │  │
│  │                                                        │  │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐          │  │
│  │  │  Billing │  │Inventory │  │Accounting│  ...     │  │
│  │  │  Module  │  │  Module  │  │  Module  │          │  │
│  │  └──────────┘  └──────────┘  └──────────┘          │  │
│  │                                                        │  │
│  │  State Management (Zustand/Redux)                    │  │
│  │  API Client (Axios)                                  │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                             ↕ HTTP/REST + JWT
┌─────────────────────────────────────────────────────────────┐
│                        API GATEWAY                           │
│                   (Laravel Sanctum Auth)                     │
└─────────────────────────────────────────────────────────────┘
                             ↕
┌─────────────────────────────────────────────────────────────┐
│                    CAPA DE APLICACIÓN (BACKEND)              │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              Controllers / API Resources              │  │
│  │         (Punto de entrada HTTP - Adaptadores)        │  │
│  └──────────────────────────────────────────────────────┘  │
│                             ↕                                 │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              Application Services Layer               │  │
│  │           (Casos de Uso / Orquestación)              │  │
│  │                                                        │  │
│  │  CreateInvoiceService                                 │  │
│  │  ProcessPaymentService                                │  │
│  │  UpdateInventoryService                               │  │
│  │  ...                                                   │  │
│  └──────────────────────────────────────────────────────┘  │
│                             ↕                                 │
│  ┌──────────────────────────────────────────────────────┐  │
│  │                 DOMAIN LAYER (CORE)                   │  │
│  │              ¡LA LÓGICA DE NEGOCIO PURA!             │  │
│  │                                                        │  │
│  │  ┌───────────────────────────────────────────────┐  │  │
│  │  │        Billing Domain (Facturación)           │  │  │
│  │  │                                                │  │  │
│  │  │  Entities: Invoice, InvoiceItem, Client       │  │  │
│  │  │  Value Objects: Money, TaxRate, InvoiceNumber│  │  │
│  │  │  Domain Services: InvoiceTotalCalculator      │  │  │
│  │  │  Domain Events: InvoiceCreated, InvoicePaid   │  │  │
│  │  │  Repository Interfaces (Ports)                │  │  │
│  │  └───────────────────────────────────────────────┘  │  │
│  │                                                        │  │
│  │  ┌───────────────────────────────────────────────┐  │  │
│  │  │       Inventory Domain (Inventario)           │  │  │
│  │  │                                                │  │  │
│  │  │  Entities: Product, Stock, Warehouse          │  │  │
│  │  │  Value Objects: SKU, Quantity, UnitPrice      │  │  │
│  │  │  Domain Services: StockMovementHandler        │  │  │
│  │  │  Domain Events: StockUpdated, LowStockAlert   │  │  │
│  │  └───────────────────────────────────────────────┘  │  │
│  │                                                        │  │
│  │  ┌───────────────────────────────────────────────┐  │  │
│  │  │      Accounting Domain (Contabilidad)         │  │  │
│  │  │                                                │  │  │
│  │  │  Entities: Account, JournalEntry, Ledger      │  │  │
│  │  │  Value Objects: AccountCode, Amount, Date     │  │  │
│  │  │  Domain Services: BalanceCalculator           │  │  │
│  │  │  Domain Events: EntryPosted                    │  │  │
│  │  └───────────────────────────────────────────────┘  │  │
│  │                                                        │  │
│  │  ┌───────────────────────────────────────────────┐  │  │
│  │  │          Shared Kernel (Compartido)           │  │  │
│  │  │                                                │  │  │
│  │  │  Common Value Objects, Interfaces, Exceptions │  │  │
│  │  └───────────────────────────────────────────────┘  │  │
│  └──────────────────────────────────────────────────────┘  │
│                             ↕                                 │
│  ┌──────────────────────────────────────────────────────┐  │
│  │            INFRASTRUCTURE LAYER (ADAPTADORES)         │  │
│  │                                                        │  │
│  │  Repositories (Eloquent)                              │  │
│  │  External APIs (DIAN, Payment Gateways)              │  │
│  │  File Storage (S3, Local)                            │  │
│  │  Email Service (SMTP, Mailgun)                       │  │
│  │  PDF Generator (DomPDF)                              │  │
│  │  Queue System (Redis)                                │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                             ↕
┌─────────────────────────────────────────────────────────────┐
│                    CAPA DE PERSISTENCIA                      │
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │  PostgreSQL  │  │    Redis     │  │  File System │     │
│  │  (Principal) │  │ (Cache/Queue)│  │   (Storage)  │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. PATRONES ARQUITECTÓNICOS

### 4.1 Arquitectura Hexagonal (Ports & Adapters)

#### Concepto
El sistema se organiza en capas concéntricas donde el núcleo (dominio) no conoce las capas externas.

#### Implementación en el Proyecto

**PUERTOS (Interfaces)**
- Definen CÓMO el dominio se comunica con el exterior
- Ejemplo: `InvoiceRepositoryInterface`

**ADAPTADORES (Implementaciones)**
- Implementan los puertos usando tecnologías específicas
- Ejemplo: `EloquentInvoiceRepository implements InvoiceRepositoryInterface`

#### Ventajas
- Fácil cambiar de ORM, base de datos o framework
- Testing sin dependencias externas
- Código de dominio limpio y enfocado

### 4.2 Domain-Driven Design (DDD)

#### Bounded Contexts

Identificamos los siguientes contextos delimitados:

1. **Billing Context (Facturación)**
   - Facturación electrónica
   - Cotizaciones
   - Notas crédito/débito

2. **Inventory Context (Inventario)**
   - Productos/servicios
   - Bodegas
   - Movimientos de stock

3. **Purchasing Context (Compras)**
   - Proveedores
   - Órdenes de compra
   - Compras

4. **Accounting Context (Contabilidad)**
   - Plan de cuentas
   - Asientos contables
   - Estados financieros

5. **Collections Context (Cobranza)**
   - Cuentas por cobrar
   - Pagos
   - Recordatorios

6. **Shared Context (Compartido)**
   - Usuarios
   - Roles
   - Empresas

#### Building Blocks DDD

**Entities (Entidades)**
- Objetos con identidad única
- Ejemplos: `Invoice`, `Product`, `Account`

```php
// Domain/Billing/Entities/Invoice.php
class Invoice
{
    private InvoiceId $id;
    private ClientId $clientId;
    private InvoiceNumber $number;
    private InvoiceDate $date;
    private InvoiceItemCollection $items;
    private InvoiceStatus $status;
    private Money $total;
    
    public function addItem(InvoiceItem $item): void
    {
        $this->items->add($item);
        $this->recalculateTotal();
    }
    
    public function markAsPaid(): void
    {
        if (!$this->status->canBeMarkedAsPaid()) {
            throw new InvalidInvoiceStateException();
        }
        
        $this->status = InvoiceStatus::paid();
        // Disparar evento de dominio
        $this->recordEvent(new InvoicePaid($this->id));
    }
}
```

**Value Objects**
- Objetos sin identidad, definidos por sus atributos
- Inmutables
- Ejemplos: `Money`, `Email`, `InvoiceNumber`

```php
// Domain/Shared/ValueObjects/Money.php
final class Money
{
    private float $amount;
    private Currency $currency;
    
    public function __construct(float $amount, Currency $currency)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
        
        $this->amount = $amount;
        $this->currency = $currency;
    }
    
    public function add(Money $other): self
    {
        if (!$this->currency->equals($other->currency)) {
            throw new CurrencyMismatchException();
        }
        
        return new self($this->amount + $other->amount, $this->currency);
    }
    
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount 
            && $this->currency->equals($other->currency);
    }
}
```

**Aggregates (Agregados)**
- Grupo de entidades tratadas como una unidad
- Aggregate Root: punto de entrada al agregado
- Ejemplo: `Invoice` es aggregate root de `InvoiceItem`

**Domain Services**
- Lógica de negocio que no pertenece a una entidad
- Ejemplo: `InvoiceTotalCalculator`, `TaxCalculator`

```php
// Domain/Billing/Services/InvoiceTotalCalculator.php
class InvoiceTotalCalculator
{
    public function calculate(Invoice $invoice): Money
    {
        $subtotal = $this->calculateSubtotal($invoice);
        $taxes = $this->calculateTaxes($invoice, $subtotal);
        $discounts = $this->calculateDiscounts($invoice, $subtotal);
        
        return $subtotal->add($taxes)->subtract($discounts);
    }
}
```

**Repositories (Interfaces)**
- Abstracciones para persistencia
- Se definen en el dominio, se implementan en infraestructura

```php
// Domain/Billing/Repositories/InvoiceRepositoryInterface.php
interface InvoiceRepositoryInterface
{
    public function findById(InvoiceId $id): ?Invoice;
    public function save(Invoice $invoice): void;
    public function nextInvoiceNumber(): InvoiceNumber;
    public function findPendingByClient(ClientId $clientId): InvoiceCollection;
}
```

**Domain Events**
- Eventos que ocurren en el dominio
- Permiten desacoplamiento entre bounded contexts

```php
// Domain/Billing/Events/InvoicePaid.php
class InvoicePaid implements DomainEvent
{
    public function __construct(
        public readonly InvoiceId $invoiceId,
        public readonly Money $amount,
        public readonly DateTimeImmutable $paidAt
    ) {}
}

// Listener en otro contexto
class UpdateAccountingOnInvoicePaid
{
    public function handle(InvoicePaid $event): void
    {
        // Crear asiento contable automáticamente
    }
}
```

### 4.3 CQRS (Command Query Responsibility Segregation)

Aunque no implementaremos CQRS completo, usaremos el principio:

**Commands (Escritura)**
- Modifican el estado
- Ejemplo: `CreateInvoiceCommand`, `MarkInvoiceAsPaidCommand`

**Queries (Lectura)**
- Solo leen datos
- Optimizados para visualización
- Ejemplo: `GetInvoiceQuery`, `GetDashboardKPIsQuery`

```php
// Application/Billing/Commands/CreateInvoiceCommand.php
class CreateInvoiceCommand
{
    public function __construct(
        public readonly string $clientId,
        public readonly array $items,
        public readonly string $notes
    ) {}
}

// Application/Billing/CommandHandlers/CreateInvoiceHandler.php
class CreateInvoiceHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private ClientRepositoryInterface $clientRepository,
        private EventDispatcher $eventDispatcher
    ) {}
    
    public function handle(CreateInvoiceCommand $command): Invoice
    {
        $client = $this->clientRepository->findById($command->clientId);
        
        $invoice = Invoice::create(
            $this->repository->nextInvoiceNumber(),
            $client,
            $command->items,
            $command->notes
        );
        
        $this->repository->save($invoice);
        $this->eventDispatcher->dispatch(new InvoiceCreated($invoice->id()));
        
        return $invoice;
    }
}
```

---

## 5. ESTRUCTURA DE DIRECTORIOS DETALLADA

### 5.1 Backend (Laravel)

```
proyecto-contable/
├── backend/
│   ├── app/
│   │   ├── Domain/                    # CAPA DE DOMINIO (núcleo)
│   │   │   ├── Billing/
│   │   │   │   ├── Entities/
│   │   │   │   │   ├── Invoice.php
│   │   │   │   │   ├── InvoiceItem.php
│   │   │   │   │   └── Client.php
│   │   │   │   ├── ValueObjects/
│   │   │   │   │   ├── InvoiceNumber.php
│   │   │   │   │   ├── InvoiceStatus.php
│   │   │   │   │   └── TaxRate.php
│   │   │   │   ├── Services/
│   │   │   │   │   ├── InvoiceTotalCalculator.php
│   │   │   │   │   └── TaxCalculator.php
│   │   │   │   ├── Events/
│   │   │   │   │   ├── InvoiceCreated.php
│   │   │   │   │   └── InvoicePaid.php
│   │   │   │   ├── Exceptions/
│   │   │   │   │   └── InvalidInvoiceStateException.php
│   │   │   │   └── Repositories/
│   │   │   │       └── InvoiceRepositoryInterface.php
│   │   │   │
│   │   │   ├── Inventory/
│   │   │   │   ├── Entities/
│   │   │   │   ├── ValueObjects/
│   │   │   │   ├── Services/
│   │   │   │   ├── Events/
│   │   │   │   └── Repositories/
│   │   │   │
│   │   │   ├── Accounting/
│   │   │   │   └── (similar structure)
│   │   │   │
│   │   │   ├── Purchasing/
│   │   │   │   └── (similar structure)
│   │   │   │
│   │   │   ├── Collections/
│   │   │   │   └── (similar structure)
│   │   │   │
│   │   │   └── Shared/            # Compartido entre contextos
│   │   │       ├── ValueObjects/
│   │   │       │   ├── Money.php
│   │   │       │   ├── Email.php
│   │   │       │   ├── Currency.php
│   │   │       │   └── Date.php
│   │   │       ├── Interfaces/
│   │   │       └── Exceptions/
│   │   │
│   │   ├── Application/            # CAPA DE APLICACIÓN (casos de uso)
│   │   │   ├── Billing/
│   │   │   │   ├── Commands/
│   │   │   │   │   ├── CreateInvoiceCommand.php
│   │   │   │   │   └── MarkInvoiceAsPaidCommand.php
│   │   │   │   ├── Queries/
│   │   │   │   │   ├── GetInvoiceQuery.php
│   │   │   │   │   └── ListInvoicesQuery.php
│   │   │   │   ├── Handlers/
│   │   │   │   │   ├── CreateInvoiceHandler.php
│   │   │   │   │   └── GetInvoiceHandler.php
│   │   │   │   ├── DTOs/
│   │   │   │   │   ├── InvoiceDTO.php
│   │   │   │   │   └── CreateInvoiceDTO.php
│   │   │   │   └── Services/
│   │   │   │       └── InvoiceApplicationService.php
│   │   │   │
│   │   │   ├── Inventory/
│   │   │   ├── Accounting/
│   │   │   └── (otros contextos)
│   │   │
│   │   ├── Infrastructure/          # CAPA DE INFRAESTRUCTURA (adaptadores)
│   │   │   ├── Persistence/
│   │   │   │   ├── Eloquent/
│   │   │   │   │   ├── Models/
│   │   │   │   │   │   ├── InvoiceModel.php
│   │   │   │   │   │   ├── ProductModel.php
│   │   │   │   │   │   └── AccountModel.php
│   │   │   │   │   └── Repositories/
│   │   │   │   │       ├── EloquentInvoiceRepository.php
│   │   │   │   │       └── EloquentProductRepository.php
│   │   │   │   └── Migrations/
│   │   │   │
│   │   │   ├── External/            # APIs externas
│   │   │   │   ├── DIAN/
│   │   │   │   │   ├── DIANClient.php
│   │   │   │   │   └── DIANAdapter.php
│   │   │   │   └── PaymentGateways/
│   │   │   │       └── WompiAdapter.php
│   │   │   │
│   │   │   ├── Email/
│   │   │   │   ├── LaravelMailer.php
│   │   │   │   └── Templates/
│   │   │   │
│   │   │   ├── Storage/
│   │   │   │   └── LaravelStorage.php
│   │   │   │
│   │   │   ├── PDF/
│   │   │   │   └── DomPDFGenerator.php
│   │   │   │
│   │   │   └── Queue/
│   │   │       └── RedisQueue.php
│   │   │
│   │   ├── Presentation/            # CAPA DE PRESENTACIÓN
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── API/
│   │   │   │   │   │   ├── Billing/
│   │   │   │   │   │   │   ├── InvoiceController.php
│   │   │   │   │   │   │   └── ClientController.php
│   │   │   │   │   │   ├── Inventory/
│   │   │   │   │   │   ├── Accounting/
│   │   │   │   │   │   └── Auth/
│   │   │   │   │   │       └── AuthController.php
│   │   │   │   │
│   │   │   │   ├── Requests/        # Form Requests
│   │   │   │   │   ├── CreateInvoiceRequest.php
│   │   │   │   │   └── UpdateProductRequest.php
│   │   │   │   │
│   │   │   │   ├── Resources/       # API Resources
│   │   │   │   │   ├── InvoiceResource.php
│   │   │   │   │   └── ProductResource.php
│   │   │   │   │
│   │   │   │   └── Middleware/
│   │   │   │       ├── Authenticate.php
│   │   │   │       └── CheckPermission.php
│   │   │   │
│   │   │   └── CLI/                 # Comandos Artisan
│   │   │       └── Commands/
│   │   │
│   │   └── Support/                 # Helpers y utilidades
│   │       ├── Helpers/
│   │       ├── Traits/
│   │       └── Enums/
│   │
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   │
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   │
│   ├── tests/
│   │   ├── Unit/
│   │   │   ├── Domain/              # Tests de dominio (puros)
│   │   │   └── Application/
│   │   ├── Feature/                 # Tests de integración
│   │   └── E2E/                     # Tests end-to-end
│   │
│   ├── storage/
│   ├── public/
│   ├── .env.example
│   ├── composer.json
│   └── phpunit.xml
```

### 5.2 Frontend (React)

```
frontend/
├── src/
│   ├── features/                    # Módulos por funcionalidad
│   │   ├── auth/
│   │   │   ├── components/
│   │   │   │   ├── LoginForm.tsx
│   │   │   │   └── RegisterForm.tsx
│   │   │   ├── hooks/
│   │   │   │   └── useAuth.ts
│   │   │   ├── services/
│   │   │   │   └── authService.ts
│   │   │   ├── store/
│   │   │   │   └── authSlice.ts
│   │   │   └── types/
│   │   │       └── auth.types.ts
│   │   │
│   │   ├── billing/
│   │   │   ├── components/
│   │   │   │   ├── InvoiceForm.tsx
│   │   │   │   ├── InvoiceList.tsx
│   │   │   │   └── InvoiceDetail.tsx
│   │   │   ├── hooks/
│   │   │   │   ├── useInvoices.ts
│   │   │   │   └── useInvoiceForm.ts
│   │   │   ├── services/
│   │   │   │   └── invoiceService.ts
│   │   │   ├── store/
│   │   │   │   └── invoiceSlice.ts
│   │   │   └── types/
│   │   │       └── invoice.types.ts
│   │   │
│   │   ├── inventory/
│   │   ├── accounting/
│   │   ├── purchasing/
│   │   └── dashboard/
│   │
│   ├── shared/                      # Compartido entre features
│   │   ├── components/
│   │   │   ├── ui/
│   │   │   │   ├── Button.tsx
│   │   │   │   ├── Input.tsx
│   │   │   │   ├── Modal.tsx
│   │   │   │   └── Table.tsx
│   │   │   ├── layout/
│   │   │   │   ├── Header.tsx
│   │   │   │   ├── Sidebar.tsx
│   │   │   │   └── Footer.tsx
│   │   │   └── common/
│   │   │       ├── LoadingSpinner.tsx
│   │   │       └── ErrorBoundary.tsx
│   │   │
│   │   ├── hooks/
│   │   │   ├── useApi.ts
│   │   │   ├── useDebounce.ts
│   │   │   └── usePermissions.ts
│   │   │
│   │   ├── utils/
│   │   │   ├── formatters.ts
│   │   │   ├── validators.ts
│   │   │   └── constants.ts
│   │   │
│   │   ├── types/
│   │   │   └── common.types.ts
│   │   │
│   │   └── styles/
│   │       └── theme.ts
│   │
│   ├── services/                    # Configuración de APIs
│   │   ├── api.ts                   # Axios instance
│   │   └── interceptors.ts
│   │
│   ├── store/                       # Estado global
│   │   ├── index.ts
│   │   └── rootReducer.ts
│   │
│   ├── layouts/
│   │   ├── AuthLayout.tsx
│   │   └── DashboardLayout.tsx
│   │
│   ├── routes/
│   │   ├── index.tsx
│   │   ├── PrivateRoute.tsx
│   │   └── routes.config.ts
│   │
│   ├── App.tsx
│   ├── main.tsx
│   └── vite-env.d.ts
│
├── public/
├── tests/
│   ├── unit/
│   ├── integration/
│   └── e2e/
│
├── .env.example
├── package.json
├── tsconfig.json
├── vite.config.ts
└── tailwind.config.js
```

---

## 6. TECNOLOGÍAS Y HERRAMIENTAS

### 6.1 Backend Stack

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| PHP | 8.2+ | Lenguaje principal |
| Laravel | 11.x | Framework web |
| PostgreSQL | 15+ | Base de datos principal |
| Redis | 7+ | Cache y colas |
| Composer | 2.x | Gestor de dependencias |

**Librerías Laravel:**
- `laravel/sanctum` - Autenticación API
- `spatie/laravel-permission` - Gestión de roles
- `barryvdh/laravel-dompdf` - Generación de PDF
- `milon/barcode` - Códigos de barras
- `laravel/telescope` - Debugging (dev)
- `laravel/horizon` - Monitoreo de colas

### 6.2 Frontend Stack

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| React | 18.x | Librería UI |
| TypeScript | 5.x | Tipado estático |
| Vite | 5.x | Build tool |
| Tailwind CSS | 3.x | Framework CSS |
| Axios | 1.x | Cliente HTTP |

**Librerías React:**
- `zustand` o `@reduxjs/toolkit` - Estado global
- `react-router-dom` - Routing
- `react-hook-form` - Formularios
- `zod` - Validación de esquemas
- `@tanstack/react-table` - Tablas
- `recharts` - Gráficos
- `lucide-react` - Iconos
- `react-query` - Data fetching

### 6.3 DevOps y Herramientas

| Herramienta | Propósito |
|-------------|-----------|
| Docker | Containerización |
| Docker Compose | Orquestación local |
| GitHub Actions | CI/CD |
| PHPUnit | Testing backend |
| Pest | Testing moderno PHP |
| Jest | Testing frontend |
| React Testing Library | Testing componentes |
| Prettier | Formateo de código |
| ESLint | Linting JavaScript/TypeScript |
| PHP CS Fixer | Linting PHP |
| Swagger/Scribe | Documentación API |

---

## 7. BASE DE DATOS

### 7.1 Esquema Relacional (Simplificado)

```sql
-- DOMINIO: Shared (Usuarios y Configuración)
users
├── id (PK)
├── name
├── email (unique)
├── password
├── active
└── timestamps

roles
├── id (PK)
├── name
└── permissions (jsonb)

companies
├── id (PK)
├── name
├── tax_id
├── address
├── logo_url
└── settings (jsonb)

-- DOMINIO: Billing (Facturación)
clients
├── id (PK)
├── company_id (FK)
├── name
├── tax_id
├── email
├── phone
└── timestamps

invoices
├── id (PK)
├── company_id (FK)
├── client_id (FK)
├── invoice_number
├── date
├── due_date
├── subtotal
├── tax_amount
├── total
├── status (enum)
├── notes
└── timestamps

invoice_items
├── id (PK)
├── invoice_id (FK)
├── product_id (FK)
├── description
├── quantity
├── unit_price
├── tax_rate
├── discount
└── line_total

payments
├── id (PK)
├── invoice_id (FK)
├── amount
├── payment_date
├── payment_method
├── reference
└── timestamps

-- DOMINIO: Inventory (Inventario)
products
├── id (PK)
├── company_id (FK)
├── sku (unique)
├── name
├── description
├── unit_price
├── cost
├── category_id (FK)
├── unit_of_measure
├── barcode
├── taxable
├── active
└── timestamps

warehouses
├── id (PK)
├── company_id (FK)
├── name
├── address
└── active

stock
├── id (PK)
├── product_id (FK)
├── warehouse_id (FK)
├── quantity
├── min_quantity
└── timestamps

stock_movements
├── id (PK)
├── product_id (FK)
├── warehouse_id (FK)
├── type (enum: in, out, adjustment)
├── quantity
├── reference_type
├── reference_id
├── user_id (FK)
└── timestamp

-- DOMINIO: Purchasing (Compras)
suppliers
├── id (PK)
├── company_id (FK)
├── name
├── tax_id
├── email
├── phone
└── timestamps

purchases
├── id (PK)
├── company_id (FK)
├── supplier_id (FK)
├── purchase_number
├── date
├── total
├── status
└── timestamps

purchase_items
├── id (PK)
├── purchase_id (FK)
├── product_id (FK)
├── quantity
├── unit_price
└── line_total

-- DOMINIO: Accounting (Contabilidad)
accounts
├── id (PK)
├── company_id (FK)
├── code
├── name
├── type (enum: asset, liability, equity, income, expense)
├── parent_id (FK self)
└── active

journal_entries
├── id (PK)
├── company_id (FK)
├── entry_number
├── date
├── description
├── reference_type
├── reference_id
├── status
└── timestamps

journal_entry_lines
├── id (PK)
├── journal_entry_id (FK)
├── account_id (FK)
├── debit
├── credit
└── description
```

### 7.2 Índices Importantes

```sql
-- Facturación
CREATE INDEX idx_invoices_client_date ON invoices(client_id, date);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_due_date ON invoices(due_date);

-- Inventario
CREATE INDEX idx_stock_product_warehouse ON stock(product_id, warehouse_id);
CREATE INDEX idx_stock_movements_product_date ON stock_movements(product_id, created_at);

-- Contabilidad
CREATE INDEX idx_journal_entry_lines_account ON journal_entry_lines(account_id);
CREATE INDEX idx_journal_entries_date ON journal_entries(date);
```

---

## 8. SEGURIDAD

### 8.1 Autenticación y Autorización

**JWT con Laravel Sanctum**
- Tokens de acceso con expiración
- Refresh tokens
- Revocación de tokens

**Role-Based Access Control (RBAC)**
```php
// Middleware de permisos
Route::middleware(['auth:sanctum', 'permission:invoices.create'])
    ->post('/invoices', [InvoiceController::class, 'store']);

// En código
if ($user->can('invoices.view')) {
    // ...
}
```

### 8.2 Protección de Datos

**Encriptación**
- Contraseñas: bcrypt con factor 12+
- Datos sensibles en DB: Laravel encryption
- Comunicación: TLS 1.3

**Validación y Sanitización**
- Form Requests en Laravel
- Zod schemas en React
- Nunca confiar en input del usuario

**Headers de Seguridad**
```php
// En middleware
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Content-Security-Policy: default-src \'self\'');
```

### 8.3 Rate Limiting

```php
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    // 60 requests por minuto
});

Route::middleware(['throttle:login'])->post('/login');
// 5 intentos por minuto en login
```

---

## 9. INTEGRACIÓN CON DIAN

### 9.1 Arquitectura de Integración

```
┌──────────────┐
│   Invoice    │
│   Entity     │
└──────────────┘
       ↓
┌──────────────────────┐
│ DIANInvoiceAdapter   │  (Infrastructure)
│ - Transforma Invoice │
│   a formato DIAN     │
└──────────────────────┘
       ↓
┌──────────────────────┐
│   DIANApiClient      │
│ - HTTP client        │
│ - Firma electrónica  │
│ - XML generation     │
└──────────────────────┘
       ↓
    [DIAN API]
```

### 9.2 Flujo de Facturación Electrónica

1. Usuario crea factura en UI
2. Backend valida y guarda factura
3. Job asíncrono envía a DIAN:
   - Genera XML según formato DIAN
   - Firma digitalmente
   - Envía a API DIAN
   - Recibe CUFE
4. Actualiza factura con respuesta DIAN
5. Genera PDF con código QR
6. Envía email a cliente

---

## 10. DECISIONES ARQUITECTÓNICAS

### ADR-001: Uso de Arquitectura Hexagonal

**Contexto:** Necesitamos un sistema mantenible a largo plazo

**Decisión:** Implementar arquitectura hexagonal con DDD

**Consecuencias:**
- ✅ Lógica de negocio protegida y testeable
- ✅ Fácil cambiar tecnologías
- ❌ Mayor complejidad inicial
- ❌ Curva de aprendizaje

### ADR-002: PostgreSQL como Base de Datos

**Contexto:** Necesitamos base de datos confiable para datos financieros

**Decisión:** Usar PostgreSQL

**Razones:**
- Tipos de datos precisos (numeric para dinero)
- ACID completo
- JSON support para flexibilidad
- Excelente para datos relacionales
- Open source y maduro

### ADR-003: TypeScript en Frontend

**Decisión:** Usar TypeScript en React

**Razones:**
- Type safety reduce bugs
- Mejor IDE support
- Documentación implícita
- Facilita refactoring

### ADR-004: API REST sobre GraphQL

**Decisión:** API REST estándar

**Razones:**
- Más simple para este proyecto
- Mejor caching
- Más herramientas disponibles
- Equipo más familiarizado

---

## 11. TESTING

### 11.1 Estrategia de Testing

```
┌─────────────────────────────────────┐
│      E2E Tests (5%)                 │  Cypress / Playwright
│  Tests de flujos completos          │
├─────────────────────────────────────┤
│   Integration Tests (20%)           │  Feature tests Laravel
│  Tests de componentes integrados    │  + React Testing Library
├─────────────────────────────────────┤
│     Unit Tests (75%)                │  PHPUnit + Jest
│  Tests de unidades aisladas         │
└─────────────────────────────────────┘
      Testing Pyramid
```

### 11.2 Tests de Dominio

```php
// tests/Unit/Domain/Billing/InvoiceTest.php
class InvoiceTest extends TestCase
{
    /** @test */
    public function it_calculates_total_correctly()
    {
        $invoice = Invoice::create(/*...*/);
        $invoice->addItem(new InvoiceItem(/*...*/));
        
        $expectedTotal = Money::cop(119000); // 100k + 19% IVA
        
        $this->assertTrue($invoice->total()->equals($expectedTotal));
    }
    
    /** @test */
    public function it_cannot_be_marked_as_paid_if_cancelled()
    {
        $invoice = Invoice::create(/*...*/);
        $invoice->cancel();
        
        $this->expectException(InvalidInvoiceStateException::class);
        
        $invoice->markAsPaid();
    }
}
```

### 11.3 Cobertura Objetivo

- **Unit Tests**: > 90% en Domain layer
- **Integration Tests**: > 70% en Application layer
- **E2E Tests**: Flujos críticos principales

---

## 12. DEPLOYMENT

### 12.1 Ambientes

| Ambiente | Propósito | URL Ejemplo |
|----------|-----------|-------------|
| Local | Desarrollo | localhost |
| Development | Testing continuo | dev.ejemplo.com |
| Staging | Pre-producción | staging.ejemplo.com |
| Production | Usuarios finales | app.ejemplo.com |

### 12.2 CI/CD Pipeline

```yaml
# .github/workflows/ci.yml
name: CI/CD Pipeline

on: [push, pull_request]

jobs:
  backend-tests:
    runs-on: ubuntu-latest
    steps:
      - Checkout code
      - Setup PHP
      - Install dependencies
      - Run PHPUnit
      - Run PHP CS Fixer
      - Code coverage report
  
  frontend-tests:
    runs-on: ubuntu-latest
    steps:
      - Checkout code
      - Setup Node
      - Install dependencies
      - Run ESLint
      - Run Jest
      - Build production
  
  deploy-staging:
    needs: [backend-tests, frontend-tests]
    if: github.ref == 'refs/heads/develop'
    steps:
      - Deploy to staging
  
  deploy-production:
    needs: [backend-tests, frontend-tests]
    if: github.ref == 'refs/heads/main'
    steps:
      - Deploy to production
```

---

## 13. MONITOREO Y OBSERVABILIDAD

### 13.1 Logs

- **Laravel Log**: Errores y eventos importantes
- **Audit Log**: Acciones de usuario en tablas críticas
- **API Log**: Requests y responses

### 13.2 Métricas

- Tiempo de respuesta de APIs
- Tasa de errores
- Uso de recursos
- Facturas procesadas por hora

### 13.3 Alertas

- Errores 500 en producción
- Latencia > 2 segundos
- Disco > 80% uso
- Colas con > 1000 jobs pendientes

---

## 14. CONCLUSIÓN

Esta arquitectura proporciona:

✅ **Mantenibilidad**: Código limpio y organizado  
✅ **Escalabilidad**: Componentes desacoplados  
✅ **Testabilidad**: Lógica aislada y testeable  
✅ **Flexibilidad**: Fácil cambiar tecnologías  
✅ **Calidad**: Siguiendo mejores prácticas  

---

**Próximos Pasos:**
1. Setup del proyecto
2. Implementación de primer bounded context (Billing)
3. Iteración y refinamiento continuo

---

**Documentación Complementaria:**
- Documento de Requerimientos
- Modelo de Base de Datos
- Guía de Desarrollo
- Manual de Despliegue

**Última actualización:** Noviembre 2025  
**Mantenido por:** [Tu Nombre]
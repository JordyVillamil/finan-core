# DevOps, CI/CD y Guía de Desarrollo

## 🚀 DevOps, CI/CD & Development Guide

**Proyecto:** Sistema Contable y Administrativo  
**Versión:** 1.0  
**Fecha:** Noviembre 2025  

---

## 1. FILOSOFÍA DEVOPS

### 1.1 Principios Core

**Automatización**: Todo lo que se pueda automatizar, debe automatizarse  
**Integración Continua**: Merge código frecuentemente  
**Entrega Continua**: El código siempre está listo para producción  
**Infraestructura como Código**: Ambientes reproducibles  
**Monitoreo**: Visibilidad completa del sistema  
**Feedback Rápido**: Detectar problemas temprano  

### 1.2 Cultura DevOps en Proyecto Individual

- **Shift Left**: Testing y seguridad desde el inicio
- **Fail Fast**: Errores detectados lo antes posible
- **Automation First**: Scripts sobre procesos manuales
- **Documentation as Code**: Docs viven con el código

---

## 2. AMBIENTES

### 2.1 Estrategia de Ambientes

```
┌────────────────────────────────────────────────┐
│            DESARROLLO LOCAL                     │
│  - Docker Compose                              │
│  - Hot reload activo                           │
│  - Debugging habilitado                        │
│  - SQLite o PostgreSQL local                   │
└────────────────────────────────────────────────┘
                     ↓ Push to develop
┌────────────────────────────────────────────────┐
│              DEVELOPMENT (DEV)                  │
│  - Auto-deploy en cada commit a develop       │
│  - Testing automatizado                        │
│  - Datos de prueba                             │
│  URL: dev.proyecto-contable.com                │
└────────────────────────────────────────────────┘
                     ↓ Manual promote
┌────────────────────────────────────────────────┐
│             STAGING (STG)                       │
│  - Réplica de producción                      │
│  - Testing final                               │
│  - Datos semi-reales (anonimizados)           │
│  URL: staging.proyecto-contable.com            │
└────────────────────────────────────────────────┘
                     ↓ Release
┌────────────────────────────────────────────────┐
│            PRODUCCIÓN (PROD)                    │
│  - Deploy manual desde main                   │
│  - Usuarios reales                             │
│  - Monitoreo activo                            │
│  URL: app.proyecto-contable.com                │
└────────────────────────────────────────────────┘
```

### 2.2 Configuración por Ambiente

**Variables de Entorno:**

```bash
# .env.local
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=pgsql
DB_HOST=localhost
MAIL_DRIVER=log

# .env.development
APP_ENV=development
APP_DEBUG=true
DB_CONNECTION=pgsql
DB_HOST=dev-db.internal
MAIL_DRIVER=smtp

# .env.staging
APP_ENV=staging
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_HOST=stg-db.internal
MAIL_DRIVER=smtp

# .env.production
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_HOST=prod-db.internal
MAIL_DRIVER=smtp
SENTRY_DSN=https://...
```

---

## 3. DOCKER Y CONTAINERIZACIÓN

### 3.1 Arquitectura de Contenedores

```
┌─────────────────────────────────────────────┐
│            Docker Compose                    │
│                                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │  nginx   │  │  Laravel │  │  React   │ │
│  │  (80/443)│  │  (9000)  │  │  (3000)  │ │
│  └──────────┘  └──────────┘  └──────────┘ │
│       │             │              │        │
│  ┌────┴─────────────┴──────────────┘        │
│  │                                          │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │PostgreSQL│  │  Redis   │  │  Mailhog │ │
│  │  (5432)  │  │  (6379)  │  │  (1025)  │ │
│  └──────────┘  └──────────┘  └──────────┘ │
└─────────────────────────────────────────────┘
```

### 3.2 docker-compose.yml

```yaml
version: '3.8'

services:
  # Nginx Web Server
  nginx:
    image: nginx:alpine
    container_name: proyecto-nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./backend/public:/var/www/html/public
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./docker/nginx/ssl:/etc/nginx/ssl
    depends_on:
      - php
      - frontend
    networks:
      - proyecto-network

  # PHP-FPM (Laravel)
  php:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: proyecto-php
    volumes:
      - ./backend:/var/www/html
      - ./docker/php/php.ini:/usr/local/etc/php/php.ini
    environment:
      - APP_ENV=local
      - DB_HOST=postgres
      - REDIS_HOST=redis
    depends_on:
      - postgres
      - redis
    networks:
      - proyecto-network

  # React Frontend
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    container_name: proyecto-frontend
    ports:
      - "3000:3000"
    volumes:
      - ./frontend:/app
      - /app/node_modules
    environment:
      - VITE_API_URL=http://localhost/api
    networks:
      - proyecto-network

  # PostgreSQL Database
  postgres:
    image: postgres:15-alpine
    container_name: proyecto-postgres
    ports:
      - "5432:5432"
    environment:
      POSTGRES_DB: proyecto_contable
      POSTGRES_USER: proyecto_user
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres-data:/var/lib/postgresql/data
      - ./docker/postgres/init.sql:/docker-entrypoint-initdb.d/init.sql
    networks:
      - proyecto-network

  # Redis Cache & Queues
  redis:
    image: redis:7-alpine
    container_name: proyecto-redis
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    networks:
      - proyecto-network

  # Mailhog (Email Testing)
  mailhog:
    image: mailhog/mailhog
    container_name: proyecto-mailhog
    ports:
      - "1025:1025"
      - "8025:8025"
    networks:
      - proyecto-network

volumes:
  postgres-data:
  redis-data:

networks:
  proyecto-network:
    driver: bridge
```

### 3.3 Dockerfile - Backend (Laravel)

```dockerfile
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
```

### 3.4 Dockerfile - Frontend (React)

```dockerfile
# Development
FROM node:18-alpine as development

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev"]

# Production Build
FROM node:18-alpine as build

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# Production Serve
FROM nginx:alpine as production

COPY --from=build /app/dist /usr/share/nginx/html
COPY docker/nginx/frontend.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
```

---

## 4. CI/CD PIPELINE

### 4.1 GitHub Actions Workflow

```yaml
# .github/workflows/ci-cd.yml
name: CI/CD Pipeline

on:
  push:
    branches: [develop, main]
  pull_request:
    branches: [develop, main]

env:
  PHP_VERSION: '8.2'
  NODE_VERSION: '18'

jobs:
  # ============================================
  # BACKEND TESTS
  # ============================================
  backend-tests:
    name: Backend Tests & Quality
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:15-alpine
        env:
          POSTGRES_DB: testing
          POSTGRES_USER: test
          POSTGRES_PASSWORD: test
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

      redis:
        image: redis:7-alpine
        ports:
          - 6379:6379

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          extensions: pdo, pdo_pgsql, redis, zip
          coverage: xdebug

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: vendor
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install dependencies
        working-directory: ./backend
        run: composer install --prefer-dist --no-interaction

      - name: Copy .env.testing
        working-directory: ./backend
        run: cp .env.testing .env

      - name: Generate application key
        working-directory: ./backend
        run: php artisan key:generate

      - name: Run migrations
        working-directory: ./backend
        run: php artisan migrate --force

      - name: Run PHPUnit tests
        working-directory: ./backend
        run: vendor/bin/phpunit --coverage-clover coverage.xml

      - name: Run Pest tests
        working-directory: ./backend
        run: vendor/bin/pest --coverage

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./backend/coverage.xml
          flags: backend

      - name: PHP CS Fixer
        working-directory: ./backend
        run: vendor/bin/php-cs-fixer fix --dry-run --diff

      - name: PHPStan Static Analysis
        working-directory: ./backend
        run: vendor/bin/phpstan analyse

  # ============================================
  # FRONTEND TESTS
  # ============================================
  frontend-tests:
    name: Frontend Tests & Quality
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: 'npm'
          cache-dependency-path: ./frontend/package-lock.json

      - name: Install dependencies
        working-directory: ./frontend
        run: npm ci

      - name: Run ESLint
        working-directory: ./frontend
        run: npm run lint

      - name: Run TypeScript check
        working-directory: ./frontend
        run: npm run type-check

      - name: Run Jest tests
        working-directory: ./frontend
        run: npm run test:coverage

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./frontend/coverage/coverage-final.json
          flags: frontend

      - name: Build application
        working-directory: ./frontend
        run: npm run build

      - name: Upload build artifacts
        uses: actions/upload-artifact@v3
        with:
          name: frontend-build
          path: ./frontend/dist

  # ============================================
  # SECURITY SCANNING
  # ============================================
  security-scan:
    name: Security Scanning
    runs-on: ubuntu-latest
    needs: [backend-tests, frontend-tests]

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Run Snyk Security Scan
        uses: snyk/actions/php@master
        env:
          SNYK_TOKEN: ${{ secrets.SNYK_TOKEN }}
        with:
          command: test
          args: --file=backend/composer.lock

      - name: Run npm audit
        working-directory: ./frontend
        run: npm audit --audit-level=moderate

  # ============================================
  # DEPLOY TO DEVELOPMENT
  # ============================================
  deploy-development:
    name: Deploy to Development
    runs-on: ubuntu-latest
    needs: [backend-tests, frontend-tests, security-scan]
    if: github.ref == 'refs/heads/develop'
    environment:
      name: development
      url: https://dev.proyecto-contable.com

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Deploy to Railway/Render/Vercel
        run: |
          # Deployment scripts here
          echo "Deploying to development..."

      - name: Run post-deployment tests
        run: |
          # Smoke tests
          echo "Running smoke tests..."

  # ============================================
  # DEPLOY TO STAGING
  # ============================================
  deploy-staging:
    name: Deploy to Staging
    runs-on: ubuntu-latest
    needs: [backend-tests, frontend-tests, security-scan]
    if: github.ref == 'refs/heads/main'
    environment:
      name: staging
      url: https://staging.proyecto-contable.com

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Deploy to Staging
        run: |
          echo "Deploying to staging..."

      - name: Run E2E tests
        run: |
          echo "Running E2E tests..."

  # ============================================
  # DEPLOY TO PRODUCTION
  # ============================================
  deploy-production:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: deploy-staging
    if: github.ref == 'refs/heads/main'
    environment:
      name: production
      url: https://app.proyecto-contable.com

    steps:
      - name: Manual Approval Required
        uses: trstringer/manual-approval@v1
        with:
          secret: ${{ secrets.GITHUB_TOKEN }}
          approvers: tu-usuario-github

      - name: Checkout code
        uses: actions/checkout@v3

      - name: Deploy to Production
        run: |
          echo "Deploying to production..."

      - name: Notify Sentry of Release
        run: |
          curl -X POST \
            https://sentry.io/api/0/organizations/org/releases/ \
            -H "Authorization: Bearer ${{ secrets.SENTRY_TOKEN }}"
```

---

## 5. TESTING STRATEGY

### 5.1 Pirámide de Testing

```
         /\
        /  \   E2E Tests (5%)
       /────\  - Playwright/Cypress
      /      \ - Flujos críticos
     /────────\
    /          \ Integration Tests (20%)
   /────────────\ - Feature tests
  /              \ - API tests
 /────────────────\
/                  \ Unit Tests (75%)
──────────────────── - Domain logic
                     - Services
                     - Utilities
```

### 5.2 Backend Testing

**PHPUnit Configuration:**
```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory>./app/Http/Middleware</directory>
            <file>./app/Http/Kernel.php</file>
        </exclude>
    </coverage>
</phpunit>
```

**Ejemplo de Test Unitario (Domain):**
```php
// tests/Unit/Domain/Billing/InvoiceTest.php
namespace Tests\Unit\Domain\Billing;

use App\Domain\Billing\Entities\Invoice;
use App\Domain\Billing\ValueObjects\InvoiceStatus;
use App\Domain\Billing\Exceptions\InvalidInvoiceStateException;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    /** @test */
    public function it_can_be_marked_as_paid(): void
    {
        $invoice = Invoice::create(/* ... */);
        
        $invoice->markAsPaid();
        
        $this->assertTrue($invoice->status()->isPaid());
        $this->assertNotNull($invoice->paidAt());
    }
    
    /** @test */
    public function it_cannot_be_marked_as_paid_if_cancelled(): void
    {
        $invoice = Invoice::create(/* ... */);
        $invoice->cancel('Test reason');
        
        $this->expectException(InvalidInvoiceStateException::class);
        
        $invoice->markAsPaid();
    }
    
    /** @test */
    public function it_calculates_total_correctly_with_tax(): void
    {
        $invoice = Invoice::create(/* ... */);
        $invoice->addItem(/* item with price 100 and 19% tax */);
        
        $this->assertEquals(119.00, $invoice->total()->amount());
    }
}
```

**Ejemplo de Test de Integración (Application):**
```php
// tests/Feature/Billing/CreateInvoiceTest.php
namespace Tests\Feature\Billing;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateInvoiceTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_creates_invoice_via_api(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $product = Product::factory()->create(['unit_price' => 100]);
        
        $response = $this->actingAs($user)
            ->postJson('/api/invoices', [
                'client_id' => $client->id,
                'date' => now()->format('Y-m-d'),
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit_price' => 100,
                    ]
                ]
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'invoice_number',
                    'total',
                ]
            ]);
        
        $this->assertDatabaseHas('invoices', [
            'client_id' => $client->id,
            'total' => 238.00, // 200 + 19% tax
        ]);
    }
}
```

### 5.3 Frontend Testing

**Jest Configuration:**
```javascript
// jest.config.js
export default {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/src/tests/setup.ts'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/src/$1',
    '\\.(css|less|scss|sass)$': 'identity-obj-proxy',
  },
  collectCoverageFrom: [
    'src/**/*.{ts,tsx}',
    '!src/**/*.d.ts',
    '!src/tests/**',
  ],
  coverageThreshold: {
    global: {
      branches: 70,
      functions: 70,
      lines: 80,
      statements: 80,
    },
  },
};
```

**Ejemplo de Test de Componente:**
```typescript
// src/features/billing/components/InvoiceForm.test.tsx
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { InvoiceForm } from './InvoiceForm';
import { vi } from 'vitest';

describe('InvoiceForm', () => {
  it('renders all required fields', () => {
    render(<InvoiceForm onSubmit={vi.fn()} />);
    
    expect(screen.getByLabelText(/client/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/date/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /add item/i })).toBeInTheDocument();
  });
  
  it('calculates total correctly', async () => {
    render(<InvoiceForm onSubmit={vi.fn()} />);
    
    // Add item
    fireEvent.click(screen.getByRole('button', { name: /add item/i }));
    
    // Fill item details
    fireEvent.change(screen.getByLabelText(/quantity/i), {
      target: { value: '2' }
    });
    fireEvent.change(screen.getByLabelText(/price/i), {
      target: { value: '100' }
    });
    
    await waitFor(() => {
      expect(screen.getByText(/total: 238.00/i)).toBeInTheDocument();
    });
  });
  
  it('submits form with correct data', async () => {
    const handleSubmit = vi.fn();
    render(<InvoiceForm onSubmit={handleSubmit} />);
    
    // Fill form...
    
    fireEvent.click(screen.getByRole('button', { name: /save/i }));
    
    await waitFor(() => {
      expect(handleSubmit).toHaveBeenCalledWith(
        expect.objectContaining({
          client_id: expect.any(Number),
          items: expect.any(Array),
        })
      );
    });
  });
});
```

### 5.4 E2E Testing

**Playwright Configuration:**
```typescript
// playwright.config.ts
import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  use: {
    baseURL: 'http://localhost:3000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
```

**Ejemplo de Test E2E:**
```typescript
// e2e/invoice-creation.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Invoice Creation Flow', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('[name="email"]', 'test@example.com');
    await page.fill('[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
  });
  
  test('creates invoice successfully', async ({ page }) => {
    // Navigate to invoices
    await page.click('text=Invoices');
    await page.click('text=New Invoice');
    
    // Fill form
    await page.click('[data-testid="client-select"]');
    await page.click('text=Client ABC');
    
    await page.click('text=Add Item');
    await page.fill('[name="items[0].description"]', 'Consulting Services');
    await page.fill('[name="items[0].quantity"]', '10');
    await page.fill('[name="items[0].unit_price"]', '50');
    
    // Verify total calculation
    await expect(page.locator('[data-testid="invoice-total"]'))
      .toContainText('595.00');
    
    // Submit
    await page.click('button[type="submit"]');
    
    // Verify success
    await expect(page.locator('.toast-success'))
      .toContainText('Invoice created successfully');
    
    // Verify in list
    await page.click('text=Back to Invoices');
    await expect(page.locator('table')).toContainText('Client ABC');
  });
});
```

---

## 6. CODE QUALITY

### 6.1 Linting y Formatting

**PHP CS Fixer:**
```php
// .php-cs-fixer.php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/app')
    ->in(__DIR__ . '/tests')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
    ])
    ->setFinder($finder);
```

**ESLint Configuration:**
```javascript
// .eslintrc.cjs
module.exports = {
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:react/recommended',
    'plugin:react-hooks/recommended',
    'prettier',
  ],
  parser: '@typescript-eslint/parser',
  plugins: ['@typescript-eslint', 'react', 'react-hooks'],
  rules: {
    'react/react-in-jsx-scope': 'off',
    '@typescript-eslint/explicit-module-boundary-types': 'off',
    '@typescript-eslint/no-explicit-any': 'error',
    'no-console': ['warn', { allow: ['warn', 'error'] }],
  },
};
```

**Prettier Configuration:**
```json
// .prettierrc
{
  "semi": true,
  "trailingComma": "es5",
  "singleQuote": true,
  "printWidth": 100,
  "tabWidth": 2,
  "arrowParens": "always"
}
```

### 6.2 Pre-commit Hooks

**Husky + Lint-staged:**
```json
// package.json
{
  "lint-staged": {
    "*.{ts,tsx}": [
      "eslint --fix",
      "prettier --write",
      "jest --findRelatedTests"
    ],
    "*.php": [
      "php-cs-fixer fix"
    ]
  }
}
```

```bash
# .husky/pre-commit
#!/bin/sh
. "$(dirname "$0")/_/husky.sh"

npx lint-staged
```

### 6.3 Static Analysis

**PHPStan:**
```neon
# phpstan.neon
parameters:
    level: 8
    paths:
        - app
    excludePaths:
        - app/Http/Middleware
    checkMissingIterableValueType: false
```

---

## 7. MONITOREO Y LOGGING

### 7.1 Application Monitoring

**Sentry Integration:**
```php
// config/sentry.php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'environment' => env('APP_ENV'),
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.2),
];
```

```typescript
// frontend/src/main.tsx
import * as Sentry from '@sentry/react';

Sentry.init({
  dsn: import.meta.env.VITE_SENTRY_DSN,
  environment: import.meta.env.MODE,
  integrations: [
    new Sentry.BrowserTracing(),
    new Sentry.Replay(),
  ],
  tracesSampleRate: 0.1,
  replaysSessionSampleRate: 0.1,
});
```

### 7.2 Logging Strategy

**Log Levels:**
- **DEBUG**: Información detallada de debugging
- **INFO**: Eventos informativos generales
- **WARNING**: Situaciones potencialmente problemáticas
- **ERROR**: Errores que no detienen la aplicación
- **CRITICAL**: Errores graves que requieren atención inmediata

**Structured Logging:**
```php
// app/Support/Logger.php
Log::info('Invoice created', [
    'invoice_id' => $invoice->id,
    'client_id' => $invoice->client_id,
    'total' => $invoice->total,
    'user_id' => auth()->id(),
]);
```

### 7.3 Health Checks

**Endpoint de Health:**
```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'services' => [
            'database' => DB::connection()->getPdo() ? 'ok' : 'down',
            'redis' => Redis::ping() ? 'ok' : 'down',
        ],
    ]);
});
```

---

## 8. PERFORMANCE OPTIMIZATION

### 8.1 Backend Optimization

**Query Optimization:**
```php
// ❌ N+1 Problem
$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo $invoice->client->name; // N queries
}

// ✅ Eager Loading
$invoices = Invoice::with('client')->get();
foreach ($invoices as $invoice) {
    echo $invoice->client->name; // 1 query
}
```

**Caching Strategy:**
```php
// Cache frequently accessed data
$accounts = Cache::remember('accounts:company:' . $companyId, 3600, function () use ($companyId) {
    return Account::where('company_id', $companyId)
        ->where('active', true)
        ->orderBy('code')
        ->get();
});
```

**Queue Jobs:**
```php
// Async processing
dispatch(new GenerateInvoicePDF($invoice));
dispatch(new SendInvoiceEmail($invoice));
```

### 8.2 Frontend Optimization

**Code Splitting:**
```typescript
// Lazy loading routes
const InvoiceList = lazy(() => import('./features/billing/pages/InvoiceList'));
const InvoiceForm = lazy(() => import('./features/billing/pages/InvoiceForm'));
```

**Memoization:**
```typescript
const expensiveCalculation = useMemo(() => {
  return calculateInvoiceTotal(items);
}, [items]);
```

**Virtual Lists:**
```typescript
// For large lists
import { FixedSizeList } from 'react-window';

<FixedSizeList
  height={600}
  itemCount={items.length}
  itemSize={50}
>
  {Row}
</FixedSizeList>
```

---

## 9. SECURITY BEST PRACTICES

### 9.1 Backend Security

**Input Validation:**
```php
// Form Request
class CreateInvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ];
    }
}
```

**SQL Injection Prevention:**
```php
// ✅ Use Eloquent ORM or Query Builder
Invoice::where('client_id', $clientId)->get();

// ✅ Use parameter binding
DB::select('SELECT * FROM invoices WHERE client_id = ?', [$clientId]);

// ❌ Never concatenate user input
DB::select("SELECT * FROM invoices WHERE client_id = $clientId");
```

**XSS Prevention:**
```php
// Blade templates auto-escape
{{ $invoice->notes }} // Escaped

// Raw output (use with caution)
{!! $trustedHtml !!}
```

### 9.2 Frontend Security

**XSS Prevention:**
```typescript
// ✅ React auto-escapes by default
<div>{userInput}</div>

// ❌ Avoid dangerouslySetInnerHTML
<div dangerouslySetInnerHTML={{ __html: userInput }} />

// ✅ If needed, sanitize first
import DOMPurify from 'dompurify';
<div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(userInput) }} />
```

**CSRF Protection:**
```typescript
// Include CSRF token in requests
axios.defaults.headers.common['X-CSRF-TOKEN'] = document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute('content');
```

### 9.3 Authentication & Authorization

**JWT Best Practices:**
```php
// Short-lived access tokens
'ttl' => 60, // 1 hour

// Refresh tokens
'refresh_ttl' => 20160, // 2 weeks

// Token blacklisting on logout
JWTAuth::invalidate($token);
```

**Permission Checks:**
```php
// Controller
$this->authorize('create', Invoice::class);

// Middleware
Route::middleware(['permission:invoices.create'])->post('/invoices', ...);

// Blade
@can('create', App\Models\Invoice::class)
    <button>Create Invoice</button>
@endcan
```

---

## 10. DEPLOYMENT CHECKLIST

### 10.1 Pre-deployment

- [ ] All tests passing
- [ ] Code coverage meets threshold (80%)
- [ ] No linting errors
- [ ] Security scan passed
- [ ] Database migrations tested
- [ ] Environment variables configured
- [ ] Secrets secured
- [ ] Backup strategy in place

### 10.2 Deployment

- [ ] Maintenance mode enabled (if needed)
- [ ] Database backup created
- [ ] Code deployed
- [ ] Dependencies installed
- [ ] Migrations run
- [ ] Cache cleared
- [ ] Queue workers restarted
- [ ] Maintenance mode disabled

### 10.3 Post-deployment

- [ ] Smoke tests passed
- [ ] Health check endpoint responding
- [ ] Monitoring dashboards green
- [ ] No error spikes in logs
- [ ] Performance metrics normal
- [ ] Rollback plan ready

---

## 11. DISASTER RECOVERY

### 11.1 Backup Strategy

**Automated Backups:**
```bash
# Database backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/database"
DB_NAME="proyecto_contable"

pg_dump $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep last 30 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete
```

**Backup Schedule:**
- Full backup: Daily at 2:00 AM
- Incremental: Every 6 hours
- Retention: 30 days
- Off-site: Weekly to S3

### 11.2 Rollback Plan

**Version Rollback:**
```bash
# Rollback to previous version
git checkout tags/v1.2.0
./deploy.sh

# Rollback database
php artisan migrate:rollback --step=1
```

**Database Restore:**
```bash
# Restore from backup
gunzip < backup_20250121_020000.sql.gz | psql proyecto_contable
```

---

## 12. DOCUMENTATION

### 12.1 Code Documentation

**PHPDoc:**
```php
/**
 * Create a new invoice for a client.
 *
 * @param  CreateInvoiceDTO  $dto
 * @return Invoice
 * @throws InsufficientStockException
 */
public function createInvoice(CreateInvoiceDTO $dto): Invoice
{
    // ...
}
```

**JSDoc:**
```typescript
/**
 * Calculates the total of an invoice including tax.
 *
 * @param items - Array of invoice items
 * @param taxRate - Tax rate as decimal (0.19 for 19%)
 * @returns Total amount including tax
 */
function calculateTotal(items: InvoiceItem[], taxRate: number): number {
  // ...
}
```

### 12.2 API Documentation

**Scribe (Laravel):**
```bash
# Generate API documentation
php artisan scribe:generate

# Docs available at /docs
```

**OpenAPI/Swagger:**
```yaml
# swagger.yaml
paths:
  /api/invoices:
    post:
      summary: Create a new invoice
      requestBody:
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CreateInvoiceRequest'
      responses:
        '201':
          description: Invoice created successfully
```

---

## CONCLUSIÓN

Esta guía DevOps proporciona:

✅ **Automatización completa**: CI/CD end-to-end  
✅ **Calidad garantizada**: Testing exhaustivo y linting  
✅ **Despliegue seguro**: Múltiples ambientes y validaciones  
✅ **Monitoreo activo**: Visibilidad de sistema en producción  
✅ **Recuperación rápida**: Backups y rollback procedures  

**Mantra DevOps**: "Automatiza todo, testea siempre, despliega con confianza"

---

**Última actualización:** Noviembre 2025  
**Mantenido por:** [Tu Nombre]  
**Próxima revisión:** Trimestral
# Finan-Core - Sistema Contable y Administrativo

[![CI/CD](https://github.com/JordyVillamil/finan-core/workflows/CI%2FCD%20Pipeline/badge.svg)](https://github.com/JordyVillamil/finan-core/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Sistema integral de gestión contable y administrativa para pequeñas y medianas empresas en Colombia.

## 🎯 Características

- ✅ **Facturación Electrónica** - Conforme a DIAN con generación de PDF y envío automático
- ✅ **Control de Inventario** - Gestión multi-bodega con códigos de barras
- ✅ **Compras y Gastos** - Gestión de proveedores y órdenes de compra
- ✅ **Sistema de Cobranza** - Seguimiento de cuentas por cobrar con recordatorios
- ✅ **Cotizaciones** - Generación rápida y conversión a facturas
- ✅ **Contabilidad NIIF** - Plan de cuentas, asientos contables y estados financieros

## 🏗️ Arquitectura

```
Arquitectura Hexagonal + DDD
├── Domain/ (Lógica de negocio pura)
│   ├── Auth/ ✅
│   ├── Company/ 🔄
│   ├── Billing/ 🔄
│   └── Shared/
├── Application/ (Casos de uso)
├── Infrastructure/ (Adaptadores/Repositorios)
└── Presentation/ (Controllers, API)
```

## 📦 Stack Tecnológico

### Backend
| Tecnología | Versión | Descripción |
|------------|---------|-------------|
| Laravel | 12.39.0 | Framework PHP |
| PHP | 8.2 | Lenguaje backend |
| Sanctum | 4.2.1 | Autenticación API |
| Spatie Permission | 6.23.0 | Roles y permisos |
| DomPDF | 3.1.1 | Generación PDF |
| PHPUnit | 11.5.44 | Testing |

### Frontend
| Tecnología | Versión | Descripción |
|------------|---------|-------------|
| React | 19.2.0 | UI Library |
| TypeScript | 5.9.3 | Tipado estático |
| Vite | 7.2.4 | Build tool |
| Tailwind CSS | 4.1.17 | Estilos |
| Zustand | 5.0.8 | State management |
| React Query | 5.90.10 | Data fetching |
| React Hook Form | 7.66.1 | Formularios |
| Zod | 4.1.12 | Validación |
| Vitest | 4.0.13 | Testing |

### Infraestructura (Docker)
| Servicio | Imagen | Puerto |
|----------|--------|--------|
| PHP-FPM | php:8.2-fpm-alpine | 9000 |
| PostgreSQL | postgres:15-alpine | 5432 |
| Redis | redis:7-alpine | 6379 |
| Nginx | nginx:alpine | 80, 443 |
| MailHog | mailhog/mailhog | 1025, 8025 |
| Frontend | node | 3000 |

## 📋 Requisitos Previos

- Docker Desktop 4.0+
- Git 2.30+
- Node.js 22+ (para desarrollo local - requerido por Vite 7)
- PHP 8.2+ (para desarrollo local)
- Composer 2.0+

## 🚀 Inicio Rápido

### Con Docker (Recomendado)

```bash
# 1. Clonar repositorio
git clone https://github.com/JordyVillamil/finan-core.git
cd finan-core

# 2. Copiar archivo de configuración
cp backend/.env.example backend/.env

# 3. Levantar contenedores
docker-compose up -d

# 4. Instalar dependencias
docker-compose exec php composer install
docker-compose exec frontend npm install

# 5. Generar clave de aplicación
docker-compose exec php php artisan key:generate

# 6. Ejecutar migraciones y seeders
docker-compose exec php php artisan migrate --seed

# 7. Acceder a la aplicación
# Frontend: http://localhost:3000
# Backend API: http://localhost/api
# Mailhog: http://localhost:8025
```

### Sin Docker

Ver [INSTALLATION.md](docs/INSTALLATION.md) para instrucciones detalladas.

## 🔐 API de Autenticación

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| POST | `/api/auth/register` | Registrar usuario | No |
| POST | `/api/auth/login` | Iniciar sesión | No |
| POST | `/api/auth/logout` | Cerrar sesión | Sí |
| GET | `/api/auth/me` | Obtener usuario actual | Sí |
| POST | `/api/auth/forgot-password` | Solicitar reset password | No |
| POST | `/api/auth/reset-password` | Cambiar contraseña | No |
| POST | `/api/auth/verify-email` | Verificar email | No |
| POST | `/api/auth/resend-verification` | Reenviar verificación | No |

## 🏢 API de Empresas

| Método | Endpoint | Descripción | Permiso |
|--------|----------|-------------|---------|
| GET | `/api/companies` | Listar empresas | `view-companies` |
| POST | `/api/companies` | Crear empresa | `create-companies` |
| GET | `/api/companies/{id}` | Ver empresa | `view-companies` |
| PUT | `/api/companies/{id}` | Actualizar empresa (parcial) | `edit-companies` |
| DELETE | `/api/companies/{id}` | Eliminar empresa | `delete-companies` |
| POST | `/api/companies/{id}/activate` | Activar empresa | `manage-companies` |
| POST | `/api/companies/{id}/deactivate` | Desactivar empresa | `manage-companies` |

## 📚 Documentación

- [Guía de Inicio Rápido](docs/00-QUICK-START-GUIDE.md)
- [Visión del Proyecto](docs/01-PROJECT-OVERVIEW.md)
- [Requerimientos](docs/02-REQUIREMENTS-SPECIFICATION.md)
- [Arquitectura del Sistema](docs/03-SYSTEM-ARCHITECTURE.md)
- [Modelo de Base de Datos](docs/04-DATABASE-MODEL.md)
- [Metodología Ágil](docs/05-AGILE-METHODOLOGY.md)
- [DevOps y CI/CD](docs/06-DEVOPS-CI-CD.md)

## 🧪 Testing

```bash
# Backend
docker-compose exec php php artisan test
docker-compose exec php vendor/bin/pest

# Frontend
docker-compose exec frontend npm run test
docker-compose exec frontend npm run test:coverage

# E2E
npm run test:e2e
```

## 📊 Cobertura de Código

- Backend: 85%
- Frontend: 82%
- Overall: 83%

## 🛠️ Desarrollo

### Branching Strategy

- `main` - Producción
- `develop` - Desarrollo
- `feature/*` - Nuevas funcionalidades
- `fix/*` - Corrección de bugs
- `hotfix/*` - Correcciones urgentes en producción

### Commits

Seguimos [Conventional Commits](https://www.conventionalcommits.org/):

```
feat(billing): add invoice PDF generation
fix(inventory): correct stock calculation
docs(readme): update installation steps
```

### Code Style

```bash
# Backend - PHP CS Fixer
composer run format

# Frontend - Prettier
npm run format
```

## 🚢 Deployment

### Staging
Despliega automáticamente desde `develop` branch.

### Production
Despliega manualmente desde `main` branch con aprobación requerida.

Ver [DEPLOYMENT.md](docs/DEPLOYMENT.md) para más detalles.

## 📈 Progreso del Proyecto

```
Sprint 0: ████████████████████ 100% ✅ Setup e Infraestructura
Sprint 1: ████████████████████ 100% ✅ Autenticación y Usuarios
Sprint 2: ████████████████░░░░  80% 🔄 Empresas y Facturación
Sprint 3: ░░░░░░░░░░░░░░░░░░░░   0% ⏳ Inventario
Sprint 4: ░░░░░░░░░░░░░░░░░░░░   0% ⏳ Compras
Sprint 5: ░░░░░░░░░░░░░░░░░░░░   0% ⏳ Cobranza y Cotizaciones
Sprint 6: ░░░░░░░░░░░░░░░░░░░░   0% ⏳ Contabilidad
Sprint 7: ░░░░░░░░░░░░░░░░░░░░   0% ⏳ Reportes
Sprint 8: ░░░░░░░░░░░░░░░░░░░░   0% ⏳ Testing y Refinamiento

Progreso total: 35%
```

### ✅ Completado (Sprint 1)
- Sistema de autenticación completo (Laravel Sanctum)
- Registro, login, logout
- Verificación de email (MailHog)
- Reset password
- Sistema de roles y permisos (Spatie Permission)
  - 6 roles: Super Admin, Admin, Contador, Auditor, Vendedor, Usuario
  - 38 permisos granulares
- 45+ tests pasando (~85% cobertura)
- Arquitectura hexagonal + DDD implementada

### 🔄 En Progreso (Sprint 2 - Empresas)

#### ✅ Domain Layer (100%)
- **Value Objects (4):** TaxId, PhoneNumber, Address, InvoiceSettings
- **Entity:** Company con métodos de dominio
- **Exceptions (4):** CompanyNotFoundException, InvalidTaxIdException, DuplicateTaxIdException, InvalidCompanyDataException
- **Repository Interface:** CompanyRepositoryInterface con método `update()` para actualizaciones parciales
- **Tests:** 84 tests unitarios pasando

#### ✅ Infrastructure Layer (100%)
- **Migraciones:** 2 tablas (companies, company_settings)
- **Model:** CompanyModel con relaciones y scopes
- **Repository:** EloquentCompanyRepository con soporte de actualizaciones parciales
- **DI:** Bindings en DomainServiceProvider
- **Tests:** 14 tests de integración pasando

#### ✅ Application Layer (100%)
- **DTOs:** CreateCompanyDTO, UpdateCompanyDTO, CompanyResponseDTO
- **Services:** CreateCompanyService, UpdateCompanyService (refactorizado para updates parciales), ListCompaniesService, GetCompanyService, DeleteCompanyService, ActivateCompanyService, DeactivateCompanyService

#### ✅ Presentation Layer (100%)
- **Form Requests:** CreateCompanyRequest, UpdateCompanyRequest (con validación `sometimes` para updates parciales)
- **Controller:** CompanyController con CRUD completo
- **Middleware:** CheckPermission personalizado para integración Sanctum + Spatie
- **Rutas API:** 7 endpoints probados y funcionando
- **Permisos:** 5 permisos (view, create, edit, delete, manage)

#### ⏳ Pendiente (Sprint 2)
- CRUD de Clientes
- CRUD de Productos
- Facturación básica con PDF

Ver [Project Board](https://github.com/JordyVillamil/finan-core/projects/1) para detalles.

## 🤝 Contribución

Este es un proyecto de portafolio personal, pero sugerencias y feedback son bienvenidos.

1. Fork el proyecto
2. Crea tu feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'feat: add some amazing feature'`)
4. Push al branch (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver [LICENSE](LICENSE) para detalles.

## 👤 Autor

**Jordy Villamil**

- GitHub: [@JordyVillamil](https://github.com/JordyVillamil)

## 🙏 Agradecimientos

- Comunidad de Laravel
- Comunidad de React
- Todos los recursos open source utilizados

---

⭐ Si este proyecto te resulta útil, considera darle una estrella en GitHub!
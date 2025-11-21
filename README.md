# Sistema Contable y Administrativo

[![CI/CD](https://github.com/tu-usuario/proyecto-contable/workflows/CI%2FCD%20Pipeline/badge.svg)](https://github.com/tu-usuario/proyecto-contable/actions)
[![codecov](https://codecov.io/gh/tu-usuario/proyecto-contable/branch/main/graph/badge.svg)](https://codecov.io/gh/tu-usuario/proyecto-contable)
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

- **Backend**: Laravel 11 con Arquitectura Hexagonal + DDD
- **Frontend**: React 18 + TypeScript + Tailwind CSS
- **Base de Datos**: PostgreSQL 15
- **Cache/Queues**: Redis 7
- **Containerización**: Docker + Docker Compose

## 📋 Requisitos Previos

- Docker Desktop 4.0+
- Git 2.30+
- Node.js 18+ (para desarrollo local sin Docker)
- PHP 8.2+ (para desarrollo local sin Docker)
- Composer 2.0+

## 🚀 Inicio Rápido

### Con Docker (Recomendado)

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-usuario/proyecto-contable.git
cd proyecto-contable

# 2. Copiar archivo de configuración
cp backend/.env.example backend/.env

# 3. Levantar contenedores
docker-compose up -d

# 4. Instalar dependencias
docker-compose exec php composer install
docker-compose exec frontend npm install

# 5. Generar clave de aplicación
docker-compose exec php php artisan key:generate

# 6. Ejecutar migraciones
docker-compose exec php php artisan migrate --seed

# 7. Acceder a la aplicación
# Frontend: http://localhost:3000
# Backend API: http://localhost/api
# Mailhog: http://localhost:8025
```

### Sin Docker

Ver [INSTALLATION.md](docs/INSTALLATION.md) para instrucciones detalladas.

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

## 📈 Roadmap

- [x] Sprint 0: Setup e infraestructura
- [x] Sprint 1: Autenticación y usuarios
- [ ] Sprint 2: Facturación básica
- [ ] Sprint 3: Inventario
- [ ] Sprint 4: Compras
- [ ] Sprint 5: Cobranza y cotizaciones
- [ ] Sprint 6: Contabilidad
- [ ] Sprint 7: Reportes
- [ ] Sprint 8: Testing y refinamiento

Ver [Project Board](https://github.com/tu-usuario/proyecto-contable/projects/1) para detalles.

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

**Tu Nombre**

- GitHub: [@tu-usuario](https://github.com/tu-usuario)
- LinkedIn: [Tu Perfil](https://linkedin.com/in/tu-perfil)
- Portfolio: [tu-portfolio.com](https://tu-portfolio.com)

## 🙏 Agradecimientos

- Comunidad de Laravel
- Comunidad de React
- Todos los recursos open source utilizados

---

⭐ Si este proyecto te resulta útil, considera darle una estrella en GitHub!
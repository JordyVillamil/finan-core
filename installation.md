# 🚀 GUÍA DE INSTALACIÓN PASO A PASO

Esta guía te llevará desde cero hasta tener el proyecto corriendo localmente.

## ✅ PRE-REQUISITOS

Asegúrate de tener instalado:
- [x] Docker Desktop (https://www.docker.com/products/docker-desktop)
- [x] Git (https://git-scm.com/downloads)
- [x] Un editor de código (VS Code recomendado)

## 📦 PASO 1: CREAR Y CONFIGURAR REPOSITORIO (10 minutos)

```bash
# 1. Crear directorio del proyecto
mkdir proyecto-contable
cd proyecto-contable

# 2. Inicializar Git
git init
git branch -M main

# 3. Crear estructura de directorios
mkdir -p backend frontend docs docker/{nginx/conf.d,php,frontend,postgres} .github/workflows

# 4. Descargar archivos de configuración
# Copia todos los archivos que generé en /mnt/user-data/outputs a tu proyecto:
#   - README.md → raíz del proyecto
#   - .gitignore → raíz del proyecto
#   - Makefile → raíz del proyecto
#   - docker-compose.yml → raíz del proyecto
#   - docker/php/Dockerfile → docker/php/
#   - docker/php/php.ini → docker/php/
#   - docker/nginx/conf.d/default.conf → docker/nginx/conf.d/
#   - docker/frontend/Dockerfile → docker/frontend/
#   - docker/postgres/init.sql → docker/postgres/
#   - Los 7 documentos de /docs → docs/

# 5. Hacer commit inicial
git add .
git commit -m "docs: initial project setup with SDLC documentation"

# 6. Crear rama develop
git checkout -b develop
```

## 🐳 PASO 2: SETUP DE DOCKER (15 minutos)

```bash
# 1. Verificar que Docker está corriendo
docker --version
docker-compose --version

# 2. Construir las imágenes (primera vez toma ~10 minutos)
docker-compose build

# 3. Levantar los contenedores
docker-compose up -d

# 4. Verificar que todos los contenedores están corriendo
docker-compose ps

# Deberías ver 6 contenedores: nginx, php, frontend, postgres, redis, mailhog
# Todos con status "Up"
```

## 🔧 PASO 3: CONFIGURAR BACKEND (Laravel) (20 minutos)

```bash
# 1. Crear proyecto Laravel
docker-compose exec php composer create-project laravel/laravel . --prefer-dist

# 2. Copiar archivo de configuración
docker-compose exec php cp .env.example .env

# 3. Editar .env (usa el contenido de backend-env-example.txt)
# Puedes editarlo desde tu editor o usar:
nano backend/.env
# Pega el contenido de backend-env-example.txt y guarda

# 4. Generar application key
docker-compose exec php php artisan key:generate

# 5. Instalar dependencias principales
docker-compose exec php composer require laravel/sanctum
docker-compose exec php composer require spatie/laravel-permission
docker-compose exec php composer require spatie/laravel-query-builder
docker-compose exec php composer require barryvdh/laravel-dompdf

# 6. Instalar dependencias de desarrollo
docker-compose exec php composer require --dev laravel/telescope
docker-compose exec php composer require --dev pestphp/pest
docker-compose exec php composer require --dev pestphp/pest-plugin-laravel
docker-compose exec php composer require --dev phpstan/phpstan
docker-compose exec php composer require --dev friendsofphp/php-cs-fixer

# 7. Publicar configuraciones
docker-compose exec php php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker-compose exec php php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 8. Crear link de storage
docker-compose exec php php artisan storage:link

# 9. Ejecutar migraciones
docker-compose exec php php artisan migrate

# 10. Verificar que funciona
curl http://localhost/api/health
# Deberías ver una respuesta JSON
```

## ⚛️ PASO 4: CONFIGURAR FRONTEND (React) (15 minutos)

```bash
# 1. Crear proyecto Vite/React
docker-compose exec frontend npm create vite@latest . -- --template react-ts

# 2. Copiar package.json (usa el contenido de frontend-package.json)
# Edita frontend/package.json y reemplaza todo el contenido

# 3. Instalar dependencias
docker-compose exec frontend npm install

# 4. Configurar Tailwind
docker-compose exec frontend npx tailwindcss init -p

# 5. Copiar configuraciones
# Copia estos archivos a frontend/:
#   - vite.config.ts
#   - tsconfig.json
#   - tailwind.config.js

# 6. Crear archivo de estilos global
cat > frontend/src/index.css << 'EOF'
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  * {
    @apply border-border;
  }
  body {
    @apply bg-background text-foreground;
  }
}
EOF

# 7. Reiniciar contenedor de frontend
docker-compose restart frontend

# 8. Verificar que funciona
# Abre http://localhost:3000 en tu navegador
# Deberías ver la pantalla de bienvenida de Vite
```

## ✅ PASO 5: VERIFICACIÓN COMPLETA (5 minutos)

```bash
# 1. Verificar todos los servicios
echo "=== Verificando servicios ==="

# Backend
echo "Backend API:"
curl http://localhost/api/health
echo ""

# Frontend
echo "Frontend:"
curl -I http://localhost:3000
echo ""

# Base de datos
echo "PostgreSQL:"
docker-compose exec postgres psql -U proyecto_user -d proyecto_contable -c "SELECT version();"
echo ""

# Redis
echo "Redis:"
docker-compose exec redis redis-cli ping
echo ""

# Mailhog
echo "Mailhog UI: http://localhost:8025"
echo ""

# 2. Ver logs en tiempo real (Ctrl+C para salir)
docker-compose logs -f
```

## 🎯 PASO 6: COMANDOS ÚTILES (Para uso diario)

```bash
# Ver todos los comandos disponibles
make help

# Iniciar proyecto
make up

# Detener proyecto
make down

# Ver logs
make logs

# Acceder al contenedor PHP
make shell-php

# Acceder al contenedor Frontend
make shell-frontend

# Ejecutar migraciones
make db-migrate

# Ejecutar tests backend
make test-backend

# Ejecutar tests frontend
make test-frontend

# Formatear código
make format

# Limpiar todo y empezar de cero
make clean
```

## 🐛 TROUBLESHOOTING

### Problema: "Cannot connect to database"
**Solución:**
```bash
# Esperar a que PostgreSQL esté listo
docker-compose exec postgres pg_isready -U proyecto_user -d proyecto_contable

# Si falla, reiniciar el contenedor
docker-compose restart postgres
```

### Problema: "Port 80 already in use"
**Solución:**
```bash
# Detener el servicio que usa el puerto 80
# En Windows: Detener IIS o Apache
# En Mac: sudo apachectl stop
# En Linux: sudo service apache2 stop

# O cambiar el puerto en docker-compose.yml:
# nginx:
#   ports:
#     - "8080:80"  # Usar puerto 8080
```

### Problema: "npm install takes forever"
**Solución:**
```bash
# Limpiar caché de npm
docker-compose exec frontend npm cache clean --force

# Instalar de nuevo
docker-compose exec frontend npm install
```

### Problema: "Permission denied" en backend
**Solución:**
```bash
# Dar permisos correctos
docker-compose exec php chown -R www-data:www-data storage bootstrap/cache
docker-compose exec php chmod -R 775 storage bootstrap/cache
```

## 🎊 ¡FELICIDADES!

Si todo funcionó, deberías tener:
- ✅ Backend corriendo en http://localhost
- ✅ Frontend corriendo en http://localhost:3000
- ✅ Mailhog en http://localhost:8025
- ✅ Base de datos PostgreSQL funcionando
- ✅ Redis funcionando

## 📚 PRÓXIMOS PASOS

1. **Lee la documentación** en `/docs`
2. **Crea tu primer branch de feature**:
   ```bash
   git checkout -b feature/setup-authentication
   ```
3. **Empieza con Sprint 1** (ver docs/00-QUICK-START-GUIDE.md)
4. **Implementa autenticación** (ver ejemplos en la documentación)

## 💡 TIPS

- Usa `make help` para ver todos los comandos disponibles
- Revisa los logs frecuentemente: `make logs`
- Haz commits pequeños y frecuentes
- Sigue las convenciones de commits (Conventional Commits)
- Corre los tests antes de hacer commit: `make test`

## 🆘 ¿NECESITAS AYUDA?

1. Revisa la documentación en `/docs`
2. Busca en Stack Overflow
3. Lee la documentación oficial de Laravel y React
4. Revisa los logs: `make logs`

---

**¡Hora de empezar a codear! 🚀**
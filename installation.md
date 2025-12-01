# 🚀 GUÍA DE INSTALACIÓN PARA WINDOWS (PowerShell)

## ⚡ Instalación Correcta: Primero las Apps, Después Docker

---

## 📋 REQUISITOS PREVIOS

- Windows 10/11
- PowerShell 5.1+
- Permisos de Administrador (para algunas instalaciones)
- Docker Desktop
- Git

### ⚠️ NOTAS DE COMPATIBILIDAD IMPORTANTES

| Herramienta | Versión | Notas |
|-------------|---------|-------|
| PHP | 8.5 (local) / 8.2 (Docker) | PHP 8.5 local, contenedor usa 8.2-fpm-alpine |
| Node.js | 20.19+ o 22.12+ | ⚠️ Vite 7 requiere estas versiones mínimas |
| Laravel | 12.x | Última versión estable |
| Tailwind CSS | 4.x | Usa `@tailwindcss/postcss` (no el plugin tradicional) |
| Testing | PHPUnit 11.x | PHPUnit viene incluido con Laravel |

---

## 🔧 PASO 1: INSTALAR HERRAMIENTAS BASE (15 minutos)

### Opción A: Con Chocolatey (Recomendado - MÁS FÁCIL)

```powershell
# 1. Abrir PowerShell como Administrador (clic derecho → Ejecutar como administrador)

# 2. Instalar Chocolatey (si no lo tienes)
Set-ExecutionPolicy Bypass -Scope Process -Force
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))

# 3. Cerrar y volver a abrir PowerShell como Administrador

# 4. Instalar todo
choco install php composer nodejs git -y

# 5. Cerrar y abrir PowerShell NORMAL (no admin)

# 6. Verificar instalaciones
php -v
composer -V
node -v
npm -v
git --version
```

### Opción B: Instalación Manual

1. **PHP 8.5**: Descarga desde https://windows.php.net/download/
   - Descomprime en `C:\php` (ej: `C:\tools\php85`)
   - Agrega la carpeta al PATH del sistema
   - **Habilitar extensiones** en `php.ini`:
     ```ini
     extension=fileinfo
     extension=zip
     extension=pdo_pgsql
     extension=openssl
     extension=mbstring
     extension=curl
     ```
   
2. **Composer**: https://getcomposer.org/Composer-Setup.exe

3. **Node.js 20.19+ o 22+**: https://nodejs.org/
   - ⚠️ **NO usar Node.js 18** - Vite 7 requiere Node.js 20.19+ o 22.12+
   - Descargar versión LTS (22.x recomendado)

4. **Git**: https://git-scm.com/download/win

---

## 📁 PASO 2: CREAR ESTRUCTURA DEL PROYECTO (5 minutos)

```powershell
# 1. Crear carpeta principal
New-Item -ItemType Directory -Path "C:\Finan-Core" -Force
Set-Location "C:\Finan-Core"

# 2. Inicializar Git
git init
git branch -M main

# 3. Crear estructura de carpetas (SIN backend y frontend todavía)
New-Item -ItemType Directory -Force -Path "docs"
New-Item -ItemType Directory -Force -Path "docker\nginx\conf.d"
New-Item -ItemType Directory -Force -Path "docker\php"
New-Item -ItemType Directory -Force -Path "docker\frontend"
New-Item -ItemType Directory -Force -Path "docker\postgres"
New-Item -ItemType Directory -Force -Path ".github\workflows"

# 4. Copiar archivos de configuración
# IMPORTANTE: Ahora copia estos archivos desde donde los descargaste:
# - docker-compose.yml → C:\Finan-Core\
# - .gitignore → C:\Finan-Core\
# - README.md → C:\Finan-Core\
# - Makefile → C:\Finan-Core\ (opcional en Windows)
# - docker/php/Dockerfile-simple → C:\Finan-Core\docker\php\Dockerfile
# - docker/php/php.ini → C:\Finan-Core\docker\php\
# - docker/nginx/conf.d/default.conf → C:\Finan-Core\docker\nginx\conf.d\
# - docker/frontend/Dockerfile → C:\Finan-Core\docker\frontend\
# - docker/postgres/init.sql → C:\Finan-Core\docker\postgres\
# - Toda la documentación (7 archivos .md) → C:\Finan-Core\docs\
```

---

## 🔨 PASO 3: CREAR BACKEND - LARAVEL (10 minutos)

```powershell
# 1. Crear Laravel en una carpeta temporal
Set-Location "C:\Finan-Core"
composer create-project laravel/laravel backend-temp --prefer-dist

# 2. Mover contenido a backend
Move-Item -Path "backend-temp" -Destination "backend" -Force

# 3. Entrar al directorio backend
Set-Location "backend"

# 4. Instalar dependencias principales
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require spatie/laravel-query-builder
composer require barryvdh/laravel-dompdf

# 5. Instalar dependencias de desarrollo
# PHPUnit ya viene incluido con Laravel 12
composer require --dev phpstan/phpstan --prefer-source
composer require --dev friendsofphp/php-cs-fixer

# 6. Publicar configuraciones
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 7. Volver a la raíz
Set-Location "C:\Finan-Core"
```

### Configurar backend\.env

```powershell
# Abrir el archivo .env para editar
notepad backend\.env
```

**Cambia estas líneas** (busca y reemplaza):

```env
APP_NAME="Sistema Contable"
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=proyecto_contable
DB_USERNAME=proyecto_user
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@finan-core.com"

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:3000
```

Guarda y cierra el archivo.

---

## ⚛️ PASO 4: CREAR FRONTEND - REACT (10 minutos)

```powershell
# 1. Crear carpeta frontend
Set-Location "C:\Finan-Core"
New-Item -ItemType Directory -Path "frontend" -Force
Set-Location "frontend"

# 2. Crear proyecto con Vite (selecciona 'y' cuando pregunte)
npm create vite@latest . -- --template react-ts

# 3. Instalar dependencias base
npm install

# 4. Instalar dependencias principales del proyecto
npm install axios zustand react-router-dom
npm install @tanstack/react-query
npm install react-hook-form @hookform/resolvers zod
npm install date-fns clsx lucide-react
npm install @tanstack/react-table recharts
npm install react-hot-toast

# 5. Instalar Tailwind CSS v4 y herramientas
# ⚠️ IMPORTANTE: Tailwind v4 usa @tailwindcss/postcss (NO el plugin tradicional)
npm install -D tailwindcss @tailwindcss/postcss postcss autoprefixer
npm install -D @tailwindcss/forms
npm install -D @types/node

# 6. Instalar herramientas de testing
npm install -D vitest @vitest/ui jsdom
npm install -D @testing-library/react @testing-library/jest-dom
npm install -D @testing-library/user-event
npm install -D @vitest/coverage-v8

# 7. Instalar linters y formatters
npm install -D eslint @typescript-eslint/eslint-plugin
npm install -D @typescript-eslint/parser
npm install -D eslint-plugin-react-hooks eslint-plugin-react-refresh
npm install -D eslint-config-prettier prettier

# 8. Inicializar Tailwind (crear archivos de configuración)
npx tailwindcss init

# 9. Volver a la raíz
Set-Location "C:\Finan-Core"
```

### Configurar Frontend

**1. Configurar `frontend\vite.config.ts`**

```powershell
notepad frontend\vite.config.ts
```

Reemplaza el contenido con:

```typescript
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    port: 3000,
    host: '0.0.0.0',
    watch: {
      usePolling: true,
    },
  },
})
```

**2. Configurar `frontend\postcss.config.js`** (⚠️ IMPORTANTE para Tailwind v4)

```powershell
notepad frontend\postcss.config.js
```

Reemplaza el contenido con:

```javascript
export default {
  plugins: {
    '@tailwindcss/postcss': {},
  },
}
```

> ⚠️ **NOTA**: Tailwind CSS v4 usa `@tailwindcss/postcss` en lugar del plugin `tailwindcss` tradicional.

**3. Configurar `frontend\tailwind.config.js`**

```powershell
notepad frontend\tailwind.config.js
```

Reemplaza el contenido con:

```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

**4. Crear `frontend\src\index.css`**

```powershell
notepad frontend\src\index.css
```

Agrega el siguiente contenido:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

**3. Actualizar `frontend\src\main.tsx`**

```powershell
notepad frontend\src\main.tsx
```

Reemplaza todo el contenido con:

```tsx
import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.tsx'
import './index.css'

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)
```

**4. Crear `frontend\.env`**

```powershell
@"
VITE_API_URL=http://localhost/api
"@ | Out-File -FilePath "frontend\.env" -Encoding UTF8
```

---

## 🐳 PASO 5: CONFIGURAR Y LEVANTAR DOCKER (5 minutos)

```powershell
# 1. Asegurarse que estás en la raíz
Set-Location "C:\Finan-Core"

# 2. Verificar que Docker Desktop está corriendo
# Abrir Docker Desktop manualmente si no está corriendo

# 3. Construir las imágenes (toma 5-10 minutos la primera vez)
docker-compose build

# 4. Levantar los contenedores
docker-compose up -d

# 5. Verificar que todos están corriendo
docker-compose ps
```

**Deberías ver 6 contenedores con STATUS "Up":**
- proyecto-nginx
- proyecto-php
- proyecto-frontend
- proyecto-postgres
- proyecto-redis
- proyecto-mailhog

---

## ✅ PASO 6: INICIALIZAR BASE DE DATOS (2 minutos)

```powershell
# 1. Esperar 10 segundos a que PostgreSQL esté listo
Start-Sleep -Seconds 10

# 2. Ejecutar migraciones
docker-compose exec php php artisan migrate

# 3. (Opcional) Ver las tablas creadas
docker-compose exec postgres psql -U proyecto_user -d proyecto_contable -c "\dt"
```

---

## 🎉 PASO 7: VERIFICAR QUE TODO FUNCIONA

### Backend (Laravel)

```powershell
# Probar la API
Invoke-WebRequest -Uri "http://localhost" -UseBasicParsing

# O abrir en navegador
Start-Process "http://localhost"
```

**Deberías ver:** Página de bienvenida de Laravel

### Frontend (React)

```powershell
# Abrir en navegador
Start-Process "http://localhost:3000"
```

**Deberías ver:** Página de bienvenida de Vite + React

### Mailhog (Testing de Emails)

```powershell
Start-Process "http://localhost:8025"
```

### PostgreSQL

```powershell
docker-compose exec postgres psql -U proyecto_user -d proyecto_contable -c "SELECT version();"
```

**Deberías ver:** Versión de PostgreSQL 15

### Redis

```powershell
docker-compose exec redis redis-cli ping
```

**Deberías ver:** `PONG`

---

## 🎯 COMANDOS ÚTILES PARA EL DÍA A DÍA

```powershell
# Ver logs en tiempo real (Ctrl+C para salir)
docker-compose logs -f

# Ver logs solo del backend
docker-compose logs -f php

# Ver logs solo del frontend
docker-compose logs -f frontend

# Acceder al contenedor PHP (para ejecutar comandos artisan)
docker-compose exec php sh

# Ejecutar comando artisan
docker-compose exec php php artisan migrate
docker-compose exec php php artisan make:model Invoice

# Acceder al contenedor Frontend
docker-compose exec frontend sh

# Reiniciar un servicio específico
docker-compose restart php
docker-compose restart frontend

# Reiniciar todo
docker-compose restart

# Detener todo
docker-compose down

# Detener y eliminar volúmenes (¡cuidado! elimina la BD)
docker-compose down -v

# Limpiar Docker completamente
docker-compose down -v
docker system prune -af
```

---

## 📊 ESTRUCTURA FINAL DEL PROYECTO

```
C:\Finan-Core\
├── backend\                    ✅ Laravel instalado
│   ├── app\
│   │   ├── Http\
│   │   ├── Models\
│   │   └── ...
│   ├── database\
│   │   ├── migrations\
│   │   └── seeders\
│   ├── routes\
│   ├── .env                    ✅ Configurado
│   ├── composer.json           ✅
│   └── artisan
│
├── frontend\                   ✅ React + Vite 7 instalado
│   ├── src\
│   │   ├── App.tsx
│   │   ├── main.tsx
│   │   └── index.css          ✅ Con Tailwind v4
│   ├── package.json            ✅
│   ├── vite.config.ts         ✅ (port 3000, host 0.0.0.0)
│   ├── postcss.config.js      ✅ (usa @tailwindcss/postcss)
│   ├── tsconfig.json          ✅
│   ├── tailwind.config.js     ✅
│   └── .env                    ✅
│
├── docker\                     ✅ Configurado
│   ├── nginx\
│   │   └── conf.d\
│   │       └── default.conf
│   ├── php\
│   │   ├── Dockerfile
│   │   └── php.ini
│   ├── frontend\
│   │   └── Dockerfile
│   └── postgres\
│       └── init.sql
│
├── docs\                       ✅ Documentación completa
│   ├── 00-QUICK-START-GUIDE.md
│   ├── 01-PROJECT-OVERVIEW.md
│   ├── 02-REQUIREMENTS-SPECIFICATION.md
│   ├── 03-SYSTEM-ARCHITECTURE.md
│   ├── 04-DATABASE-MODEL.md
│   ├── 05-AGILE-METHODOLOGY.md
│   └── 06-DEVOPS-CI-CD.md
│
├── .github\
│   └── workflows\
│
├── docker-compose.yml          ✅
├── .gitignore                  ✅
├── README.md                   ✅
└── INSTALLATION-POWERSHELL.md  ✅ (este archivo)
```

---

## 🔧 TROUBLESHOOTING

### ❌ Error: "Port 80 is already in use"

**Causa:** IIS o Apache está usando el puerto 80

**Solución 1:** Detener IIS
```powershell
# Como Administrador
Stop-Service -Name W3SVC
```

**Solución 2:** Cambiar puerto en `docker-compose.yml`
```yaml
nginx:
  ports:
    - "8080:80"  # Usar puerto 8080
```

### ❌ Error: "Cannot connect to Docker daemon"

**Solución:**
1. Abrir Docker Desktop manualmente
2. Esperar que inicie completamente
3. Intentar de nuevo

### ❌ Error: "Access denied" al instalar con Chocolatey

**Solución:**
```powershell
# Abrir PowerShell como Administrador
# Clic derecho en PowerShell → Ejecutar como administrador
```

### ❌ Error: Permisos en carpeta storage de Laravel

**Solución:**
```powershell
docker-compose exec php chown -R www-data:www-data storage bootstrap/cache
docker-compose exec php chmod -R 775 storage bootstrap/cache
```

### ❌ Frontend no actualiza automáticamente (Hot Reload no funciona)

**Solución:** Ya está configurado en `vite.config.ts` con:
```typescript
server: {
  watch: {
    usePolling: true,
  },
}
```

Si aún no funciona, reinicia el contenedor:
```powershell
docker-compose restart frontend
```

### ❌ Error: "composer: command not found"

**Solución:**
1. Reiniciar PowerShell después de instalar Composer
2. Verificar que está en el PATH:
```powershell
$env:Path
```

### ❌ Error: Composer toma mucho tiempo

**Solución:**
```powershell
# Usar mirrors más rápidos
composer config -g repos.packagist composer https://packagist.org
```

### ℹ️ Nota: Usamos PHPUnit en lugar de Pest

**Razón:** Pest no es compatible con PHP 8.5, por lo que usamos PHPUnit que viene incluido con Laravel.

**Ejecutar tests:**
```powershell
# Localmente
php artisan test

# En contenedor Docker
docker-compose exec php php artisan test

# Con cobertura
docker-compose exec php php artisan test --coverage
```

### ❌ Error: Vite no inicia con Node.js 18

**Causa:** Vite 7 requiere Node.js 20.19+ o 22.12+

**Mensaje típico:**
```
Vite requires Node.js version 20.19+, 22.12+, or 24+
```

**Solución:**
1. Actualizar Node.js a versión 22 LTS
2. Si usas Docker, el Dockerfile ya usa `node:22-alpine`

### ❌ Error: Tailwind CSS v4 no funciona con postcss.config.js tradicional

**Causa:** Tailwind v4 cambió la forma de integrarse con PostCSS

**Solución:**
Asegúrate de que `postcss.config.js` use `@tailwindcss/postcss`:

```javascript
// ❌ INCORRECTO (Tailwind v3)
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}

// ✅ CORRECTO (Tailwind v4)
export default {
  plugins: {
    '@tailwindcss/postcss': {},
  },
}
```

### ❌ Error: php.ini causa errores en contenedor Docker

**Causa:** El archivo `php.ini` local tiene configuraciones de Windows (rutas .dll)

**Solución:**
El archivo `docker/php/php.ini` NO debe contener:
```ini
; ❌ INCORRECTO - Estas líneas causan errores en Linux
extension_dir = "C:\php\ext"
extension=php_fileinfo.dll
extension=php_zip.dll
```

Solo debe contener configuraciones generales:
```ini
; ✅ CORRECTO - Configuraciones que funcionan en el contenedor
max_execution_time = 300
memory_limit = 256M
date.timezone = America/Bogota
```

---

## 🎯 VERIFICACIÓN FINAL - CHECKLIST

Antes de continuar al desarrollo, verifica:

- [ ] ✅ Backend responde en http://localhost (Laravel 12)
- [ ] ✅ Frontend responde en http://localhost:3000 (Vite 7 + React 19)
- [ ] ✅ Mailhog UI visible en http://localhost:8025
- [ ] ✅ `docker-compose ps` muestra 6 contenedores "Up"
- [ ] ✅ PostgreSQL responde: `docker-compose exec postgres psql -U proyecto_user -d proyecto_contable -c "SELECT 1;"`
- [ ] ✅ Redis responde: `docker-compose exec redis redis-cli ping`
- [ ] ✅ Migraciones ejecutadas: `docker-compose exec php php artisan migrate`
- [ ] ✅ Archivo `backend\.env` configurado correctamente
- [ ] ✅ Archivo `frontend\.env` existe

---

## ⚠️ CONFIGURACIÓN IMPORTANTE PARA AUTENTICACIÓN

Si usas un modelo de usuario personalizado (como `UserModel` en arquitectura DDD), necesitas configurar:

### 1. config/auth.php - Provider correcto

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        // Cambiar a tu modelo personalizado:
        'model' => App\Infrastructure\Persistence\Eloquent\Models\UserModel::class,
    ],
],
```

### 2. UserModel debe extender Authenticatable

```php
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserModel extends Authenticatable  // ← NO "extends Model"
{
    use HasFactory, SoftDeletes, HasApiTokens, HasRoles;
    // ...
}
```

### 3. Configurar guard para Spatie Permission

Si usas `spatie/laravel-permission` con guard 'api':

```php
class UserModel extends Authenticatable
{
    use HasRoles;
    
    protected string $guard_name = 'api';  // ← Agregar esto
    // ...
}
```

### 4. Ejecutar seeder de roles

```powershell
docker-compose exec php php artisan db:seed --class=RolesAndPermissionsSeeder
```
- [ ] ✅ `postcss.config.js` usa `@tailwindcss/postcss` (Tailwind v4)

---

## 📦 VERSIONES DEL STACK TECNOLÓGICO

### Backend (Laravel)
| Paquete | Versión |
|---------|---------|
| PHP (Local) | 8.5 |
| PHP (Docker) | 8.2-fpm-alpine |
| Laravel | 12.x |
| Laravel Sanctum | 4.x |
| Spatie Permission | 6.x |
| Spatie Query Builder | 6.x |
| Laravel DomPDF | 3.x |
| PHPUnit | 11.x |
| PHPStan | 2.x |
| PHP-CS-Fixer | 3.x |

### Frontend (React)
| Paquete | Versión |
|---------|---------|
| Node.js (Docker) | 22-alpine |
| Vite | 7.x |
| React | 19.x |
| TypeScript | 5.9.x |
| Tailwind CSS | 4.x |
| React Router | 7.x |
| TanStack Query | 5.x |
| TanStack Table | 8.x |
| Zustand | 5.x |
| React Hook Form | 7.x |
| Zod | 4.x |
| Vitest | 4.x |

### Infraestructura (Docker)
| Servicio | Imagen |
|----------|--------|
| NGINX | nginx:alpine |
| PHP-FPM | php:8.2-fpm-alpine |
| Node.js | node:22-alpine |
| PostgreSQL | postgres:15-alpine |
| Redis | redis:7-alpine |
| Mailhog | mailhog/mailhog:latest |

---

## 🚀 SIGUIENTE PASO: PRIMER COMMIT Y DESARROLLO

```powershell
# 1. Ver estado de Git
git status

# 2. Agregar todo
git add .

# 3. Hacer commit inicial
git commit -m "chore: initial project setup with Laravel and React"

# 4. Crear rama develop
git checkout -b develop

# 5. Empezar con el desarrollo
# Lee: docs\00-QUICK-START-GUIDE.md
```

---

## 📚 PRÓXIMOS PASOS

1. **Sprint 1: Autenticación** (ver `docs\00-QUICK-START-GUIDE.md`)
   - Login de usuarios
   - Registro
   - Sistema de roles
   - Dashboard básico

2. **Configurar IDE** (VS Code)
   - Instalar extensiones recomendadas
   - Configurar debugging

3. **Setup de Testing**
   - Configurar PHPUnit
   - Configurar Vitest

---

## ⏱️ TIEMPO TOTAL ESTIMADO

- ✅ Paso 1: Instalar herramientas → 15 minutos
- ✅ Paso 2: Estructura del proyecto → 5 minutos
- ✅ Paso 3: Laravel → 10 minutos
- ✅ Paso 4: React → 10 minutos
- ✅ Paso 5: Docker → 5 minutos
- ✅ Paso 6: Base de datos → 2 minutos
- ✅ Paso 7: Verificación → 3 minutos

**TOTAL: ~50 minutos**

---

## 💡 TIPS IMPORTANTES

1. **Usa PowerShell, no CMD** - Los comandos están optimizados para PowerShell
2. **Cierra y abre PowerShell** después de instalar herramientas nuevas
3. **Docker Desktop debe estar corriendo** antes de ejecutar comandos docker-compose
4. **No uses `sudo`** en Windows - usa "Ejecutar como Administrador" cuando sea necesario
5. **Los paths usan `\`** no `/` en Windows

---

¡Listo para comenzar el desarrollo! 🎉

**¿Tuviste algún problema?** Revisa la sección de Troubleshooting o los logs:
```powershell
docker-compose logs -f
```
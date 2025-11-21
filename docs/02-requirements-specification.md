# Documento de Requerimientos del Sistema (SRS)

## 📋 Software Requirements Specification

**Proyecto:** Sistema Contable y Administrativo  
**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Estado:** Aprobado  

---

## 1. INTRODUCCIÓN

### 1.1 Propósito
Este documento especifica los requerimientos funcionales y no funcionales del Sistema Contable y Administrativo, destinado a gestionar operaciones financieras de pequeñas y medianas empresas en Colombia.

### 1.2 Alcance
El sistema cubrirá facturación electrónica, inventario, compras, cobranza, cotizaciones y contabilidad, con integración a la DIAN.

### 1.3 Definiciones y Acrónimos
- **DIAN**: Dirección de Impuestos y Aduanas Nacionales
- **NIIF**: Normas Internacionales de Información Financiera
- **API**: Application Programming Interface
- **JWT**: JSON Web Token
- **CRUD**: Create, Read, Update, Delete
- **SPA**: Single Page Application
- **PEPS**: Primero en Entrar, Primero en Salir

### 1.4 Referencias
- Normativa DIAN sobre facturación electrónica
- NIIF para PYMES
- Código de Comercio de Colombia
- Estatuto Tributario Nacional

---

## 2. DESCRIPCIÓN GENERAL

### 2.1 Perspectiva del Producto
Sistema web empresarial independiente que integra múltiples módulos contables y administrativos.

### 2.2 Funciones del Producto
1. Emisión de facturas electrónicas
2. Control de inventarios
3. Gestión de compras y gastos
4. Sistema de cobranza
5. Generación de cotizaciones
6. Contabilidad integral

### 2.3 Características de Usuarios

| Tipo de Usuario | Descripción | Nivel Técnico |
|-----------------|-------------|---------------|
| Administrador | Control total del sistema | Alto |
| Contador | Gestión contable y financiera | Medio-Alto |
| Vendedor | Facturación y cotizaciones | Medio |
| Almacenista | Control de inventario | Bajo-Medio |
| Gerente | Visualización de reportes | Medio |

### 2.4 Restricciones
- Debe cumplir con normativa DIAN
- Debe cumplir con NIIF
- Compatibilidad con navegadores modernos (últimas 2 versiones)
- Tiempo de respuesta máximo: 2 segundos
- Disponibilidad: 99.9%

---

## 3. REQUERIMIENTOS FUNCIONALES

### 3.1 MÓDULO: AUTENTICACIÓN Y USUARIOS

#### RF-001: Registro de Usuario
**Prioridad:** Alta  
**Descripción:** El sistema debe permitir el registro de nuevos usuarios.

**Criterios de Aceptación:**
- Usuario proporciona: nombre, email, contraseña
- Contraseña debe tener mínimo 8 caracteres, mayúsculas, minúsculas y números
- Email debe ser único en el sistema
- Se envía correo de verificación
- Usuario queda inactivo hasta verificar email

#### RF-002: Inicio de Sesión
**Prioridad:** Alta  
**Descripción:** Los usuarios deben poder autenticarse en el sistema.

**Criterios de Aceptación:**
- Login con email y contraseña
- Genera token JWT con expiración de 24 horas
- Máximo 5 intentos fallidos antes de bloqueo temporal (15 minutos)
- Registro de último acceso
- Opción "Recordarme" (30 días)

#### RF-003: Gestión de Roles y Permisos
**Prioridad:** Alta  
**Descripción:** Sistema de roles granulares para control de acceso.

**Roles Predefinidos:**
- Super Admin
- Administrador
- Contador
- Vendedor
- Almacenista
- Consultor (solo lectura)

**Permisos por Módulo:**
- Ver, Crear, Editar, Eliminar, Exportar

#### RF-004: Recuperación de Contraseña
**Prioridad:** Media  
**Descripción:** Usuario puede recuperar acceso mediante email.

**Criterios de Aceptación:**
- Envío de token de recuperación por email
- Token válido por 1 hora
- Link de recuperación único
- Forzar cambio de contraseña

---

### 3.2 MÓDULO: FACTURACIÓN ELECTRÓNICA

#### RF-010: Crear Factura
**Prioridad:** Alta  
**Descripción:** Creación de facturas de venta cumpliendo requisitos DIAN.

**Criterios de Aceptación:**
- Campos obligatorios: cliente, productos/servicios, valores, impuestos
- Cálculo automático de subtotales, IVA, retenciones
- Numeración automática y consecutiva
- Validación de stock disponible
- Estados: Borrador, Enviada, Pagada, Anulada, Nota Crédito
- Descuentos por línea y general
- Múltiples impuestos configurables

#### RF-011: Plantillas Personalizables
**Prioridad:** Media  
**Descripción:** Personalización visual de facturas.

**Criterios de Aceptación:**
- Logo de empresa
- Colores corporativos
- Campos adicionales opcionales
- Inclusión de imágenes/videos por URL
- Términos y condiciones personalizables
- Pie de página personalizable

#### RF-012: Generación de PDF
**Prioridad:** Alta  
**Descripción:** Generación automática de PDF de factura.

**Criterios de Aceptación:**
- Generación asíncrona
- Almacenamiento seguro
- Link de descarga temporal
- Marca de agua para borradores
- Código QR de validación
- Formato profesional y legible

#### RF-013: Envío por Email
**Prioridad:** Alta  
**Descripción:** Envío automático de factura por correo electrónico.

**Criterios de Aceptación:**
- Envío a email de cliente
- CC opcional a emails adicionales
- Asunto personalizable
- Cuerpo de email con plantilla
- Adjunto: PDF de factura
- Registro de envíos
- Reenvío manual disponible

#### RF-014: Botón de Pago en Línea
**Prioridad:** Media  
**Descripción:** Integración de pasarela de pago en factura.

**Criterios de Aceptación:**
- Link de pago embebido
- Integración con pasarelas (Wompi, Mercado Pago, etc.)
- Actualización automática de estado al pagar
- Notificación de pago recibido
- Registro de transacción

#### RF-015: Anulación de Factura
**Prioridad:** Alta  
**Descripción:** Anular facturas con justificación.

**Criterios de Aceptación:**
- Solo facturas no pagadas
- Requiere motivo de anulación
- Genera documento de anulación
- Actualiza inventario si aplica
- Registro en auditoría
- No se puede eliminar, solo anular

#### RF-016: Notas Crédito
**Prioridad:** Alta  
**Descripción:** Generación de notas crédito sobre facturas.

**Criterios de Aceptación:**
- Asociada a factura original
- Puede ser total o parcial
- Actualiza cuentas por cobrar
- Genera PDF
- Cumple requisitos DIAN

#### RF-017: Factura Recurrente
**Prioridad:** Baja  
**Descripción:** Programación de facturas automáticas.

**Criterios de Aceptación:**
- Definir frecuencia (mensual, trimestral, etc.)
- Fecha de inicio y fin
- Generación automática
- Notificación al cliente

---

### 3.3 MÓDULO: INVENTARIO

#### RF-030: Gestión de Productos
**Prioridad:** Alta  
**Descripción:** CRUD completo de productos y servicios.

**Criterios de Aceptación:**
- Información: código, nombre, descripción, precio, costo, categoría
- Imágenes múltiples
- Unidad de medida
- IVA y otros impuestos
- Productos simples y compuestos
- Servicios sin control de stock
- Estado activo/inactivo

#### RF-031: Control de Bodegas
**Prioridad:** Alta  
**Descripción:** Gestión de múltiples ubicaciones de almacenamiento.

**Criterios de Aceptación:**
- Crear/editar bodegas
- Stock por bodega
- Transferencias entre bodegas
- Bodega principal designada
- Restricciones por bodega

#### RF-032: Códigos de Barras
**Prioridad:** Media  
**Descripción:** Generación y lectura de códigos de barras.

**Criterios de Aceptación:**
- Generación automática o manual
- Formatos: EAN-13, Code-128
- Impresión de etiquetas
- Búsqueda por código de barras
- Escaneo en facturas (futuro)

#### RF-033: Movimientos de Inventario
**Prioridad:** Alta  
**Descripción:** Registro de todos los movimientos de stock.

**Criterios de Aceptación:**
- Tipos: entrada, salida, ajuste, transferencia
- Motivo del movimiento
- Usuario responsable
- Fecha y hora
- Documento asociado (factura, compra, etc.)
- Trazabilidad completa

#### RF-034: Alertas de Stock Bajo
**Prioridad:** Media  
**Descripción:** Notificaciones cuando stock llega a mínimo.

**Criterios de Aceptación:**
- Definir stock mínimo por producto
- Email/notificación al alcanzar mínimo
- Dashboard con productos en mínimo
- Sugerencia de reorden

#### RF-035: Valoración de Inventario
**Prioridad:** Alta  
**Descripción:** Cálculo de valor de inventario.

**Criterios de Aceptación:**
- Métodos: PEPS, Promedio Ponderado
- Costo por producto
- Valor total de inventario
- Reporte de valoración

---

### 3.4 MÓDULO: COMPRAS Y GASTOS

#### RF-050: Gestión de Proveedores
**Prioridad:** Alta  
**Descripción:** CRUD de proveedores.

**Criterios de Aceptación:**
- Información: NIT, nombre, contacto, email, teléfono
- Condiciones de pago
- Categoría de proveedor
- Documentos adjuntos
- Historial de compras

#### RF-051: Órdenes de Compra
**Prioridad:** Media  
**Descripción:** Creación de órdenes de compra a proveedores.

**Criterios de Aceptación:**
- Selección de proveedor y productos
- Cantidades y precios
- Fecha de entrega esperada
- Estados: Borrador, Enviada, Recibida, Cancelada
- Generación de PDF
- Envío por email

#### RF-052: Registro de Compras
**Prioridad:** Alta  
**Descripción:** Registro de compras recibidas.

**Criterios de Aceptación:**
- Asociar a orden de compra (opcional)
- Factura de proveedor
- Productos recibidos
- Actualización automática de inventario
- Cuentas por pagar
- Impuestos y retenciones

#### RF-053: Categorización de Gastos
**Prioridad:** Media  
**Descripción:** Clasificación de gastos por categoría.

**Categorías:**
- Operacionales
- Administrativos
- Ventas
- Financieros
- Otros

#### RF-054: Centro de Costos
**Prioridad:** Media  
**Descripción:** Asignación de gastos a centros de costo.

**Criterios de Aceptación:**
- Definir centros de costo
- Asignar gastos/compras
- Reportes por centro de costo

---

### 3.5 MÓDULO: COBRANZA

#### RF-070: Estado de Cartera
**Prioridad:** Alta  
**Descripción:** Visualización de cuentas por cobrar.

**Criterios de Aceptación:**
- Lista de facturas pendientes
- Filtros: cliente, rango de fechas, valor
- Saldo pendiente por cliente
- Facturas vencidas resaltadas
- Total de cartera

#### RF-071: Recordatorios Automáticos
**Prioridad:** Media  
**Descripción:** Envío automático de recordatorios de pago.

**Criterios de Aceptación:**
- Configurar días antes/después de vencimiento
- Plantillas de email personalizables
- Programación automática
- Log de recordatorios enviados
- Desactivar por cliente si es necesario

#### RF-072: Comentarios en Facturas
**Prioridad:** Baja  
**Descripción:** Agregar notas de seguimiento a facturas.

**Criterios de Aceptación:**
- Comentarios con fecha y usuario
- Visibles solo internamente
- Historial de comentarios
- Notificaciones a usuarios mencionados

#### RF-073: Pagos Parciales
**Prioridad:** Alta  
**Descripción:** Registro de pagos parciales de facturas.

**Criterios de Aceptación:**
- Múltiples pagos por factura
- Fecha y método de pago
- Saldo restante actualizado
- Recibo de pago generado
- Estado "Pago Parcial"

#### RF-074: Antigüedad de Saldos
**Prioridad:** Media  
**Descripción:** Reporte de antigüedad de cartera.

**Criterios de Aceptación:**
- Rangos: 0-30, 31-60, 61-90, 90+ días
- Por cliente
- Gráficos visuales
- Exportable

---

### 3.6 MÓDULO: COTIZACIONES

#### RF-090: Crear Cotización
**Prioridad:** Alta  
**Descripción:** Generación rápida de cotizaciones.

**Criterios de Aceptación:**
- Similar a factura pero sin afectar inventario
- Productos/servicios con precios
- Descuentos
- Vigencia de cotización
- Estados: Borrador, Enviada, Aprobada, Rechazada, Vencida
- Términos y condiciones

#### RF-091: Conversión a Factura
**Prioridad:** Alta  
**Descripción:** Convertir cotización aprobada en factura.

**Criterios de Aceptación:**
- Un clic para convertir
- Conserva información de cotización
- Actualiza estado de cotización
- Valida stock disponible
- Genera factura automáticamente

#### RF-092: Plantillas de Cotización
**Prioridad:** Media  
**Descripción:** Uso de plantillas predefinidas.

**Criterios de Aceptación:**
- Guardar cotizaciones como plantillas
- Aplicar plantilla a nueva cotización
- Paquetes predefinidos de productos/servicios

#### RF-093: Seguimiento de Cotizaciones
**Prioridad:** Baja  
**Descripción:** Rastreo de acciones sobre cotizaciones.

**Criterios de Aceptación:**
- Registro de envío
- Visualización por cliente (tracking)
- Notificación de apertura (opcional)
- Recordatorio de seguimiento

---

### 3.7 MÓDULO: CONTABILIDAD

#### RF-110: Plan de Cuentas
**Prioridad:** Alta  
**Descripción:** Gestión del plan de cuentas NIIF.

**Criterios de Aceptación:**
- Estructura jerárquica (hasta 6 niveles)
- Cuentas de: Activo, Pasivo, Patrimonio, Ingresos, Gastos, Costos
- Importar plan NIIF estándar
- Personalización de cuentas
- Activar/desactivar cuentas
- Validación de uso antes de eliminar

#### RF-111: Asientos Contables
**Prioridad:** Alta  
**Descripción:** Registro de transacciones contables.

**Criterios de Aceptación:**
- Débitos y créditos deben cuadrar
- Fecha de asiento
- Descripción/concepto
- Documento soporte
- Generación automática desde facturas/compras
- Asientos manuales
- Estados: Borrador, Contabilizado, Anulado

#### RF-112: Balance General
**Prioridad:** Alta  
**Descripción:** Generación de balance general.

**Criterios de Aceptación:**
- Fecha de corte
- Comparativo con período anterior
- Activos = Pasivos + Patrimonio
- Exportable a PDF y Excel
- Niveles de detalle configurables

#### RF-113: Estado de Resultados
**Prioridad:** Alta  
**Descripción:** Reporte de pérdidas y ganancias.

**Criterios de Aceptación:**
- Rango de fechas
- Ingresos - Gastos = Utilidad/Pérdida
- Comparativo con mismo período año anterior
- Márgenes calculados
- Gráficos visuales

#### RF-114: Flujo de Efectivo
**Prioridad:** Media  
**Descripción:** Reporte de movimiento de efectivo.

**Criterios de Aceptación:**
- Actividades: operación, inversión, financiación
- Efectivo inicial y final
- Método directo o indirecto
- Proyección futura (opcional)

#### RF-115: Conciliación Bancaria
**Prioridad:** Media  
**Descripción:** Cuadre de extractos bancarios con contabilidad.

**Criterios de Aceptación:**
- Importar extracto bancario (CSV)
- Relacionar movimientos con asientos
- Identificar diferencias
- Generar ajustes necesarios
- Reporte de conciliación

#### RF-116: Cierre Contable
**Prioridad:** Media  
**Descripción:** Proceso de cierre de período contable.

**Criterios de Aceptación:**
- Verificación de cuadre
- Bloqueo de período cerrado
- Traslado de utilidad a patrimonio
- Generación de asientos de cierre
- No permitir modificaciones en períodos cerrados

---

### 3.8 MÓDULO: DASHBOARD Y REPORTES

#### RF-130: Dashboard Ejecutivo
**Prioridad:** Alta  
**Descripción:** Panel principal con KPIs.

**KPIs a Mostrar:**
- Ventas del mes (vs. mes anterior)
- Facturas pendientes de cobro
- Inventario bajo stock
- Compras del mes
- Gráfico de ventas (últimos 6 meses)
- Top 5 productos más vendidos
- Top 5 clientes
- Estado de cartera (vencida/vigente)

#### RF-131: Reportes Predefinidos
**Prioridad:** Media  
**Descripción:** Conjunto de reportes estándar.

**Reportes:**
- Ventas por período
- Ventas por producto
- Ventas por cliente
- Compras por período
- Compras por proveedor
- Movimientos de inventario
- Kardex por producto
- Libro diario
- Libro mayor
- Balance de prueba
- Indicadores financieros

#### RF-132: Exportación de Reportes
**Prioridad:** Media  
**Descripción:** Exportar reportes a diferentes formatos.

**Formatos:**
- PDF
- Excel (.xlsx)
- CSV

---

### 3.9 MÓDULO: CONFIGURACIÓN

#### RF-150: Configuración de Empresa
**Prioridad:** Alta  
**Descripción:** Datos de la empresa.

**Información:**
- Razón social
- NIT
- Dirección, ciudad, país
- Teléfono, email
- Logo
- Moneda principal
- Año fiscal

#### RF-151: Configuración de Impuestos
**Prioridad:** Alta  
**Descripción:** Gestión de impuestos.

**Criterios de Aceptación:**
- IVA con diferentes tarifas (0%, 5%, 19%)
- Retenciones (renta, IVA, ICA)
- Impuesto al consumo
- Activar/desactivar impuestos
- Predeterminados por producto

#### RF-152: Numeración y Consecutivos
**Prioridad:** Alta  
**Descripción:** Configuración de consecutivos de documentos.

**Criterios de Aceptación:**
- Prefijos personalizables
- Número inicial
- Formato (FV-00001)
- Separado por tipo de documento
- Resolución DIAN asociada

#### RF-153: Plantillas de Email
**Prioridad:** Media  
**Descripción:** Personalización de emails automáticos.

**Plantillas:**
- Factura enviada
- Recordatorio de pago
- Cotización enviada
- Bienvenida
- Recuperación de contraseña

#### RF-154: Logs de Auditoría
**Prioridad:** Media  
**Descripción:** Registro de acciones importantes.

**Criterios de Aceptación:**
- Usuario, acción, fecha/hora
- IP de origen
- Cambios realizados (antes/después)
- Búsqueda y filtros
- Retención de 1 año mínimo

---

## 4. REQUERIMIENTOS NO FUNCIONALES

### 4.1 RENDIMIENTO

#### RNF-001: Tiempo de Respuesta
- Páginas deben cargar en menos de 2 segundos
- APIs deben responder en menos de 500ms
- Búsquedas en menos de 1 segundo

#### RNF-002: Capacidad
- Soportar mínimo 1000 usuarios concurrentes
- 10,000 facturas al mes
- Base de datos con capacidad de 5 años de datos históricos

#### RNF-003: Escalabilidad
- Arquitectura debe permitir escalamiento horizontal
- Base de datos debe soportar particionamiento
- Cache implementado para queries frecuentes

### 4.2 SEGURIDAD

#### RNF-010: Autenticación
- JWT con expiración
- Renovación de tokens
- Cierre de sesión en todos los dispositivos

#### RNF-011: Autorización
- Control de acceso basado en roles (RBAC)
- Permisos granulares
- Validación en backend de cada operación

#### RNF-012: Encriptación
- Contraseñas con bcrypt (factor 12+)
- Datos sensibles encriptados en base de datos
- Comunicación solo por HTTPS/TLS 1.3
- Encriptación de PDFs sensibles

#### RNF-013: Protección contra Ataques
- Rate limiting en APIs (100 req/min por usuario)
- Protección SQL Injection
- Validación y sanitización de inputs
- CSRF tokens
- XSS prevention
- Headers de seguridad (HSTS, CSP, etc.)

#### RNF-014: Privacidad de Datos
- Cumplimiento con Ley 1581 de Colombia (Habeas Data)
- No compartir datos con terceros
- Opción de exportar datos del usuario
- Opción de eliminar cuenta y datos

### 4.3 DISPONIBILIDAD Y CONFIABILIDAD

#### RNF-020: Disponibilidad
- 99.9% de uptime mensual
- Máximo 43 minutos de downtime al mes
- Mantenimientos programados fuera de horario laboral

#### RNF-021: Recuperación ante Desastres
- Backups automáticos diarios
- Retención de backups: 30 días
- Tiempo de recuperación (RTO): 4 horas
- Punto de recuperación (RPO): 24 horas

#### RNF-022: Tolerancia a Fallos
- Validación de datos críticos
- Manejo de errores graceful
- Logs de errores para debugging
- Notificaciones de fallos críticos

### 4.4 USABILIDAD

#### RNF-030: Interfaz de Usuario
- Diseño responsivo (móvil, tablet, desktop)
- Navegación intuitiva
- Máximo 3 clics para acciones comunes
- Mensajes de error claros y accionables

#### RNF-031: Accesibilidad
- Cumplir WCAG 2.1 nivel AA
- Navegación por teclado
- Soporte para lectores de pantalla
- Contraste adecuado de colores

#### RNF-032: Internacionalización
- Soporte para español
- Formato de fecha: DD/MM/YYYY
- Moneda: COP (Pesos colombianos)
- Separador decimal: coma (,)
- Separador miles: punto (.)

### 4.5 MANTENIBILIDAD

#### RNF-040: Código
- Seguir PSR-12 (PHP) y Airbnb (JavaScript)
- Cobertura de pruebas mínima: 80%
- Documentación inline (PHPDoc, JSDoc)
- Código reviewed antes de merge

#### RNF-041: Documentación
- README completo en repositorio
- Documentación de API actualizada
- Guías de despliegue
- Manual de usuario

#### RNF-042: Monitoreo
- Logs centralizados
- Métricas de aplicación
- Alertas configuradas
- Dashboard de monitoreo

### 4.6 COMPATIBILIDAD

#### RNF-050: Navegadores
- Chrome (últimas 2 versiones)
- Firefox (últimas 2 versiones)
- Safari (últimas 2 versiones)
- Edge (últimas 2 versiones)

#### RNF-051: Dispositivos
- Desktop (1920x1080 y superiores)
- Tablet (768x1024)
- Mobile (375x667 mínimo)

#### RNF-052: Integraciones
- API REST estándar
- Webhooks para eventos
- Documentación OpenAPI/Swagger

### 4.7 LEGAL Y CUMPLIMIENTO

#### RNF-060: Cumplimiento Tributario
- 100% conforme con requisitos DIAN
- Facturación electrónica según normativa vigente
- Reportes tributarios válidos

#### RNF-061: Contabilidad
- Plan de cuentas según NIIF para PYMES
- Estados financieros conformes
- Trazabilidad de transacciones

#### RNF-062: Protección de Datos
- Cumplimiento Ley 1581 de 2012 (Colombia)
- Política de privacidad publicada
- Consentimiento explícito para uso de datos

---

## 5. RESTRICCIONES

### 5.1 Restricciones Técnicas
- Backend debe ser Laravel 11+
- Frontend debe ser React 18+
- Base de datos PostgreSQL 15+
- Hosting compatible con PHP 8.2+

### 5.2 Restricciones de Tiempo
- Proyecto debe completarse en 20 semanas máximo
- Sprints de 2 semanas

### 5.3 Restricciones de Presupuesto
- Uso prioritario de herramientas gratuitas
- Hosting en plan gratuito/económico
- Sin costos de licencias

---

## 6. CASOS DE USO PRINCIPALES

### CU-001: Facturar Cliente
**Actor:** Vendedor  
**Flujo Principal:**
1. Usuario ingresa a módulo de facturación
2. Selecciona "Nueva Factura"
3. Busca y selecciona cliente
4. Agrega productos/servicios
5. Sistema calcula totales e impuestos
6. Usuario confirma y envía factura
7. Sistema genera PDF y envía email
8. Sistema actualiza inventario y contabilidad

### CU-002: Recibir Compra
**Actor:** Almacenista  
**Flujo Principal:**
1. Usuario ingresa a módulo de compras
2. Selecciona "Nueva Compra"
3. Selecciona proveedor
4. Agrega productos recibidos
5. Ingresa factura del proveedor
6. Sistema actualiza inventario
7. Sistema registra cuenta por pagar

### CU-003: Generar Balance General
**Actor:** Contador  
**Flujo Principal:**
1. Usuario ingresa a módulo de contabilidad
2. Selecciona "Balance General"
3. Indica fecha de corte
4. Sistema genera reporte
5. Usuario exporta a PDF

---

## 7. TRAZABILIDAD

| Requerimiento | Prioridad | Módulo | Sprint |
|---------------|-----------|--------|--------|
| RF-001 a RF-004 | Alta | Autenticación | 2 |
| RF-010 a RF-017 | Alta | Facturación | 3 |
| RF-030 a RF-035 | Alta | Inventario | 4 |
| RF-050 a RF-054 | Alta | Compras | 5 |
| RF-070 a RF-074 | Media | Cobranza | 6 |
| RF-090 a RF-093 | Alta | Cotizaciones | 6 |
| RF-110 a RF-116 | Alta | Contabilidad | 7 |
| RF-130 a RF-132 | Media | Reportes | 8 |
| RF-150 a RF-154 | Media | Configuración | 2 |

---

## 8. APROBACIONES

| Rol | Nombre | Firma | Fecha |
|-----|--------|-------|-------|
| Product Owner | [Tu Nombre] | _________ | ______ |
| Tech Lead | [Tu Nombre] | _________ | ______ |

---

**Última revisión:** Noviembre 2025  
**Próxima revisión:** Cada sprint  
**Estado:** Aprobado
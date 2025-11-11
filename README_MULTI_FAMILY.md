# Secret Santa - Sistema Multi-Familia

## 🎉 ¡Implementación Completada!

El sistema Secret Santa ha sido exitosamente actualizado para soportar múltiples familias independientes usando una sola instalación.

---

## ✨ Características Implementadas

### 🏠 Múltiples Familias Independientes
- ✅ Una instalación, infinitas familias
- ✅ Cada familia con sus propios usuarios
- ✅ Cada familia con su propio sorteo
- ✅ Cada familia con sus propias fechas
- ✅ Separación total de datos

### 🔗 Sistema de URLs Inteligente
- ✅ Registro: `dominio.com/registro?fam=garcia`
- ✅ Login: `dominio.com` (sin parámetro)
- ✅ Dashboard: `dominio.com` (filtrado automático)

### 🛡️ Protecciones Implementadas
- ✅ Familia original completamente protegida
- ✅ Bloqueo automático de registro post-sorteo
- ✅ Validaciones de fechas lógicas
- ✅ Slug único y formato validado
- ✅ No afecta relaciones `family_id` existentes

### 📅 Gestión de Fechas por Familia
- ✅ Cada familia con fechas independientes
- ✅ Familia original con fechas congeladas del `.env`
- ✅ Admin configura fechas desde panel web
- ✅ Validación automática de orden lógico

---

## 📁 Archivos Creados/Modificados

### ✨ Nuevos Archivos

**Migraciones:**
- [`database/migrations/2025_11_11_150948_create_family_groups_table.php`](database/migrations/2025_11_11_150948_create_family_groups_table.php:1)
- [`database/migrations/2025_11_11_151016_add_family_group_id_to_users_table.php`](database/migrations/2025_11_11_151016_add_family_group_id_to_users_table.php:1)
- [`database/migrations/2025_11_11_151042_add_family_group_id_to_secret_santa_assignments_table.php`](database/migrations/2025_11_11_151042_add_family_group_id_to_secret_santa_assignments_table.php:1)

**Modelos:**
- [`app/Models/FamilyGroup.php`](app/Models/FamilyGroup.php:1) - Modelo principal con métodos de validación

**Middleware:**
- [`app/Http/Middleware/CaptureFamilyGroup.php`](app/Http/Middleware/CaptureFamilyGroup.php:1) - Captura `?fam=` y bloquea si hay sorteo

**Controladores:**
- [`app/Http/Controllers/Admin/FamilyGroupController.php`](app/Http/Controllers/Admin/FamilyGroupController.php:1) - CRUD completo de familias

**Vistas Admin:**
- [`resources/views/admin/family-groups/index.blade.php`](resources/views/admin/family-groups/index.blade.php:1) - Lista de familias
- [`resources/views/admin/family-groups/create.blade.php`](resources/views/admin/family-groups/create.blade.php:1) - Crear familia
- [`resources/views/admin/family-groups/show.blade.php`](resources/views/admin/family-groups/show.blade.php:1) - Detalles y enlace
- [`resources/views/admin/family-groups/edit.blade.php`](resources/views/admin/family-groups/edit.blade.php:1) - Editar familia

**Documentación:**
- [`ARCHITECTURE_MULTI_FAMILY.md`](ARCHITECTURE_MULTI_FAMILY.md:1) - Arquitectura técnica
- [`DATES_MANAGEMENT.md`](DATES_MANAGEMENT.md:1) - Gestión de fechas
- [`ADMIN_GUIDE.md`](ADMIN_GUIDE.md:1) - Guía paso a paso para admin

### 🔧 Archivos Modificados

**Modelos:**
- [`app/Models/User.php`](app/Models/User.php:1) - Agregada relación `familyGroup()`
- [`app/Models/SecretSantaAssignment.php`](app/Models/SecretSantaAssignment.php:1) - Agregada relación `familyGroup()`

**Controladores:**
- [`app/Http/Controllers/Admin/DrawController.php`](app/Http/Controllers/Admin/DrawController.php:1) - Selector de familia y filtros
- [`app/Http/Controllers/Admin/UserController.php`](app/Http/Controllers/Admin/UserController.php:1) - Filtro por familia
- [`app/Http/Controllers/UserController.php`](app/Http/Controllers/UserController.php:1) - Usa fechas de familia

**Fortify:**
- [`app/Actions/Fortify/CreateNewUser.php`](app/Actions/Fortify/CreateNewUser.php:1) - Asigna `family_group_id`
- [`app/Providers/FortifyServiceProvider.php`](app/Providers/FortifyServiceProvider.php:1) - Registra vista

**Configuración:**
- [`bootstrap/app.php`](bootstrap/app.php:1) - Registra middleware
- [`routes/web.php`](routes/web.php:1) - Rutas de family-groups

**Vistas:**
- [`resources/views/admin/draw.blade.php`](resources/views/admin/draw.blade.php:1) - Selector de familia
- [`resources/views/admin/users/index.blade.php`](resources/views/admin/users/index.blade.php:1) - Filtro y badge de familia
- [`resources/views/navigation-menu.blade.php`](resources/views/navigation-menu.blade.php:1) - Enlace a Familias

---

## 🚀 Cómo Empezar

### Paso 1: La Base de Datos ya está Lista
Las migraciones ya fueron ejecutadas y tu familia original está protegida con ID=1 y slug='default'.

### Paso 2: Crear tu Primera Nueva Familia

1. Inicia sesión como admin
2. Click en **"Familias"** en el menú
3. Click **"+ Nueva Familia"**
4. Completa:
   - Nombre: "Familia García"
   - Slug: "garcia"
   - Fecha Sorteo: Ej: 15/12/2025 18:00
   - Fecha Revelación: Ej: 24/12/2025 20:00
   - Fecha Límite Edición: Ej: 31/12/2025 23:59
5. Click **"Crear Familia"**

### Paso 3: Compartir Enlace

1. En la lista de familias, click **"Ver"** en "Familia García"
2. Click botón **"📋 Copiar"** en el enlace de registro
3. Comparte por WhatsApp: `dominio.com/registro?fam=garcia`

### Paso 4: Esperar Registros

Los usuarios acceden al enlace y se registran automáticamente en la familia García.

### Paso 5: Realizar Sorteo

1. Cuando llegue la fecha, ve a **"Start Draw"**
2. Selecciona **"Familia García"** del dropdown
3. Click **"🎁 Iniciar Sorteo para Familia García"**
4. ¡Listo! El registro se cierra automáticamente

---

## 📊 Estructura de Base de Datos

### Tabla: family_groups

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | ID único |
| slug | VARCHAR(255) | Identificador en URL |
| name | VARCHAR(255) | Nombre de la familia |
| description | TEXT | Descripción opcional |
| is_active | BOOLEAN | Familia activa |
| enable_draw_at | DATETIME | Cuándo habilitar sorteo |
| reveal_date | DATETIME | Cuándo revelar amigo secreto |
| profile_edit_end_date | DATETIME | Hasta cuándo editar perfil |
| created_at | TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | Última actualización |

### Modificaciones a Tablas Existentes

**users:**
- Agregado: `family_group_id` (nullable, foreign key)
- **NO modificado:** `family_id` (sigue intacto)

**secret_santa_assignments:**
- Agregado: `family_group_id` (nullable, foreign key)

---

## 🔍 Diferencias Clave

### family_id vs family_group_id

```php
// family_id (EXISTENTE - NO TOCADO)
// Maneja grupos familiares INTERNOS dentro de una familia
// Ej: Juan y María son hermanos en FAMILIA GARCÍA
$user->family_id = 5;  // ID de Juan (padre de familia interna)
$user->getAllFamilyMembers(); // María, Pedro (hermanos)

// family_group_id (NUEVO)
// Separa las FAMILIAS GRANDES entre sí
// Ej: Familia García vs Familia Rodríguez
$user->family_group_id = 2;  // Familia García  
$user->familyGroup->name;  // "Familia García"
```

**Ambos coexisten sin conflicto.**

---

## 🎯 URLs y Flujos

### Flujo de Registro

```
1. Admin crea familia "garcia" en panel
2. Admin obtiene: dominio.com/registro?fam=garcia
3. Admin comparte enlace
4. Usuario abre enlace
5. Middleware captura ?fam=garcia
6. Middleware verifica: ¿Ya sorteada? NO → Permite
7. Usuario completa registro
8. Sistema guarda: user.family_group_id = 2
```

### Flujo de Login (sin parámetro)

```
1. Usuario abre: dominio.com
2. Usuario ingresa DNI + contraseña
3. Sistema autentica
4. Sistema lee: user.family_group_id = 2 (de BD)
5. Sistema muestra datos filtrados de familia García
```

### Flujo de Sorteo

```
1. Admin va a: /admin/draw
2. Admin selecciona: "Familia García" (dropdown)
3. Sistema filtra: Solo usuarios con family_group_id = 2
4. Sistema usa fechas: De family_group ID=2
5. Admin sortea
6. Sistema guarda: assignments con family_group_id = 2
7. Sistema bloquea: Registro para familia García
```

---

## 🛡️ Protecciones Implementadas

### 1. Familia Original Intacta

```sql
-- Tu familia actual está en:
SELECT * FROM family_groups WHERE id = 1;

-- Resultado:
id: 1
slug: 'default'
name: 'Familia Original'
enable_draw_at: '2025-10-25 10:00:00' (del .env)
reveal_date: '2025-10-25 12:00:00' (del .env)
profile_edit_end_date: '2025-11-30 23:59:59' (del .env)
```

**Protecciones:**
- ❌ No se puede editar
- ❌ No se puede eliminar
- ❌ No se puede cambiar fechas
- ✅ Sorteo existente intacto

### 2. Bloqueo de Registro Post-Sorteo

```php
// Si familia ya tiene sorteo
if ($familyGroup->hasDrawn()) {
    // Redirigir con mensaje de error
    return redirect()->route('login')
        ->with('error', 'El registro ha sido cerrado');
}
```

### 3. Validación de Fechas

```php
// Al crear familia
'reveal_date' => 'required|date|after:enable_draw_at'
'profile_edit_end_date' => 'required|date|after:reveal_date'
```

---

## 📱 Panel de Administración

### Secciones Actualizadas

**1. Familias** (NUEVO)
- `/admin/family-groups` - Gestión completa
- Crear, ver, editar, eliminar familias
- Copiar enlaces de registro
- Ver estadísticas por familia

**2. Users** (ACTUALIZADO)
- `/admin/users` - Lista con filtro por familia
- Badge muestra a qué familia pertenece
- Filtro dropdown para ver familias específicas

**3. Start Draw** (ACTUALIZADO)
- `/admin/draw` - Selector de familia
- Sorteo independiente por familia
- Usa fechas de familia seleccionada

---

## 🔧 Configuración Técnica

### Middleware Registrado

```php
// bootstrap/app.php
'capture.family.group' => \App\Http\Middleware\CaptureFamilyGroup::class
```

### Rutas Agregadas

```php
// routes/web.php
Route::resource('admin/family-groups', FamilyGroupController::class)
    ->middleware('is_admin')
    ->names('admin.family-groups');
```

### Relaciones Eloquent

```php
// FamilyGroup
public function users()
public function assignments()

// User
public function familyGroup()

// SecretSantaAssignment
public function familyGroup()
```

---

## 📖 Documentación Disponible

1. **[`ARCHITECTURE_MULTI_FAMILY.md`](ARCHITECTURE_MULTI_FAMILY.md:1)**
   - Arquitectura técnica completa
   - Diagramas de flujo
   - Estructura de base de datos
   - Código de ejemplo

2. **[`DATES_MANAGEMENT.md`](DATES_MANAGEMENT.md:1)**
   - Gestión detallada de fechas
   - Migración desde `.env`
   - Ejemplos de configuración
   - Timeline por familia

3. **[`ADMIN_GUIDE.md`](ADMIN_GUIDE.md:1)**
   - Guía paso a paso para administrador
   - Casos de uso completos
   - Solución de problemas
   - Mejores prácticas

---

## ⚡ Inicio Rápido

### Para el Admin:

```bash
# 1. Las migraciones ya están ejecutadas ✓

# 2. Accede al panel admin
URL: dominio.com/admin/family-groups

# 3. Crea una nueva familia
Click: + Nueva Familia
Completa: Nombre, Slug, Fechas

# 4. Comparte el enlace generado
Copia: dominio.com/registro?fam=TU_SLUG

# 5. Espera registros y sortea
Cuando llegue la fecha: Start Draw → Selecciona familia → Sortea
```

### Para los Usuarios:

```bash
# 1. Reciben enlace de registro
Ej: dominio.com/registro?fam=garcia

# 2. Se registran (una sola vez)
Completan formulario

# 3. Ingresan normalmente (sin parámetro)
URL: dominio.com
Login: DNI + contraseña

# 4. Ven su amigo secreto en la fecha configurada
Sistema muestra automáticamente cuando llegue la hora
```

---

## 🎯 Casos de Uso

### Caso 1: Admin con 3 Familias Diferentes

```
Familia Original (default):
- 15 usuarios
- Sorteo: Ya realizado
- Estado: 🔒 Cerrado

Familia García:
- 20 usuarios
- Sorteo: 15/12/2025
- Estado: ✓ Abierto

Familia Rodríguez:
- 12 usuarios
- Sorteo: 20/12/2025
- Estado: ✓ Abierto
```

**Resultado:** 3 sorteos independientes, 0 conflictos

---

## ⚠️ Importante

### ✅ LO QUE ESTÁ PROTEGIDO:
- Tu sorteo original y todas las asignaciones
- Las relaciones `family_id` existentes
- Los datos de usuarios actuales
- Las fechas de la familia original

### ❌ LO QUE NO SE DEBE HACER:
- Modificar familia "default" (está protegida)
- Cambiar `family_group_id` manualmente en BD
- Eliminar tabla `family_groups`
- Modificar fechas post-sorteo

### 🔒 SEGURIDAD:
- Solo admin puede gestionar familias
- Registro bloqueado automáticamente post-sorteo
- Validaciones en todas las entradas
- Transacciones DB para consistencia

---

## 🧪 Testing Sugerido

### Test 1: Crear Nueva Familia

```
1. Login como admin
2. Familias → + Nueva Familia
3. Crear: "Familia Test" / "test"
4. Verificar: Enlace generado correctamente
5. Resultado esperado: Familia creada, enlace copiable
```

### Test 2: Registro con ?fam=

```
1. Copiar enlace: dominio.com/registro?fam=test
2. Abrir en ventana incógnito
3. Completar registro
4. Verificar: Usuario creado con family_group_id correcto
5. Login normal en: dominio.com (sin parámetro)
6. Resultado esperado: Usuario ve su familia correcta
```

### Test 3: Bloqueo Post-Sorteo

```
1. Sortear familia "test"
2. Intentar registrar nuevo usuario con mismo enlace
3. Resultado esperado: Bloqueado con mensaje "Registro cerrado"
```

### Test 4: Familia Original Protegida

```
1. Intentar editar familia "default"
2. Resultado esperado: Mensaje de error, edición bloqueada
```

---

## 📊 Estadísticas del Sistema

**Archivos Creados:** 11
**Archivos Modificados:** 10
**Migraciones:** 3 (ejecutadas exitosamente)
**Vistas Admin:** 4 nuevas
**Controladores:** 1 nuevo, 3 modificados
**Modelos:** 1 nuevo, 2 modificados
**Middleware:** 1 nuevo

---

## 🎓 Conceptos Técnicos

### Separación de Responsabilidades

```
family_id (Existente):
├── Maneja grupos familiares INTERNOS
├── Ej: Hermanos, padres, hijos
└── NO se modifica

family_group_id (Nuevo):
├── Separa FAMILIAS GRANDES
├── Ej: García vs Rodríguez vs Del Trabajo
└── Cada una con sorteo independiente
```

### Flujo de Sesión

```
Registro:
URL → Middleware → Sesión → CreateNewUser → BD

Login:
Credenciales → Auth → BD → Automático
```

---

## 💡 Ventajas del Sistema

1. **Escalabilidad** - Agregar familias sin límite
2. **Simplicidad** - Usuarios solo necesitan un enlace
3. **Automatización** - Bloqueo y filtrado automático
4. **Centralización** - Todo desde un solo panel
5. **Protección** - Datos existentes intactos
6. **Flexibilidad** - Fechas independientes por familia

---

## 📞 Soporte y Ayuda

**Documentación Principal:**
- Lee [`ADMIN_GUIDE.md`](ADMIN_GUIDE.md:1) para uso diario
- Consulta [`ARCHITECTURE_MULTI_FAMILY.md`](ARCHITECTURE_MULTI_FAMILY.md:1) para detalles técnicos
- Revisa [`DATES_MANAGEMENT.md`](DATES_MANAGEMENT.md:1) para gestión de fechas

**Problemas Comunes:**
- Ver sección "Solución de Problemas" en [`ADMIN_GUIDE.md`](ADMIN_GUIDE.md:1)
- Verificar logs de Laravel en `storage/logs`

---

## ✅ Checklist de Verificación

- [x] Migraciones ejecutadas
- [x] Familia default creada con datos del .env
- [x] Usuarios existentes asignados a familia default
- [x] Asignaciones existentes vinculadas a familia default
- [x] Middleware registrado y funcional
- [x] Rutas configuradas
- [x] Vistas creadas
- [x] Controladores implementados
- [x] Validaciones activas
- [x] Protecciones implementadas
- [x] Documentación completa

---

**Sistema:** Secret Santa Multi-Familia
**Versión:** 2.0
**Estado:** ✅ Completamente Implementado
**Fecha:** 11/11/2025
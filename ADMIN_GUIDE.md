# Guía de Administrador - Sistema Multi-Familia Secret Santa

## 🎯 Bienvenido

Este sistema ahora soporta múltiples familias independientes, cada una con su propio sorteo, fechas y participantes. Esta guía explica cómo administrar el sistema.

---

## 📋 Índice

1. [Conceptos Clave](#conceptos-clave)
2. [Gestión de Familias](#gestión-de-familias)
3. [Proceso de Registro](#proceso-de-registro)
4. [Realizar Sorteos](#realizar-sorteos)
5. [Protecciones del Sistema](#protecciones-del-sistema)
6. [Preguntas Frecuentes](#preguntas-frecuentes)

---

## 🔑 Conceptos Clave

### Familia Original vs Nuevas Familias

- **Familia Original (default):**
  - La familia que ya tenías con sorteo realizado
  - **PROTEGIDA:** No se puede editar ni eliminar
  - Fechas congeladas desde el `.env`
  - No requiere `?fam=` en URL de registro

- **Nuevas Familias:**
  - Familias independientes que creas desde el panel
  - Cada una tiene sus propias fechas configurables
  - Requieren `?fam=slug` en URL de registro
  - Totalmente separadas entre sí

### ¿Qué es un Slug?

El **slug** es el identificador único que se usa en la URL. Por ejemplo:
- Slug: `garcia` → URL: `dominio.com/registro?fam=garcia`
- Slug: `familia-rodriguez` → URL: `dominio.com/registro?fam=familia-rodriguez`

**Reglas para slugs:**
- Solo letras minúsculas, números, guiones (`-`) y guiones bajos (`_`)
- Sin espacios ni caracteres especiales
- Máximo 50 caracteres
- Debe ser único (no repetido)

---

## 👨‍👩‍👧‍👦 Gestión de Familias

### Ver Lista de Familias

1. Inicia sesión como administrador
2. Click en **"Familias"** en el menú superior
3. Verás todas las familias con:
   - Nombre y slug
   - Número de usuarios registrados
   - Estado del sorteo (Pendiente/Listo/Sorteado)
   - Estado del registro (Abierto/Cerrado)

### Crear Nueva Familia

1. En la lista de familias, click **"+ Nueva Familia"**
2. Completa el formulario:

   **Información Básica:**
   - **Nombre:** Ej: "Familia García"
   - **Slug:** Ej: "garcia" (para URL)
   - **Descripción:** (Opcional) Ej: "Familia García 2025"

   **Fechas (todas obligatorias):**
   - **🎲 Fecha de Sorteo:** Desde cuándo puedes sortear
   - **🎁 Fecha de Revelación:** Cuándo se revela el amigo secreto
   - **✏️ Fecha Límite Edición:** Hasta cuándo pueden editar perfil

3. Click **"Crear Familia"**
4. **¡Importante!** Copia el enlace de registro que aparece

### Ver Detalles de Familia

1. En lista de familias, click **"Ver"** en la familia deseada
2. Verás:
   - Estadísticas completas
   - Fechas configuradas con cuenta regresiva
   - **Enlace de registro** para compartir
   - Botón para copiar enlace al portapapeles

### Editar Familia

**⚠️ Restricciones:**
- Solo se puede editar ANTES del sorteo
- La familia "Original" NO se puede editar
- Después del sorteo = NO EDITABLE

**Cómo editar:**
1. Click **"Editar"** en la familia (si está disponible)
2. Modifica nombre, slug, fechas o descripción
3. Click **"Actualizar Familia"**

### Eliminar Familia

**⚠️ Restricciones:**
- Solo se puede eliminar si NO tiene usuarios registrados
- La familia "Original" NO se puede eliminar

---

## 📝 Proceso de Registro

### Paso 1: Crear y Configurar Familia

```
Admin → Familias → + Nueva Familia
Completa: Nombre, Slug, Fechas
Sistema genera: dominio.com/registro?fam=garcia
```

### Paso 2: Compartir Enlace

**Opción A - Copiar desde Panel:**
1. Click en "Ver" la familia
2. Click botón "📋 Copiar" en sección de enlace
3. Comparte por WhatsApp, email, etc.

**Opción B - Construcción Manual:**
```
URL base: dominio.com/registro
Agregar: ?fam=TU_SLUG
Resultado: dominio.com/registro?fam=garcia
```

### Paso 3: Usuario se Registra

```
Usuario recibe enlace → Abre URL → Se registra
Sistema guarda: user.family_group_id = (correspondiente)
Usuario ingresa normalmente: dominio.com (sin parámetro)
```

**¡IMPORTANTE!**
- El parámetro `?fam=` solo se usa UNA VEZ en el registro
- Después, el usuario ingresa a `dominio.com` normalmente
- El sistema ya sabe a qué familia pertenece

---

## 🎲 Realizar Sorteos

### Antes del Sorteo

**Verificar:**
- ✅ Al menos 2 usuarios registrados en la familia
- ✅ La fecha de sorteo ya llegó
- ✅ NO existe sorteo previo para esa familia

### Proceso de Sorteo

1. **Ir a Sorteo:**
   - Menu → "Start Draw"

2. **Seleccionar Familia:**
   - Dropdown arriba muestra todas las familias
   - Selecciona la familia que quieres sortear
   - Verás participantes solo de esa familia

3. **Verificar Estado:**
   - **Verde "Listo"**: Puedes sortear
   - **Gris "Pendiente"**: Aún no es fecha
   - **Verde "Sorteado"**: Ya fue sorteado

4. **Iniciar Sorteo:**
   - Click **"🎁 Iniciar Sorteo para [Familia]"**
   - Animación de 10 segundos
   - Confirmación de éxito

### Después del Sorteo

**Automáticamente:**
- 🔒 Registro BLOQUEADO para esa familia
- ✓ Asignaciones guardadas
- ✓ Usuarios pueden ver su amigo secreto en la fecha configurada

---

## 🛡️ Protecciones del Sistema

### 1. Familia Original Protegida

```
❌ NO se puede editar
❌ NO se puede eliminar
❌ NO se puede modificar fechas
✅ Sorteo existente intacto
✅ Usuarios actuales protegidos
```

### 2. Bloqueo de Registro Post-Sorteo

**Cuando una familia ya fue sorteada:**
```
Usuario intenta: dominio.com/registro?fam=garcia
Sistema detecta: Familia García ya tiene sorteo
Sistema bloquea: Redirige a login
Mensaje: "El registro ha sido cerrado"
```

**Aplicable a TODAS las familias**, incluyendo la original sin parámetro.

### 3. Validaciones de Fechas

**Al crear/editar familia:**
```
Fecha Sorteo > Ahora
Fecha Revelación > Fecha Sorteo
Fecha Límite Edición > Fecha Revelación
```

Si las fechas no son lógicas, el sistema rechaza el cambio.

### 4. Slug Protegido

**Slugs reservados (no se pueden usar):**
- `default` (familia original)
- `admin`
- `api`
- `sanctum`

---

## 🔄 Flujos Completos

### Flujo: Nueva Familia Completa

```
DÍA 1 (01/12/2025):
1. Admin crea familia "garcia"
   - Sorteo: 15/12/2025 18:00
   - Revelación: 24/12/2025 20:00
   - Edición hasta: 10/01/2026 23:59

2. Admin copia enlace y comparte:
   dominio.com/registro?fam=garcia

DÍA 1-14 (01-14/12/2025):
3. Usuarios se registran usando el enlace
4. Sistema asigna automáticamente a familia García

DÍA 15 (15/12/2025 18:00):
5. Admin va a "Start Draw"
6. Selecciona "Familia García"
7. Realiza sorteo
8. ¡Registro se cierra automáticamente!

DÍA 24 (24/12/2025 20:00):
9. Usuarios ven su amigo secreto

DÍA 10 (10/01/2026 23:59):
10. Última oportunidad para editar perfil
```

### Flujo: Usuario Intenta Registrarse Tarde

```
Usuario recibe: dominio.com/registro?fam=garcia
Usuario abre enlace (después del sorteo)

Sistema verifica: ¿Familia García ya sorteada?
Respuesta: Sí

Sistema bloquea: Redirige a login
Mensaje: "El registro ha sido cerrado. El sorteo ya fue realizado."
```

---

## ❓ Preguntas Frecuentes

### ¿Puedo tener múltiples familias a la vez?

**Sí, sin límite.** Puedes crear tantas familias como necesites:
- Familia García
- Familia Rodríguez
- Familia Pérez
- Familia del Trabajo
- Familia de Amigos
- etc.

### ¿Las familias se mezclan entre sí?

**No, están completamente separadas:**
- Cada familia tiene sus propios usuarios
- Cada familia tiene su propio sorteo
- Cada familia tiene sus propias fechas
- Los usuarios solo ven su propia familia

### ¿Qué pasa si alguien usa el enlace equivocado?

**El sistema valida:**
- Si el slug no existe → Error: "Enlace no válido"
- Si la familia ya fue sorteada → Error: "Registro cerrado"
- Si el slug es correcto y no hay sorteo → Permite registro

### ¿Puedo cambiar las fechas después de crear la familia?

**Depende:**
- ✅ ANTES del sorteo: Sí, puedes editar todo
- ❌ DESPUÉS del sorteo: No, todo queda congelado
- ❌ Familia original: Nunca se puede editar

### ¿Qué pasa con las fechas del .env?

Las fechas del `.env` se migraron automáticamente a la familia "default" en la base de datos. Ya no se leen del `.env`, se leen de la tabla `family_groups`.

### ¿Los usuarios necesitan usar ?fam= para hacer login?

**NO.** El parámetro `?fam=` solo se usa una vez:
- ✅ Registro: `dominio.com/registro?fam=garcia`
- ❌ Login: `dominio.com` (sin parámetro)
- ❌ Dashboard: `dominio.com` (sin parámetro)

El sistema recuerda automáticamente la familia del usuario.

### ¿Puedo ver usuarios de todas las familias?

**Sí**, en el panel de usuarios (`Users`) verás todos los usuarios. Cada uno tendrá un indicador de su familia.

### ¿Cómo sé si una familia ya fue sorteada?

**Indicadores visuales:**
- En lista de familias: Estado "✓ Sorteado"
- En lista de familias: Registro "🔒 Cerrado"
- En sorteo: Mensaje "Todos los participantes han sido asignados"
- En detalles de familia: Badge verde "✓ Realizado"

### ¿Puedo des-hacer un sorteo?

**No recomendado.** Una vez sorteado:
- El registro se cierra
- Las asignaciones son permanentes
- Los usuarios empezarán a ver sus amigos secretos

Si necesitas eliminar un sorteo, deberás hacerlo manualmente en la base de datos.

---

## 🚀 Guía Rápida de Inicio

### Para Agregar una Nueva Familia:

1. **Crear Familia**
   - Familias → + Nueva Familia
   - Completa nombre, slug y fechas
   - Guarda

2. **Compartir Enlace**
   - Ver detalles de la familia
   - Copiar enlace de registro
   - Compartir con participantes

3. **Esperar Registros**
   - Monitorear cantidad de usuarios
   - Los usuarios se registran usando el enlace

4. **Realizar Sorteo**
   - Start Draw → Seleccionar familia
   - Verificar fecha y participantes
   - Iniciar sorteo

5. **Listo**
   - Registro se cierra automáticamente
   - Usuarios verán su amigo secreto en la fecha configurada

---

## ⚠️ Advertencias Importantes

### 🔴 CRÍTICO - NO HACER:

1. **NO edites la familia "Original"** - Está protegida por una razón
2. **NO cambies manualmente family_group_id** en la base de datos
3. **NO borres la familia "default"** - Causará errores
4. **NO modifiques fechas después del sorteo** - El sistema lo impedirá

### 🟡 PRECAUCIÓN:

1. **Verifica las fechas** antes de crear una familia (deben ser futuras)
2. **Slug único** - No podrás usar el mismo slug dos veces
3. **Backup** - Haz respaldo antes de sortear por primera vez una familia
4. **Usuarios mínimos** - Se necesitan al menos 2 usuarios para sortear

---

## 📊 Panel de Control

### Secciones del Admin:

**1. Familias** (`/admin/family-groups`)
- Ver todas las familias
- Crear nuevas familias
- Ver detalles y enlaces
- Editar configuración

**2. Users** (`/admin/users`)
- Ver todos los usuarios de todas las familias
- Editar usuarios
- Ver a qué familia pertenece cada uno

**3. Start Draw** (`/admin/draw`)
- Selector de familia
- Ver participantes por familia
- Realizar sorteo por familia
- Ver resultados de sorteos

---

## 🎯 Casos de Uso

### Caso 1: Agregar Familia para Navidad

```
Objetivo: Nueva familia "García" para Navidad 2025

Pasos:
1. Crear familia:
   - Nombre: Familia García
   - Slug: garcia
   - Sorteo: 15/12/2025 18:00
   - Revelación: 24/12/2025 20:00
   - Edición: 31/12/2025 23:59

2. Compartir: dominio.com/registro?fam=garcia
3. Esperar registros (hasta 15/12)
4. Sortear el 15/12 a las 18:00
5. Usuarios verán el 24/12 a las 20:00
```

### Caso 2: Múltiples Familias Simultáneas

```
Familia García:
- Slug: garcia
- Sorteo: 15/12/2025
- 15 participantes

Familia Rodríguez:
- Slug: rodriguez
- Sorteo: 20/12/2025
- 20 participantes

Familia del Trabajo:
- Slug: trabajo-2025
- Sorteo: 18/12/2025
- 30 participantes

Resultado: 3 sorteos independientes, 0 conflictos
```

---

## 🔧 Solución de Problemas

### "El enlace de registro no es válido"

**Causas:**
- Slug incorrecto en la URL
- Familia desactivada
- Familia eliminada

**Solución:**
- Verificar que el slug existe en panel de familias
- Verificar que la familia esté activa
- Generar nuevo enlace desde el panel

### "El registro ha sido cerrado"

**Causa:**
- La familia ya tiene sorteo realizado

**Solución:**
- Verificar estado de la familia
- Si fue error, crear nueva familia con slug diferente
- Los usuarios de la familia cerrada ya están registrados

### "Se necesitan al menos 2 usuarios"

**Causa:**
- Intentas sortear con 1 o 0 usuarios

**Solución:**
- Esperar más registros
- Compartir enlace con más personas
- Verificar que los usuarios se registraron en la familia correcta

### "Aún no es tiempo de realizar el sorteo"

**Causa:**
- La fecha de sorteo aún no ha llegado

**Solución:**
- Esperar a la fecha configurada
- Si necesitas sortear antes, edita la fecha de sorteo (antes del sorteo)

---

## 📱 URLs de Referencia

### Admin:
- **Panel de Familias:** `dominio.com/admin/family-groups`
- **Crear Familia:** `dominio.com/admin/family-groups/create`
- **Sorteo:** `dominio.com/admin/draw`
- **Usuarios:** `dominio.com/admin/users`

### Usuarios:
- **Registro (original):** `dominio.com/registro`
- **Registro (nueva):** `dominio.com/registro?fam=SLUG`
- **Login:** `dominio.com`
- **Dashboard:** `dominio.com` (después de login)

---

## 📈 Mejores Prácticas

### 1. Nombra tus Familias Claramente
```
✅ Bueno: "Familia García 2025", "Trabajo - Equipo Marketing"
❌ Malo: "Fam1", "Test", "Familia"
```

### 2. Slugs Descriptivos
```
✅ Bueno: garcia, familia-rodriguez, trabajo-2025
❌ Malo: fam1, test, abc123
```

### 3. Fechas Lógicas
```
✅ Bueno:
   Sorteo: 15/12/2025 18:00
   Revelación: 24/12/2025 20:00
   Edición: 31/12/2025 23:59

❌ Malo:
   Sorteo: 24/12/2025
   Revelación: 15/12/2025 (antes del sorteo!)
```

### 4. Comunicación Clara
```
Al compartir enlace, incluir:
- Nombre de la familia
- Fechas importantes
- Instrucciones de registro
```

### 5. Monitoreo
```
Verificar regularmente:
- Cantidad de usuarios registrados
- Fechas próximas
- Estado del registro
```

---

## 🎁 Ejemplo Completo

### Familia García - Timeline Completa

```
📅 01/12/2025 - 10:00 AM
Admin crea familia "garcia"
Configuración:
- Sorteo: 15/12/2025 18:00
- Revelación: 24/12/2025 20:00
- Edición: 10/01/2026 23:59

📨 01/12/2025 - 10:05 AM
Admin comparte enlace: dominio.com/registro?fam=garcia
Vía WhatsApp grupal

👥 01/12 - 14/12/2025
Usuarios se registran:
- Juan García (02/12)
- María García (03/12)
- Pedro García (05/12)
- ... (total 15 usuarios)

🎲 15/12/2025 - 18:05 PM
Admin realiza sorteo
Sistema: ✓ Sorteo exitoso
Sistema: 🔒 Registro cerrado
Total: 15 asignaciones guardadas

❌ 16/12/2025
Carlos García intenta registrarse
Sistema: Bloquea con mensaje "Registro cerrado"

🎁 24/12/2025 - 20:00 PM
Usuarios ven su amigo secreto
Sistema: Revelación automática

✏️ 01/01/2026 - 10/01/2026
Usuarios pueden editar perfil

🔒 11/01/2026
Edición de perfil cerrada
```

---

## 📞 Soporte

Si encuentras algún problema o necesitas ayuda:

1. Revisa esta guía primero
2. Verifica los logs de Laravel
3. Consulta la documentación técnica:
   - `ARCHITECTURE_MULTI_FAMILY.md`
   - `DATES_MANAGEMENT.md`

---

**Versión:** 1.0
**Última actualización:** 11/11/2025
**Sistema:** Secret Santa Multi-Familia
# 🧪 Instrucciones de Prueba - Sistema Multi-Familia

## ✅ Todo Está Listo Para Probar

El sistema ya está completamente implementado y funcionando con tu vista de registro customizada.

---

## 🎯 Prueba 1: Acceder al Panel de Familias

### Pasos:
```
1. Abrir navegador
2. Ir a: tu-dominio.com
3. Login como admin
4. Click en "Familias" en el menú superior
5. Deberías ver: "Familia Original" en la lista
```

### Resultado Esperado:
✅ Ves la tabla con "Familia Original"
✅ Badge amarillo "Original"
✅ Estado: "✓ Sorteado"
✅ Registro: "🔒 Cerrado"
✅ Botón "+ Nueva Familia" visible

---

## 🎯 Prueba 2: Crear Nueva Familia

### Pasos:
```
1. En panel de Familias
2. Click: + Nueva Familia
3. Completar:
   - Nombre: Familia Test
   - Slug: test
   - Sorteo desde: [fecha futura, ej: mañana]
   - Revelar: [fecha posterior al sorteo]
   - Editar hasta: [fecha posterior a revelación]
4. Click: Crear Familia
```

### Resultado Esperado:
✅ Familia creada exitosamente
✅ Mensaje de éxito
✅ Vuelve a lista de familias
✅ "Familia Test" aparece en lista

---

## 🎯 Prueba 3: Obtener Enlace de Registro

### Pasos:
```
1. En lista de familias
2. Click: "Ver" en "Familia Test"
3. Buscar sección "🔗 Enlace de Registro"
4. Click: 📋 Copiar
```

### Resultado Esperado:
✅ Enlace copiado: dominio.com/registro?fam=test
✅ Mensaje: "✓ Enlace copiado al portapapeles"

---

## 🎯 Prueba 4: Registro de Usuario Nuevo

### Pasos:
```
1. Abrir ventana incógnito
2. Pegar enlace: dominio.com/registro?fam=test
3. Deberías ver TU VISTA CUSTOMIZADA
4. Completar formulario:
   - Foto (opcional)
   - Nombre: Juan Test
   - DNI: 12345678
   - 3 Sugerencias de regalo
   - Contraseña + confirmación
5. Click: Registrar mi participación
```

### Resultado Esperado:
✅ Usuario registrado exitosamente
✅ Login automático
✅ Redirige a perfil
✅ Ve countdown o amigo secreto según fecha

---

## 🎯 Prueba 5: Verificar Asignación de Familia

### Pasos:
```
1. Login como admin (otra ventana)
2. Menu: Users
3. Buscar: "Juan Test"
4. Ver columna "Familia"
```

### Resultado Esperado:
✅ Badge azul: "Familia Test"
✅ Usuario correctamente asignado

---

## 🎯 Prueba 6: Filtro de Usuarios

### Pasos:
```
1. En Users (admin)
2. Dropdown "Filtrar por Familia"
3. Seleccionar: Familia Test
4. Ver lista filtrada
```

### Resultado Esperado:
✅ Solo muestra usuarios de "Familia Test"
✅ "Juan Test" aparece
✅ Usuarios de otras familias NO aparecen

---

## 🎯 Prueba 7: Selector en Sorteo

### Pasos:
```
1. Menu: Start Draw
2. Ver dropdown "Seleccionar Familia"
3. Seleccionar: Familia Test
4. Ver información de la familia
```

### Resultado Esperado:
✅ Lista muestra todas las familias
✅ Al seleccionar, muestra solo usuarios de esa familia
✅ Muestra estado y fechas de la familia
✅ Botón de sorteo adaptado al nombre de familia

---

## 🎯 Prueba 8: Bloqueo por Sorteo

### Pasos:
```
1. En Start Draw, con Familia Test seleccionada
2. Realizar sorteo (si fecha permite)
3. Abrir ventana incógnito
4. Intentar: dominio.com/registro?fam=test
```

### Resultado Esperado:
✅ NO muestra formulario de registro
✅ Redirige a login
✅ Mensaje: "El registro para esta familia ha sido cerrado"

---

## 🎯 Prueba 9: Familia Original Bloqueada

### Pasos:
```
1. Ventana incógnito
2. Ir a: dominio.com/registro (SIN parámetro)
```

### Resultado Esperado:
✅ NO muestra formulario
✅ Redirige a login
✅ Mensaje: "El registro ha sido cerrado"
✅ (Porque tu familia ya tiene sorteo)

---

## 🎯 Prueba 10: Protección de Familia Default

### Pasos:
```
1. Admin → Familias
2. Intentar editar "Familia Original"
3. Click: Editar (si aparece)
```

### Resultado Esperado:
✅ Botón "Editar" NO aparece para familia original
✅ O si aparece y haces click: Mensaje de error
✅ "La familia original no puede ser modificada"

---

## 🎯 Prueba Completa: Flujo End-to-End

### Timeline:
```
DÍA 1 - 10:00 AM:
✅ Admin crea "Familia García"
✅ Fechas: Sorteo 15/12, Revelar 24/12
✅ Copia enlace: dominio.com/registro?fam=garcia

DÍA 1 - 10:05 AM:
✅ Comparte enlace por WhatsApp

DÍA 1-14:
✅ 10 usuarios se registran
✅ Cada uno completa TU FORMULARIO CUSTOMIZADO
✅ Sistema asigna family_group_id automáticamente

DÍA 15 - 18:00:
✅ Admin → Start Draw
✅ Selecciona: Familia García
✅ Inicia sorteo
✅ 10 segundos de animación
✅ Sorteo exitoso
✅ Registro AUTOMÁTICAMENTE CERRADO

DÍA 16:
✅ Nuevo usuario intenta usar enlace
✅ Sistema BLOQUEA
✅ Mensaje: "Registro cerrado"

DÍA 24 - 20:00:
✅ Usuarios ven su amigo secreto
✅ Filtrado automático por familia
```

---

## ✅ Checklist de Verificación

Marca cada item después de probarlo:

- [ ] Panel de familias accesible
- [ ] Familia Original visible
- [ ] Puedo crear familia nueva
- [ ] Enlace se genera correctamente
- [ ] Puedo copiar enlace
- [ ] Registro funciona con ?fam=test
- [ ] Usuario ve TU VISTA customizada
- [ ] Usuario se registra exitosamente
- [ ] Usuario asignado a familia correcta
- [ ] Filtro en Users funciona
- [ ] Selector en Draw funciona
- [ ] Bloqueo post-sorteo funciona
- [ ] Familia Original no editable

---

## 🚨 Si Algo No Funciona

### Error: "No veo panel de Familias"
```
Verifica: ¿Estás logueado como admin?
Solución: Asegúrate de tener is_admin = true
```

### Error: "Enlace de registro no funciona"
```
Verifica: ¿La familia está activa?
Verifica: ¿No tiene sorteo?
Solución: Ver detalles en panel admin
```

### Error: "No se asigna familia"
```
Verifica: ¿El middleware está registrado?
Verifica: ¿La ruta tiene el middleware?
Solución: Ver routes/web.php línea 18
```

### Error: "Fortify redirige a su registro"
```
Verifica: Features::registration() comentado
Archivo: config/fortify.php línea 147
Solución: Ya está desactivado
```

---

## 📞 Archivos de Referencia

**Para Uso Diario:**
→ [`QUICK_START.md`](QUICK_START.md:1)
→ [`ADMIN_GUIDE.md`](ADMIN_GUIDE.md:1)

**Para Esta Actualización:**
→ [`CAMBIOS_FINALES.md`](CAMBIOS_FINALES.md:1)

**Técnicos:**
→ [`ARCHITECTURE_MULTI_FAMILY.md`](ARCHITECTURE_MULTI_FAMILY.md:1)
→ [`DATES_MANAGEMENT.md`](DATES_MANAGEMENT.md:1)

---

**Estado:** ✅ Listo para Testing
**Vista de Registro:** ✅ Tu Vista Customizada
**Sistema:** ✅ Completamente Operativo
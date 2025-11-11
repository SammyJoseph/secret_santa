# Gestión de Fechas por Familia

## Problema Actual

El archivo `.env` tiene fechas globales:
```env
SECRET_SANTA_ENABLE_DRAW="2025-10-25 10:00:00"
SECRET_SANTA_REVEAL_DATE="2025-10-25 12:00:00"
SECRET_SANTA_PROFILE_EDIT_END_DATE="2025-11-30 23:59:59"
```

**Restricción:** Estas fechas solo sirven para UNA familia, pero necesitamos múltiples familias independientes.

## Solución: Fechas en Base de Datos

### Estructura de Tabla family_groups

```sql
CREATE TABLE family_groups (
    id BIGINT UNSIGNED PRIMARY KEY,
    slug VARCHAR(255) UNIQUE,
    name VARCHAR(255),
    enable_draw_at DATETIME,           -- Cuándo se habilita el sorteo
    reveal_date DATETIME,               -- Cuándo se revela el amigo secreto
    profile_edit_end_date DATETIME,     -- Hasta cuándo se puede editar perfil
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Migración de Familia Original

La familia original heredará las fechas del `.env`:

```php
// En migration: xxxx_create_family_groups_table.php
public function up()
{
    Schema::create('family_groups', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique();
        $table->string('name');
        $table->text('description')->nullable();
        $table->boolean('is_active')->default(true);
        $table->dateTime('enable_draw_at')->nullable();
        $table->dateTime('reveal_date')->nullable();
        $table->dateTime('profile_edit_end_date')->nullable();
        $table->timestamps();
    });
    
    // Crear familia default con fechas actuales del .env
    DB::table('family_groups')->insert([
        'slug' => 'default',
        'name' => 'Familia Original',
        'is_active' => true,
        'enable_draw_at' => env('SECRET_SANTA_ENABLE_DRAW'),
        'reveal_date' => env('SECRET_SANTA_REVEAL_DATE'),
        'profile_edit_end_date' => env('SECRET_SANTA_PROFILE_EDIT_END_DATE'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
```

## Cómo Funciona por Familia

### Familia Original (ID=1, slug='default')

```php
// ✅ Fechas YA CONGELADAS en BD
$familiaOriginal = FamilyGroup::find(1);
$familiaOriginal->enable_draw_at;        // "2025-10-25 10:00:00" (de BD)
$familiaOriginal->reveal_date;           // "2025-10-25 12:00:00" (de BD)
$familiaOriginal->profile_edit_end_date; // "2025-11-30 23:59:59" (de BD)

// ❌ Admin NO puede editar estas fechas
// ❌ Admin NO puede borrar esta familia
```

### Nueva Familia García (ID=2, slug='garcia')

```php
// ✅ Admin configura fechas independientes
$familiaGarcia = FamilyGroup::find(2);
$familiaGarcia->enable_draw_at;        // "2025-12-15 18:00:00"
$familiaGarcia->reveal_date;           // "2025-12-24 20:00:00"
$familiaGarcia->profile_edit_end_date; // "2026-01-10 23:59:59"

// ✅ Admin puede editar estas fechas (antes del sorteo)
// ⚠️ Después del sorteo = NO editable
```

## Uso en Controladores

### Antes (leyendo del .env)

```php
// ❌ INCORRECTO: Global para todas las familias
$revealDate = new \DateTime(config('services.secret_santa.reveal_date'));
```

### Después (leyendo por familia)

```php
// ✅ CORRECTO: Específico por familia del usuario
$user = Auth::user();
$familyGroup = $user->familyGroup;

$revealDate = $familyGroup->reveal_date;
$canEditProfile = $familyGroup->canEditProfile();
```

## Ejemplo Completo: UserController

```php
public function profile()
{
    $user = Auth::user();
    $familyGroup = $user->familyGroup;
    
    // Usar fechas de la familia
    $now = now();
    $isRevealed = $now >= $familyGroup->reveal_date;
    $canEditProfile = $now <= $familyGroup->profile_edit_end_date;
    
    $secretSanta = null;
    if ($isRevealed) {
        $assignment = SecretSantaAssignment::where('giver_id', $user->id)
            ->where('family_group_id', $user->family_group_id)
            ->with('receiver')
            ->first();
        
        if ($assignment) {
            $secretSanta = $assignment->receiver;
        }
    }
    
    return view('user.profile', compact(
        'user', 
        'secretSanta', 
        'isRevealed',
        'canEditProfile',
        'familyGroup'
    ));
}
```

## Vista del Dashboard

```blade
<!-- resources/views/user/profile.blade.php -->

@if($familyGroup->isRevealed())
    <!-- Mostrar amigo secreto -->
    <h2>¡Tu Amigo Secreto es!</h2>
    <p>{{ $secretSanta->name }}</p>
@else
    <!-- Mostrar countdown -->
    <h2>Faltan para la revelación:</h2>
    <div id="countdown" 
         data-reveal-date="{{ $familyGroup->reveal_date->toIso8601String() }}">
    </div>
@endif

@if($familyGroup->canEditProfile())
    <button>Editar Perfil</button>
@else
    <p>Edición de perfil cerrada desde {{ $familyGroup->profile_edit_end_date->format('d/m/Y') }}</p>
@endif
```

## Panel Admin: Crear Nueva Familia

```blade
<!-- resources/views/admin/family-groups/create.blade.php -->

<form method="POST" action="{{ route('admin.family-groups.store') }}">
    @csrf
    
    <div>
        <label>Slug (identificador único)</label>
        <input type="text" name="slug" required pattern="[a-z0-9-]+">
        <small>Ej: garcia, rodriguez, familia-perez</small>
    </div>
    
    <div>
        <label>Nombre</label>
        <input type="text" name="name" required>
        <small>Ej: Familia García, Los Rodríguez</small>
    </div>
    
    <hr>
    
    <h3>📅 Configuración de Fechas</h3>
    
    <div>
        <label>🎲 Habilitar sorteo desde:</label>
        <input type="datetime-local" name="enable_draw_at" required>
        <small>El admin podrá sortear desde esta fecha</small>
    </div>
    
    <div>
        <label>🎁 Revelar amigo secreto:</label>
        <input type="datetime-local" name="reveal_date" required>
        <small>Los usuarios verán su amigo secreto desde esta fecha</small>
    </div>
    
    <div>
        <label>✏️ Fecha límite para editar perfil:</label>
        <input type="datetime-local" name="profile_edit_end_date" required>
        <small>Después de esta fecha no se puede editar perfil</small>
    </div>
    
    <button type="submit">Crear Familia</button>
</form>
```

## Validaciones en FamilyGroupController

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'slug' => 'required|unique:family_groups|alpha_dash|max:50',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'enable_draw_at' => 'required|date|after:now',
        'reveal_date' => 'required|date|after:enable_draw_at',
        'profile_edit_end_date' => 'required|date|after:reveal_date',
    ], [
        'enable_draw_at.after' => 'La fecha de sorteo debe ser futura',
        'reveal_date.after' => 'La revelación debe ser después del sorteo',
        'profile_edit_end_date.after' => 'La fecha límite debe ser después de la revelación',
    ]);
    
    FamilyGroup::create($validated);
    
    return redirect()->route('admin.family-groups.index')
        ->with('success', 'Familia creada exitosamente');
}

public function edit(FamilyGroup $familyGroup)
{
    // Proteger familia original
    if ($familyGroup->isDefault()) {
        return redirect()->back()
            ->with('error', 'La familia original no puede ser modificada para proteger los sorteos existentes.');
    }
    
    // Proteger familias ya sorteadas
    if ($familyGroup->hasDrawn()) {
        return redirect()->back()
            ->with('error', 'No se puede editar una familia que ya tiene sorteo realizado.');
    }
    
    return view('admin.family-groups.edit', compact('familyGroup'));
}
```

## Métodos Útiles en Modelo FamilyGroup

```php
class FamilyGroup extends Model
{
    protected $casts = [
        'enable_draw_at' => 'datetime',
        'reveal_date' => 'datetime',
        'profile_edit_end_date' => 'datetime',
    ];
    
    // ¿Se puede sortear ya?
    public function canDraw()
    {
        return now()->gte($this->enable_draw_at);
    }
    
    // ¿Ya se reveló el amigo secreto?
    public function isRevealed()
    {
        return now()->gte($this->reveal_date);
    }
    
    // ¿Aún se puede editar perfil?
    public function canEditProfile()
    {
        return now()->lte($this->profile_edit_end_date);
    }
    
    // ¿Es la familia original protegida?
    public function isDefault()
    {
        return $this->id === 1 || $this->slug === 'default';
    }
    
    // ¿Ya tiene sorteo?
    public function hasDrawn()
    {
        return $this->assignments()->exists();
    }
}
```

## Ventajas de este Sistema

1. ✅ **Familia original protegida** - Sus fechas están congeladas en BD
2. ✅ **Independencia total** - Cada familia tiene sus propias fechas
3. ✅ **Flexibilidad** - Admin puede configurar fechas diferentes por familia
4. ✅ **Simplicidad** - Usuario no necesita saber nada, se filtra automáticamente
5. ✅ **No afecta .env** - Las variables del .env solo se usan una vez para migrar

## Tabla Comparativa

| Aspecto | Antes (Global .env) | Después (Por Familia en BD) |
|---------|-------------------|---------------------------|
| **Alcance** | Una sola familia | Múltiples familias |
| **Configuración** | Manual en .env | Panel admin con UI |
| **Modificable** | Solo editando archivo | Solo antes del sorteo |
| **Familia original** | En uso | Protegida, congelada |
| **Nuevas familias** | No soportado | Fácil de crear |
| **Fechas diferentes** | Imposible | Totalmente posible |

## Flujo Temporal Completo

### Familia García - Timeline

```
2025-12-01 00:00 - Admin crea familia "garcia"
                   (enable_draw_at: 2025-12-15 18:00)

2025-12-01 10:00 - Admin comparte link: dominio.com/registro?fam=garcia

2025-12-01-14 - Usuarios se registran
                (family_group_id = 2 asignado)

2025-12-15 17:59 - ❌ Botón sorteo deshabilitado
2025-12-15 18:00 - ✅ Botón sorteo habilitado
2025-12-15 18:05 - Admin realiza sorteo
                   (assignments guardados con family_group_id = 2)
2025-12-15 18:06 - 🔒 Registro bloqueado (sorteo realizado)

2025-12-24 19:59 - ❌ Usuarios no ven amigo secreto
2025-12-24 20:00 - ✅ Usuarios ven su amigo secreto

2026-01-10 23:59 - ✅ Última oportunidad para editar perfil
2026-01-11 00:00 - ❌ Edición de perfil cerrada
```

---

**Nota:** Este sistema garantiza que tu familia original (con sorteo ya realizado) permanezca intacta mientras permites crear familias nuevas con fechas totalmente independientes.
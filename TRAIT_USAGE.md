# HasStateTransitions Trait - Documentación

## 🎯 **Propósito**
Trait reutilizable para agregar automáticamente acciones de transición de estado a cualquier Resource de Filament que use Spatie Model States.

## 🚀 **Uso Básico**

### 1. En tu Resource:
```php
<?php

namespace App\Filament\Resources;

use App\Filament\Traits\HasStateTransitions;

class YourModelResource extends Resource
{
    use HasStateTransitions;
    
    public static function table(Table $table): Table
    {
        return $table
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                // ✨ Esto automáticamente agrega todas las transiciones dinámicas
                ...static::getStateTransitionActions(),
            ]);
    }
}
```

### 2. Tu modelo debe usar Spatie Model States:
```php
<?php

namespace App\Models;

use Spatie\ModelStates\HasStates;

class YourModel extends Model
{
    use HasStates;
    
    protected $casts = [
        'state' => YourModelState::class,
    ];
}
```

## 🎨 **Funcionalidades Automáticas**

### ✅ **Auto-detección**
- Detecta automáticamente si el modelo usa Spatie States
- Solo muestra acciones si hay transiciones disponibles
- Se adapta dinámicamente a cualquier modelo

### ✅ **Estilos Inteligentes**
- **Borrador**: Amarillo + icono documento
- **Pendiente**: Azul + icono reloj  
- **Aprobado**: Verde + icono check
- **Rechazado**: Rojo + icono X
- **Completado**: Verde + icono badge
- **Cancelado**: Gris + icono prohibido

### ✅ **UX Completa**
- Confirmaciones modales automáticas
- Notificaciones de éxito/error
- Labels en español automáticos
- Manejo de errores robusto

## 🔧 **Personalización**

### Personalizar permisos por Resource:
```php
class DocumentationResource extends Resource
{
    use HasStateTransitions;
    
    protected static function canPerformTransition(Model $record, string $stateClass): bool
    {
        $user = auth()->user();
        $stateName = class_basename($stateClass);
        
        // Lógica específica para este Resource
        if ($stateName === 'PendingSupervisorState') {
            return $user->hasRole('super_admin');
        }
        
        if ($stateName === 'ApprovedTravelState') {
            return $user->hasRole(['super_admin', 'Travel']);
        }
        
        return parent::canPerformTransition($record, $stateClass);
    }
}
```

### Personalizar labels:
```php
protected static function getTransitionLabel(string $stateName): string
{
    $customLabels = [
        'YourSpecialState' => 'Tu Estado Especial',
        'AnotherState' => 'Otro Estado',
    ];
    
    return $customLabels[$stateName] ?? parent::getTransitionLabel($stateName);
}
```

## 📱 **Resultado Visual**

Los usuarios verán un botón **"Cambiar Estado"** que al hacer clic muestra un dropdown con todas las transiciones disponibles según:

1. **Estados permitidos** por Spatie Model States
2. **Permisos del usuario** (si están configurados)
3. **Estado actual** del registro

## 🔄 **Para Futuros Resources**

Simplemente agrega el trait y las acciones:

```php
class OrderResource extends Resource 
{
    use HasStateTransitions;
    
    // En la tabla:
    ->actions([
        // Acciones normales...
        ...static::getStateTransitionActions(), // ¡Listo!
    ])
}

class InvoiceResource extends Resource 
{
    use HasStateTransitions;
    
    // Funciona automáticamente con cualquier modelo que use Spatie States
}
```

## ✅ **Ventajas**

- **Reutilizable**: Un trait para todos los Resources futuros
- **Dinámico**: Se adapta automáticamente a cualquier flujo de estados  
- **Consistente**: UX uniforme en toda la aplicación
- **Extensible**: Fácil de personalizar por Resource cuando sea necesario
- **Mantenible**: Cambios centralizados en un solo archivo

---

**Ubicación del archivo**: `/app/Filament/Traits/HasStateTransitions.php`
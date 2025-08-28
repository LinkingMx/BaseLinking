<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StateTransition extends Model
{
    protected $fillable = [
        'from_state_id',
        'to_state_id',
        'name',
        'label',
        'description',
        'requires_permission',
        'permission_name',
        'requires_role',
        'role_names',
        'requires_approval',
        'approver_roles',
        'condition_rules',
        'notification_template',
        'success_message',
        'failure_message',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requires_permission' => 'boolean',
        'requires_role' => 'boolean',
        'requires_approval' => 'boolean',
        'role_names' => 'array',
        'approver_roles' => 'array',
        'condition_rules' => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'creator_restriction',
        'restriction_applies_to_roles',
        'restriction_except_roles',
        'creator_field_name',
        'model_type',
    ];

    /**
     * Relación con el estado origen
     */
    public function fromState(): BelongsTo
    {
        return $this->belongsTo(ApprovalState::class, 'from_state_id');
    }

    /**
     * Relación con el estado destino
     */
    public function toState(): BelongsTo
    {
        return $this->belongsTo(ApprovalState::class, 'to_state_id');
    }

    /**
     * Verificar si el usuario puede ejecutar esta transición
     */
    public function canBeExecutedBy(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        // Verificar permisos
        if ($this->requires_permission && $this->permission_name) {
            if (! $user->can($this->permission_name)) {
                return false;
            }
        }

        // Verificar roles
        if ($this->requires_role && ! empty($this->role_names)) {
            $hasRole = false;
            foreach ($this->role_names as $roleName) {
                if ($user->hasRole($roleName)) {
                    $hasRole = true;
                    break;
                }
            }
            if (! $hasRole) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verificar si se cumplen las condiciones para la transición
     */
    public function conditionsAreMet(Model $model): bool
    {
        if (empty($this->condition_rules)) {
            return true;
        }

        foreach ($this->condition_rules as $rule) {
            if (! $this->evaluateCondition($model, $rule)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluar una condición específica
     */
    protected function evaluateCondition(Model $model, array $rule): bool
    {
        $type = $rule['type'] ?? 'field_comparison';

        // Handle different types of conditions
        switch ($type) {
            case 'creator_restriction':
                return $this->evaluateCreatorRestriction($model, $rule);

            case 'field_comparison':
            default:
                return $this->evaluateFieldComparison($model, $rule);
        }
    }

    /**
     * Evaluate creator restriction conditions
     */
    protected function evaluateCreatorRestriction(Model $model, array $rule): bool
    {
        $restriction = $rule['restriction'] ?? null;
        $user = auth()->user();

        if (! $user || ! $restriction) {
            return true;
        }

        switch ($restriction) {
            case 'cannot_reverse_after_submission':
                // Creator cannot reverse transitions once submitted to higher authority
                $appliesToRoles = $rule['applies_to'] ?? ['User'];
                $exceptRoles = $rule['except_roles'] ?? ['super_admin'];

                // If user has exception role, allow the transition
                foreach ($exceptRoles as $role) {
                    if ($user->hasRole($role)) {
                        return true;
                    }
                }

                // If user has restricted role and is the creator, deny the transition
                foreach ($appliesToRoles as $role) {
                    if ($user->hasRole($role)) {
                        // Check if user is the creator
                        $creatorField = $rule['creator_field'] ?? 'created_by';
                        if ($model->{$creatorField} === $user->id) {
                            return false; // Deny the transition
                        }
                    }
                }

                return true; // Allow if not creator or doesn't have restricted role

            case 'only_approver_can_change':
                // Only the designated approver can make this transition
                $approverRoles = $rule['approver_roles'] ?? [];

                return $user->hasAnyRole($approverRoles);

            default:
                return true;
        }
    }

    /**
     * Evaluate field comparison conditions (original logic)
     */
    protected function evaluateFieldComparison(Model $model, array $rule): bool
    {
        $field = $rule['field'] ?? null;
        $operator = $rule['operator'] ?? '=';
        $value = $rule['value'] ?? null;

        if (! $field) {
            return true;
        }

        $modelValue = data_get($model, $field);

        return match ($operator) {
            '=' => $modelValue == $value,
            '!=' => $modelValue != $value,
            '>' => $modelValue > $value,
            '<' => $modelValue < $value,
            '>=' => $modelValue >= $value,
            '<=' => $modelValue <= $value,
            'in' => in_array($modelValue, (array) $value),
            'not_in' => ! in_array($modelValue, (array) $value),
            'contains' => str_contains((string) $modelValue, (string) $value),
            'starts_with' => str_starts_with((string) $modelValue, (string) $value),
            'ends_with' => str_ends_with((string) $modelValue, (string) $value),
            'is_null' => is_null($modelValue),
            'is_not_null' => ! is_null($modelValue),
            default => true,
        };
    }

    /**
     * Obtener transiciones disponibles para un estado
     */
    public static function getAvailableTransitions(ApprovalState $fromState, ?User $user = null, ?Model $model = null): \Illuminate\Database\Eloquent\Collection
    {
        $transitions = static::where('from_state_id', $fromState->id)
            ->where('is_active', true)
            ->with(['toState'])
            ->orderBy('sort_order')
            ->get();

        return $transitions->filter(function ($transition) use ($user, $model) {
            // Check role/permission requirements
            if (! $transition->canBeExecutedBy($user)) {
                return false;
            }

            // Check condition requirements if model is provided
            if ($model && ! $transition->conditionsAreMet($model)) {
                return false;
            }

            return true;
        });
    }

    /**
     * Obtener mensaje de éxito para la transición
     */
    public function getSuccessMessage(): string
    {
        return $this->success_message ?: "Estado cambiado a {$this->toState->label} exitosamente.";
    }

    /**
     * Obtener mensaje de error para la transición
     */
    public function getFailureMessage(): string
    {
        return $this->failure_message ?: "No se pudo cambiar el estado a {$this->toState->label}.";
    }

    /**
     * Scope para transiciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para transiciones desde un estado específico
     */
    public function scopeFromState($query, int $stateId)
    {
        return $query->where('from_state_id', $stateId);
    }

    /**
     * Scope para transiciones hacia un estado específico
     */
    public function scopeToState($query, int $stateId)
    {
        return $query->where('to_state_id', $stateId);
    }

    /**
     * UI Field Accessors - Convert condition_rules JSON to UI fields
     */
    public function getCreatorRestrictionAttribute(): string
    {
        $creatorRule = $this->getCreatorRestrictionRule();

        return $creatorRule ? ($creatorRule['restriction'] ?? 'none') : 'none';
    }

    public function getRestrictionAppliesToRolesAttribute(): array
    {
        $creatorRule = $this->getCreatorRestrictionRule();

        return $creatorRule ? ($creatorRule['applies_to'] ?? ['User']) : ['User'];
    }

    public function getRestrictionExceptRolesAttribute(): array
    {
        $creatorRule = $this->getCreatorRestrictionRule();

        return $creatorRule ? ($creatorRule['except_roles'] ?? ['super_admin']) : ['super_admin'];
    }

    public function getCreatorFieldNameAttribute(): string
    {
        $creatorRule = $this->getCreatorRestrictionRule();

        return $creatorRule ? ($creatorRule['creator_field'] ?? 'created_by') : 'created_by';
    }

    /**
     * Obtener el tipo de modelo desde el estado origen
     */
    public function getModelTypeAttribute(): ?string
    {
        return $this->fromState?->model_type;
    }

    /**
     * UI Field Mutators - Convert UI fields to condition_rules JSON
     */
    public function setCreatorRestrictionAttribute($value): void
    {
        $this->updateCreatorRestrictionRule('restriction', $value);
    }

    public function setRestrictionAppliesToRolesAttribute($value): void
    {
        $this->updateCreatorRestrictionRule('applies_to', $value);
    }

    public function setRestrictionExceptRolesAttribute($value): void
    {
        $this->updateCreatorRestrictionRule('except_roles', $value);
    }

    public function setCreatorFieldNameAttribute($value): void
    {
        $this->updateCreatorRestrictionRule('creator_field', $value);
    }

    /**
     * Helper methods for managing creator restriction rules
     */
    private function getCreatorRestrictionRule(): ?array
    {
        $rules = $this->condition_rules ?? [];

        foreach ($rules as $rule) {
            if (($rule['type'] ?? null) === 'creator_restriction') {
                return $rule;
            }
        }

        return null;
    }

    private function updateCreatorRestrictionRule(string $field, $value): void
    {
        $rules = $this->condition_rules ?? [];
        $creatorRuleIndex = null;

        // Find existing creator restriction rule
        foreach ($rules as $index => $rule) {
            if (($rule['type'] ?? null) === 'creator_restriction') {
                $creatorRuleIndex = $index;
                break;
            }
        }

        // If no creator restriction exists and we're setting a meaningful value
        if ($creatorRuleIndex === null && $field === 'restriction' && $value !== 'none') {
            $rules[] = [
                'type' => 'creator_restriction',
                'restriction' => $value,
                'applies_to' => ['User'],
                'except_roles' => ['super_admin'],
                'creator_field' => 'created_by',
                'description' => 'Creator restriction configured from UI',
            ];
        }
        // If creator restriction exists, update it
        elseif ($creatorRuleIndex !== null) {
            // If setting restriction to 'none', remove the rule entirely
            if ($field === 'restriction' && $value === 'none') {
                unset($rules[$creatorRuleIndex]);
                $rules = array_values($rules); // Re-index array
            } else {
                $rules[$creatorRuleIndex][$field] = $value;
            }
        }

        $this->condition_rules = $rules;
    }
}

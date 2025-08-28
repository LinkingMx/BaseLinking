<?php

namespace App\States;

use App\Models\ApprovalState;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class DocumentationState extends State
{
    /**
     * Estado inicial por defecto
     */
    public static function default(): DocumentationState
    {
        return new DraftState(null);
    }

    /**
     * Configuración de estados para Documentation
     */
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(DraftState::class)
            // Flujo principal de aprobación
            ->allowTransition(DraftState::class, PendingSupervisorState::class)
            ->allowTransition(PendingSupervisorState::class, FullyApprovedState::class) // ← AGREGADO: Transición directa a aprobado
            ->allowTransition(PendingSupervisorState::class, ApprovedSupervisorPendingTravelState::class)
            ->allowTransition(ApprovedSupervisorPendingTravelState::class, ApprovedTravelPendingTreasuryState::class)
            ->allowTransition(ApprovedTravelPendingTreasuryState::class, FullyApprovedState::class)

            // Transiciones de rechazo desde cualquier estado de aprobación
            ->allowTransition(PendingSupervisorState::class, RejectedState::class)
            ->allowTransition(ApprovedSupervisorPendingTravelState::class, RejectedState::class)
            ->allowTransition(ApprovedTravelPendingTreasuryState::class, RejectedState::class)

            // Regreso a borrador desde rechazo
            ->allowTransition(RejectedState::class, DraftState::class)

            // Compatibilidad con estados legacy
            ->allowTransition(DraftState::class, PendingApprovalState::class) // backward compatibility
            ->allowTransition(PendingApprovalState::class, ApprovedState::class) // backward compatibility
            ->allowTransition(PendingApprovalState::class, RejectedState::class) // backward compatibility
            ->allowTransition(PendingApprovalState::class, ApprovedSupervisorPendingTravelState::class) // bridge to new system

            // Estados finales
            ->allowTransition(FullyApprovedState::class, PublishedState::class)
            ->allowTransition([FullyApprovedState::class, PublishedState::class], ArchivedState::class);
    }

    /**
     * Obtener ApprovalState correspondiente
     */
    public function getApprovalState(): ?ApprovalState
    {
        $stateName = $this->getStateName();

        return ApprovalState::where('model_type', 'App\\Models\\Documentation')
            ->where('name', $stateName)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Obtener nombre del estado
     */
    abstract public function getStateName(): string;

    /**
     * Obtener color para badges
     */
    abstract public function getColor(): string;

    /**
     * Obtener icono
     */
    abstract public function getIcon(): string;

    /**
     * Obtener descripción
     */
    abstract public function getDescription(): string;

    /**
     * Verificar si requiere aprobación
     */
    public function requiresApproval(): bool
    {
        return $this->getApprovalState()?->requires_approval ?? false;
    }

    /**
     * Verificar si es estado inicial
     */
    public function isInitial(): bool
    {
        return $this->getApprovalState()?->is_initial ?? false;
    }

    /**
     * Verificar si es estado final
     */
    public function isFinal(): bool
    {
        return $this->getApprovalState()?->is_final ?? false;
    }
}

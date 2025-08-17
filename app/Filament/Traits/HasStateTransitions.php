<?php

namespace App\Filament\Traits;

use Filament\Notifications\Notification;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\State;

trait HasStateTransitions
{
    /**
     * Get dynamic state transition actions for Filament resources
     */
    public static function getStateTransitionActions(): array
    {
        return [
            Tables\Actions\Action::make('state_transitions')
                ->label('Cambiar Estado')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('info')
                ->visible(function (Model $record) {
                    // Only show if model has states and transitions available
                    if (! method_exists($record, 'state') || ! $record->state instanceof State) {
                        return false;
                    }

                    return ! empty($record->state->transitionableStates());
                })
                ->action(function (Model $record, array $data) {
                    // This will be handled by the dropdown menu
                })
                ->form(function (Model $record) {
                    $options = [];

                    // Check if model uses Spatie Model States
                    if (! method_exists($record, 'state') || ! $record->state instanceof State) {
                        return [];
                    }

                    // Get available state transitions
                    $availableTransitions = $record->state->transitionableStates();

                    foreach ($availableTransitions as $stateClass) {
                        // Check user permissions for this transition
                        if (static::canPerformTransition($record, $stateClass)) {
                            $transitionName = class_basename($stateClass);
                            $label = static::getTransitionLabel($transitionName);
                            $options[$stateClass] = $label;
                        }
                    }

                    if (empty($options)) {
                        return [];
                    }

                    return [
                        Forms\Components\Select::make('new_state')
                            ->label('Nuevo Estado')
                            ->options($options)
                            ->required()
                            ->native(false),
                    ];
                })
                ->action(function (Model $record, array $data) {
                    if (! isset($data['new_state'])) {
                        return;
                    }

                    $stateClass = $data['new_state'];
                    $transitionName = class_basename($stateClass);
                    $label = static::getTransitionLabel($transitionName);

                    try {
                        $record->state->transitionTo($stateClass);
                        $record->refresh();

                        Notification::make()
                            ->title('Estado actualizado correctamente')
                            ->body("Documento cambiado a: {$label}")
                            ->success()
                            ->duration(5000)
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al cambiar estado')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(7000)
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Cambiar Estado del Documento')
                ->modalDescription('¿Está seguro que desea cambiar el estado de este documento?')
                ->modalSubmitActionLabel('Sí, cambiar estado'),
        ];
    }

    /**
     * Get color for transition based on state name
     */
    protected static function getTransitionColor(string $stateName): string
    {
        return match (true) {
            str_contains(strtolower($stateName), 'draft') => 'warning',
            str_contains(strtolower($stateName), 'pending') => 'info',
            str_contains(strtolower($stateName), 'approved') => 'success',
            str_contains(strtolower($stateName), 'rejected') => 'danger',
            str_contains(strtolower($stateName), 'completed') => 'success',
            str_contains(strtolower($stateName), 'cancelled') => 'gray',
            default => 'primary'
        };
    }

    /**
     * Get icon for transition based on state name
     */
    protected static function getTransitionIcon(string $stateName): string
    {
        return match (true) {
            str_contains(strtolower($stateName), 'draft') => 'heroicon-o-document-text',
            str_contains(strtolower($stateName), 'pending') => 'heroicon-o-clock',
            str_contains(strtolower($stateName), 'approved') => 'heroicon-o-check-circle',
            str_contains(strtolower($stateName), 'rejected') => 'heroicon-o-x-circle',
            str_contains(strtolower($stateName), 'completed') => 'heroicon-o-check-badge',
            str_contains(strtolower($stateName), 'cancelled') => 'heroicon-o-no-symbol',
            default => 'heroicon-o-arrow-right'
        };
    }

    /**
     * Get human-readable label for transition
     */
    protected static function getTransitionLabel(string $stateName): string
    {
        // Convert PascalCase to readable format
        $label = preg_replace('/(?<!^)[A-Z]/', ' $0', $stateName);

        // Spanish translations for common states
        $translations = [
            'Draft State' => 'Borrador',
            'Pending Supervisor State' => 'Pendiente Supervisor',
            'Approved Supervisor Pending Travel State' => 'Aprobado Supervisor - Pendiente Travel',
            'Approved Travel Pending Treasury State' => 'Aprobado Travel - Pendiente Treasury',
            'Fully Approved State' => 'Completamente Aprobado',
            'Rejected State' => 'Rechazado',
            'Cancelled State' => 'Cancelado',
            'Completed State' => 'Completado',
        ];

        return $translations[$label] ?? ucwords(strtolower($label));
    }

    /**
     * Check if current user can perform this transition
     * Override this method in your Resource for custom permission logic
     */
    protected static function canPerformTransition(Model $record, string $stateClass): bool
    {
        // Basic implementation - override in your Resource for role-based permissions
        $user = auth()->user();
        $stateName = class_basename($stateClass);

        // Example permission logic (customize per Resource)
        if (! $user) {
            return false;
        }

        // Super admin can do everything
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Add your specific business logic here
        // For example:
        // - Only creators can submit for approval
        // - Only supervisors can approve pending items
        // - etc.

        return true; // Default: allow all authenticated users
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentationResource\Pages;
use App\Filament\Resources\DocumentationResource\RelationManagers;
use App\Filament\Traits\HasStateTransitions;
use App\Models\Documentation;
use App\Models\StateTransitionLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DocumentationResource extends Resource
{
    use HasStateTransitions;

    protected static ?string $model = Documentation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Gestión de Contenido';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Documento';

    protected static ?string $pluralModelLabel = 'Documentación';

    protected static ?string $navigationLabel = 'Documentación';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Documento')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('state')
                            ->label('Estado')
                            ->options(function ($record) {
                                if (! $record) {
                                    return ['draft' => 'Borrador'];
                                }

                                $availableTransitions = $record->getAvailableStateTransitions();
                                $currentState = $record->state;

                                $options = [];
                                if ($currentState) {
                                    $options[$currentState->getStateName()] = $currentState->getDescription();
                                }

                                foreach ($availableTransitions as $transition) {
                                    $toState = $transition['to_state'];
                                    $options[$toState->name] = $toState->label;
                                }

                                return $options;
                            })
                            ->disabled(fn ($context) => $context === 'create')
                            ->helperText('Los documentos nuevos se crean en estado Borrador'),
                    ]),

                Forms\Components\Section::make('Información de Aprobación')
                    ->schema([
                        Forms\Components\Select::make('approved_by')
                            ->label('Aprobado por')
                            ->relationship('approver', 'name')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Fecha de Aprobación')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && $record->approved_at),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(100)
                    ->tooltip(fn (Documentation $record) => $record->description),

                Tables\Columns\TextColumn::make('state')
                    ->label('Estado')
                    ->formatStateUsing(function ($record) {
                        if (! $record->state) {
                            return 'Sin estado';
                        }

                        return $record->state->getDescription();
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (! $record->state) {
                            return 'gray';
                        }

                        return $record->state->getColor();
                    }),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->default('Sistema'),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Aprobado por')
                    ->default('Pendiente')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Aprobado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label('Estado')
                    ->options(function () {
                        $states = \App\Models\ApprovalState::where('model_type', 'App\\Models\\Documentation')
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->get();

                        return $states->pluck('label', 'name')->toArray();
                    }),

                Tables\Filters\SelectFilter::make('created_by')
                    ->label('Creado por')
                    ->relationship('creator', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Simple state transition action
                Tables\Actions\Action::make('cambiar_estado')
                    ->label('Aprobaciones')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->visible(function ($record) {
                        // Check if model has states and transitions available
                        if (! isset($record->state) || ! $record->state instanceof \Spatie\ModelStates\State) {
                            return false;
                        }

                        // Check if user has permissions for any transition
                        return static::hasPermissionForAnyTransition($record);
                    })
                    ->form(function ($record) {
                        $options = [];

                        // Check if model uses Spatie Model States
                        if (! isset($record->state) || ! $record->state instanceof \Spatie\ModelStates\State) {
                            return [];
                        }

                        // Get available transitions using dynamic system
                        $availableTransitions = static::getAvailableTransitionsForRecord($record);

                        foreach ($availableTransitions as $transitionData) {
                            $stateClass = $transitionData['state_class'];
                            $transition = $transitionData['transition'];
                            $targetState = $transitionData['target_state'];

                            // Use the label from the target ApprovalState
                            $label = $targetState->label;
                            $options[$stateClass] = $label;
                        }

                        if (empty($options)) {
                            return [];
                        }

                        return [
                            \Filament\Forms\Components\Select::make('new_state')
                                ->label('Nuevo Estado')
                                ->options($options)
                                ->required()
                                ->native(false),

                            \Filament\Forms\Components\Textarea::make('comment')
                                ->label('Comentario')
                                ->placeholder('Agregue un comentario sobre esta transición...')
                                ->helperText('Este comentario quedará registrado en el historial de aprobaciones')
                                ->rows(3)
                                ->columnSpanFull(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        if (! isset($data['new_state'])) {
                            return;
                        }

                        $stateClass = $data['new_state'];

                        // Get the transition information for proper messaging
                        $availableTransitions = static::getAvailableTransitionsForRecord($record);
                        $transitionData = null;

                        foreach ($availableTransitions as $transition) {
                            if ($transition['state_class'] === $stateClass) {
                                $transitionData = $transition;
                                break;
                            }
                        }

                        if (! $transitionData) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body('Transición no válida o no permitida')
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            // Get current state before transition for logging
                            $currentStateName = static::getStateNameFromClass(get_class($record->state));
                            $currentState = \App\Models\ApprovalState::where('model_type', 'App\\Models\\Documentation')
                                ->where('name', $currentStateName)
                                ->first();

                            // Execute the state transition
                            $record->state->transitionTo($stateClass);
                            $record->refresh();

                            // Create log entry for the transition
                            if ($currentState && $transitionData['target_state']) {
                                StateTransitionLog::createLog(
                                    $record,
                                    $transitionData['transition'],
                                    $currentState,
                                    $transitionData['target_state'],
                                    $data['comment'] ?? null
                                );
                            }

                            // Use dynamic success message from StateTransition
                            $successMessage = $transitionData['transition']->getSuccessMessage();

                            \Filament\Notifications\Notification::make()
                                ->title('Estado actualizado correctamente')
                                ->body($successMessage)
                                ->success()
                                ->duration(5000)
                                ->send();

                        } catch (\Exception $e) {
                            // Use dynamic failure message from StateTransition
                            $failureMessage = $transitionData['transition']->getFailureMessage();

                            \Filament\Notifications\Notification::make()
                                ->title('Error al cambiar estado')
                                ->body($failureMessage.' Error: '.$e->getMessage())
                                ->danger()
                                ->duration(7000)
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Cambiar Estado del Documento')
                    ->modalDescription('¿Está seguro que desea cambiar el estado de este documento?')
                    ->modalSubmitActionLabel('Sí, cambiar estado'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Documentos Eliminados')
                                ->body('Los documentos seleccionados han sido eliminados exitosamente.')
                                ->duration(5000)
                        )
                        ->modalHeading('Eliminar Documentos Seleccionados')
                        ->modalDescription('¿Está seguro que desea eliminar los documentos seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar documentos'),

                    // Acción bulk para cambiar estado a borrador
                    Tables\Actions\BulkAction::make('mark_as_draft')
                        ->label('Marcar como Borrador')
                        ->icon('heroicon-o-document-text')
                        ->color('warning')
                        ->action(function ($records) {
                            $updated = 0;
                            foreach ($records as $record) {
                                if ($record->status !== 'draft') {
                                    $record->update(['status' => 'draft']);
                                    $updated++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title('Estados Actualizados')
                                ->body("Se marcaron {$updated} documentos como borrador.")
                                ->duration(5000)
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Marcar como Borrador')
                        ->modalDescription('¿Está seguro que desea marcar los documentos seleccionados como borrador?')
                        ->modalSubmitActionLabel('Sí, marcar como borrador'),

                    // Acción bulk para archivar documentos
                    Tables\Actions\BulkAction::make('archive')
                        ->label('Archivar')
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->action(function ($records) {
                            $updated = 0;
                            foreach ($records as $record) {
                                if ($record->status !== 'archived') {
                                    $record->update(['status' => 'archived']);
                                    $updated++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title('Documentos Archivados')
                                ->body("Se archivaron {$updated} documentos.")
                                ->duration(5000)
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Archivar Documentos')
                        ->modalDescription('¿Está seguro que desea archivar los documentos seleccionados?')
                        ->modalSubmitActionLabel('Sí, archivar documentos'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StateTransitionLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentations::route('/'),
            'create' => Pages\CreateDocumentation::route('/create'),
            'view' => Pages\ViewDocumentation::route('/{record}'),
            'edit' => Pages\EditDocumentation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['creator', 'approver'])
            ->latest('created_at');
    }

    // Métodos de autorización para acciones bulk
    public static function canDelete(Model $record): bool
    {
        return true; // Permitir eliminación por defecto
    }

    public static function canDeleteAny(): bool
    {
        return true; // Permitir eliminación bulk
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    /**
     * Convert state class name to database state name
     * Example: App\States\PendingSupervisorState -> pending_supervisor
     */
    public static function getStateNameFromClass(string $stateClass): string
    {
        // Extract class name (e.g., "PendingSupervisorState")
        $className = class_basename($stateClass);

        // Handle special mappings first
        $specialMappings = [
            'PendingApprovalState' => 'pending_supervisor',
            'PendingSupervisorState' => 'pending_supervisor',
            'ApprovedSupervisorPendingTravelState' => 'approved_supervisor_pending_travel',
            'ApprovedTravelPendingTreasuryState' => 'approved_travel_pending_treasury',
            'FullyApprovedState' => 'fully_approved',
            'RejectedState' => 'rejected',
            'DraftState' => 'draft',
        ];

        if (isset($specialMappings[$className])) {
            return $specialMappings[$className];
        }

        // Fallback: Remove "State" suffix and convert PascalCase to snake_case
        $withoutSuffix = str_replace('State', '', $className);
        $snakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $withoutSuffix));

        return $snakeCase;
    }

    /**
     * Convert database state name to state class name (inverse of getStateNameFromClass)
     * Example: pending_supervisor -> App\States\PendingApprovalState
     */
    protected static function getStateClassFromName(string $stateName): ?string
    {
        $stateClassMappings = [
            'draft' => 'App\\States\\DraftState',
            'pending_supervisor' => 'App\\States\\PendingApprovalState',
            'approved_supervisor_pending_travel' => 'App\\States\\ApprovedSupervisorPendingTravelState',
            'approved_travel_pending_treasury' => 'App\\States\\ApprovedTravelPendingTreasuryState',
            'fully_approved' => 'App\\States\\FullyApprovedState',
            'rejected' => 'App\\States\\RejectedState',
        ];

        return $stateClassMappings[$stateName] ?? null;
    }

    /**
     * Check if user has permission for any transition on this record
     */
    protected static function hasPermissionForAnyTransition($record): bool
    {
        if (! isset($record->state) || ! $record->state instanceof \Spatie\ModelStates\State) {
            return false;
        }

        return ! empty(static::getAvailableTransitionsForRecord($record));
    }

    /**
     * Get available transitions for a record using dynamic StateTransition system
     */
    protected static function getAvailableTransitionsForRecord($record): array
    {
        // Get current state from database
        $currentStateName = static::getStateNameFromClass(get_class($record->state));
        $currentState = \App\Models\ApprovalState::where('model_type', 'App\\Models\\Documentation')
            ->where('name', $currentStateName)
            ->first();

        if (! $currentState) {
            return [];
        }

        // Get available transitions using StateTransition system
        $transitions = \App\Models\StateTransition::getAvailableTransitions($currentState, auth()->user(), $record);

        $result = [];
        foreach ($transitions as $transition) {
            // Additional check: only creator can submit from draft
            if ($currentStateName === 'draft' && $transition->toState->name === 'pending_supervisor') {
                if ($record->created_by !== auth()->id()) {
                    continue; // Skip this transition for non-creators
                }
            }

            // Map database state back to state class
            $stateClass = static::getStateClassFromName($transition->toState->name);
            if ($stateClass) {
                $result[] = [
                    'state_class' => $stateClass,
                    'transition' => $transition,
                    'target_state' => $transition->toState,
                ];
            }
        }

        return $result;
    }

    /**
     * Check if current user can perform specific transition (now using dynamic system)
     */
    protected static function canUserPerformTransition($record, string $stateClass): bool
    {
        $availableTransitions = static::getAvailableTransitionsForRecord($record);

        foreach ($availableTransitions as $transitionData) {
            if ($transitionData['state_class'] === $stateClass) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkflowWizardResource\Pages;
use App\Models\AdvancedWorkflow;
use App\Services\ModelIntrospectionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WorkflowWizardResource extends Resource
{
    protected static ?string $model = AdvancedWorkflow::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Automatización';

    protected static ?string $navigationLabel = 'Asistente de Workflows';

    protected static ?string $modelLabel = 'Workflow';

    protected static ?string $pluralModelLabel = 'Workflows';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('¿Qué quieres automatizar?')
                        ->icon('heroicon-o-light-bulb')
                        ->schema([
                            Forms\Components\Section::make('Elige el tipo de automatización')
                                ->description('Selecciona qué tipo de acción quieres que se ejecute automáticamente')
                                ->icon('heroicon-o-document-text')
                                ->schema([
                                    Forms\Components\Radio::make('automation_type')
                                        ->label('')
                                        ->options([
                                            'notify_email' => 'Notificar por email',
                                            'interact_records' => 'Interactuar con registros',
                                        ])
                                        ->descriptions([
                                            'notify_email' => 'Envía emails automáticos cuando ocurren eventos específicos (bienvenidas, alertas, confirmaciones, recordatorios)',
                                            'interact_records' => 'Modifica, crea o actualiza registros automáticamente cuando se cumplen ciertas condiciones',
                                        ])
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // Pre-configurar según tipo de automatización
                                            match ($state) {
                                                'notify_email' => [
                                                    $set('name', 'Notificación Automática'),
                                                    $set('workflow_template', 'email_notification'),
                                                ],
                                                'interact_records' => [
                                                    $set('name', 'Automatización de Registros'),
                                                    $set('workflow_template', 'record_interaction'),
                                                ],
                                                default => null,
                                            };
                                        })
                                        ->columns(1),
                                ]),

                            Forms\Components\Section::make('Personalización básica')
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nombre del workflow')
                                        ->required()
                                        ->helperText('Un nombre descriptivo para identificar este workflow')
                                        ->placeholder(fn (callable $get) => match ($get('automation_type')) {
                                            'notify_email' => 'Ej: Notificar nuevos usuarios',
                                            'interact_records' => 'Ej: Actualizar estado automáticamente',
                                            default => 'Ej: Mi workflow automatizado'
                                        }),
                                ])
                                ->visible(fn (callable $get) => $get('automation_type')),
                        ]),

                    Forms\Components\Wizard\Step::make('¿Cuándo debe ejecutarse?')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            Forms\Components\Section::make('Selecciona el módulo')
                                ->description('¿Qué módulo del sistema quieres automatizar?')
                                ->schema([
                                    Forms\Components\Select::make('target_model')
                                        ->label('Selecciona el módulo que vas a automatizar')
                                        ->required()
                                        ->options(function () {
                                            $introspectionService = app(ModelIntrospectionService::class);
                                            $models = $introspectionService->getAvailableModels();

                                            $options = [];
                                            foreach ($models as $model) {
                                                // Nombres más amigables
                                                $friendlyName = match ($model['class']) {
                                                    'App\\Models\\User' => 'Usuarios',
                                                    'App\\Models\\Order' => 'Pedidos',
                                                    'App\\Models\\Task' => 'Tareas',
                                                    'App\\Models\\Backup' => 'Backups',
                                                    default => $model['display_name']
                                                };
                                                $options[$model['class']] = $friendlyName;
                                            }

                                            return $options;
                                        })
                                        ->searchable()
                                        ->reactive(),
                                ]),

                            Forms\Components\Section::make('¿Cuándo debe activarse?')
                                ->schema([
                                    Forms\Components\Radio::make('trigger_event')
                                        ->label('Evento disparador')
                                        ->required()
                                        ->options(function (callable $get) {
                                            $targetModel = $get('target_model');
                                            $automationType = $get('automation_type');

                                            if ($automationType) {
                                                return static::getContextualEventsForAutomationType($automationType, $targetModel);
                                            }

                                            if (! $targetModel) {
                                                return [
                                                    'created' => 'Cuando se crea un registro',
                                                    'updated' => 'Cuando se actualiza un registro',
                                                    'deleted' => 'Cuando se elimina un registro',
                                                ];
                                            }

                                            $events = static::getAvailableEventsForModel($targetModel);
                                            $friendlyEvents = [];

                                            foreach ($events as $key => $label) {
                                                $friendlyEvents[$key] = static::getFriendlyEventName($key, $targetModel);
                                            }

                                            return $friendlyEvents;
                                        })
                                        ->reactive()
                                        ->columns(1),
                                ])
                                ->visible(fn (callable $get) => $get('target_model')) ,
                        ]),

                    Forms\Components\Wizard\Step::make('¿A quién notificar?')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Forms\Components\Section::make('Destinatarios del email')
                                ->schema([
                                    Forms\Components\CheckboxList::make('notification_recipients')
                                        ->label('¿Quién debe recibir la notificación?')
                                        ->options([
                                            'creator' => 'Al creador del registro',
                                            'admin' => 'Al administrador del sistema',
                                            'assigned' => 'Al usuario asignado',
                                            'team' => 'Al equipo completo',
                                            'custom' => 'Emails específicos',
                                        ])
                                        ->descriptions([
                                            'creator' => 'La persona que creó el registro',
                                            'admin' => 'Usuarios con rol de administrador',
                                            'assigned' => 'Usuario asignado al registro (si aplica)',
                                            'team' => 'Todos los miembros del equipo',
                                            'custom' => 'Direcciones de email específicas',
                                        ])
                                        ->reactive()
                                        ->columns(1),

                                    Forms\Components\TagsInput::make('custom_emails')
                                        ->label('Emails específicos')
                                        ->helperText('Ingresa las direcciones de email separadas por comas')
                                        ->placeholder('admin@empresa.com, soporte@empresa.com')
                                        ->visible(fn (callable $get) => in_array('custom', $get('notification_recipients') ?? [])),
                            ]),
                    ]),

                Forms\Components\Wizard\Step::make('Selecciona el Mensaje')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Forms\Components\Section::make('Selecciona una plantilla de email')
                            ->description('Elige la plantilla de email que se enviará cuando este workflow se active.')
                            ->schema([
                                Forms\Components\Select::make('existing_template_key')
                                    ->label('Plantilla de Email')
                                    ->options(function () {
                                        return \App\Models\EmailTemplate::where('is_active', true)
                                            ->pluck('name', 'key')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $template = \App\Models\EmailTemplate::where('key', $state)->first();
                                            if ($template) {
                                                $set('email_subject', $template->subject);
                                                $originalContent = $template->metadata['original_content'] ?? strip_tags($template->content);
                                                $set('email_content', $originalContent);
                                            }
                                        }
                                    })
                                    ->helperText('Las plantillas se gestionan desde la sección "Templates de Email".')
                                    ->placeholder('Selecciona una plantilla...'),

                                Forms\Components\Placeholder::make('template_preview')
                                    ->label('Vista previa de la plantilla')
                                    ->content(function (callable $get) {
                                        $templateKey = $get('existing_template_key');
                                        if (!$templateKey) {
                                            return 'Selecciona una plantilla para ver la vista previa.';
                                        }
                                        $template = \App\Models\EmailTemplate::where('key', $templateKey)->first();
                                        if (!$template) {
                                            return 'Plantilla no encontrada.';
                                        }
                                        $originalContent = $template->metadata['original_content'] ?? strip_tags($template->content);
                                        
                                        $name = htmlspecialchars($template->name);
                                        $subject = htmlspecialchars($template->subject);
                                        $limitedContent = htmlspecialchars(Str::limit($originalContent, 200));

                                        return new \Illuminate\Support\HtmlString(<<<HTML
                                            <div class='bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border'>
                                                <h4 class='font-medium text-gray-900 dark:text-gray-100 mb-2'>{$name}</h4>
                                                <p class='text-sm text-gray-600 dark:text-gray-400 mb-2'>
                                                    <strong>Asunto:</strong> {$subject}
                                                </p>
                                                <div class='text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 p-3 rounded border'>
                                                    {$limitedContent}
                                                </div>
                                            </div>
                                        HTML);
                                    })
                                    ->reactive(),
                            ]),
                    ]),

                Forms\Components\Wizard\Step::make('Vista previa y configuración')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Forms\Components\Section::make('Vista previa del email')
                            ->schema([
                                Forms\Components\Placeholder::make('email_preview')
                                    ->label('')
                                    ->content(function (callable $get) {
                                        $subject = $get('email_subject') ?? 'Asunto del email';
                                        $content = $get('email_content') ?? 'Contenido del email';
                                        $previewSubject = str_replace(['{{nombre}}', '{{app_name}}', '{{email}}'], ['Juan Pérez', 'Mi Aplicación', 'juan@ejemplo.com'], $subject);
                                        $previewContent = str_replace(['{{nombre}}', '{{app_name}}', '{{email}}'], ['Juan Pérez', 'Mi Aplicación', 'juan@ejemplo.com'], $content);
                                        $bgColor = 'bg-gray-50';
                                        $borderColor = 'border-gray-200';
                                        $html = '<div class="'.$bgColor.' border '.$borderColor.' rounded-lg p-6">';
                                        $html .= '<div class="mb-4"><div class="flex items-center gap-2 mb-2"><span class="font-semibold">Vista Previa del Email</span></div>';
                                        $html .= '<div class="text-sm text-gray-600"><strong>Para:</strong> juan@ejemplo.com<br><strong>Asunto:</strong> '.htmlspecialchars($previewSubject).'</div></div>';
                                        $html .= '<div class="bg-white border rounded p-4 shadow-sm"><div class="prose prose-sm max-w-none">'.nl2br(htmlspecialchars($previewContent)).'</div></div>';
                                        $html .= '</div>';
                                        return new \Illuminate\Support\HtmlString($html);
                                    })
                                    ->reactive(),
                            ]),

                        Forms\Components\Section::make('Configuración final')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activar workflow inmediatamente')
                                    ->helperText('Si está desactivado, podrás activarlo más tarde')
                                    ->default(true)
                                    ->inline(false),
                                Forms\Components\Textarea::make('description')
                                    ->label('Notas (opcional)')
                                    ->helperText('Describe qué hace este workflow para referencia futura')
                                    ->rows(2),
                            ]),
                    ]),
            ])
                ->columnSpanFull()
                ->persistStepInQueryString()
                ->submitAction(new \Illuminate\Support\HtmlString(
                '<x-filament::button type="submit" size="lg" color="success">
                    <x-heroicon-o-sparkles class="w-5 h-5 mr-2"/>
                    Crear Workflow
                </x-filament::button>'
            )),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Workflow')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('target_model')
                    ->label('Monitorea')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'App\\Models\\User' => 'Usuarios',
                        'App\\Models\\Order' => 'Pedidos',
                        'App\\Models\\Task' => 'Tareas',
                        'App\\Models\\Backup' => 'Backups',
                        default => class_basename($state)
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('stepDefinitions_count')
                    ->label('Acciones')
                    ->counts('stepDefinitions')
                    ->badge()
                    ->color('success')
                    ->suffix(' pasos'),

                Tables\Columns\TextColumn::make('executions_count')
                    ->label('Ejecutado')
                    ->counts('executions')
                    ->badge()
                    ->color('warning')
                    ->suffix(' veces'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo')
                    ->onColor('success')
                    ->offColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('target_model')
                    ->label('Tipo')
                    ->options([
                        'App\\Models\\User' => 'Usuarios',
                        'App\\Models\\Order' => 'Pedidos',
                        'App\\Models\\Task' => 'Tareas',
                        'App\\Models\\Backup' => 'Backups',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (AdvancedWorkflow $record) {
                        $newWorkflow = $record->replicate();
                        $newWorkflow->name = $record->name.' (Copia)';
                        $newWorkflow->is_active = false;
                        $newWorkflow->version = 1;
                        $newWorkflow->save();

                        return redirect()->to(static::getUrl('edit', ['record' => $newWorkflow->id]));
                    }),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-sparkles')
            ->emptyStateHeading('No hay workflows configurados')
            ->emptyStateDescription('Crea tu primer workflow automático para ahorrar tiempo')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear primer workflow')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkflowWizards::route('/'),
            'create' => Pages\CreateWorkflowWizard::route('/create'),
            'edit' => Pages\EditWorkflowWizard::route('/{record}/edit'),
        ];
    }

    /**
     * Obtener eventos disponibles para un modelo de forma user-friendly
     */
    protected static function getAvailableEventsForModel(string $modelClass): array
    {
        // Eventos básicos
        $events = [
            'created' => 'Creación de registro',
            'updated' => 'Actualización de registro',
            'deleted' => 'Eliminación de registro',
        ];

        try {
            // Verificar si usa Spatie Model States
            $usesSpatieStates = in_array('Spatie\\ModelStates\\HasStates', class_uses_recursive($modelClass));

            if ($usesSpatieStates) {
                $events['state_changed'] = 'Cambio de Estado';
            } else {
                // Verificar campos de estado simples
                try {
                    $introspectionService = app(\App\Services\ModelIntrospectionService::class);
                    $modelInfo = $introspectionService->getModelInfo($modelClass);
                    $fields = $modelInfo['fields'] ?? [];

                    foreach (['status', 'state', 'stage', 'phase'] as $statusField) {
                        if (isset($fields[$statusField])) {
                            $events['status_changed'] = 'Cambio de Estado';
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    // Continuar sin eventos de estado
                }
            }
        } catch (\Exception $e) {
            // Devolver solo eventos básicos si hay error
        }

        return $events;
    }

    /**
     * Obtener eventos contextuales basados en el tipo de automatización
     */
    protected static function getContextualEventsForAutomationType(string $automationType, ?string $targetModel = null): array
    {
        $modelName = $targetModel ? static::getModelFriendlyName($targetModel) : 'registro';
        $hasStates = $targetModel ? static::modelHasSpatieStates($targetModel) : false;

        return match ($automationType) {
            'notify_email' => $hasStates ? [
                'created' => "Cuando se crea un nuevo {$modelName}",
                'state_changed' => "Cuando {$modelName} cambia de estado (Recomendado - Sistema moderno)",
                'status_changed' => "Cuando {$modelName} cambia de estatus (Sistema legacy)",
                'updated' => "Cuando se actualiza cualquier campo del {$modelName}",
                'deleted' => "Cuando se elimina el {$modelName}",
            ] : [
                'created' => "Cuando se crea un nuevo {$modelName}",
                'updated' => "Cuando se actualiza el {$modelName}",
                'status_changed' => "Cuando {$modelName} cambia de estatus",
                'state_changed' => "Cuando {$modelName} cambia de estado",
                'deleted' => "Cuando se elimina el {$modelName}",
            ],
            'interact_records' => $hasStates ? [
                'state_changed' => "Cuando {$modelName} cambia de estado (Recomendado - Sistema moderno)",
                'created' => "Cuando se crea un nuevo {$modelName}",
                'updated' => "Cuando se actualiza el {$modelName}",
                'status_changed' => "Cuando {$modelName} cambia de estatus (Sistema legacy)",
            ] : [
                'created' => "Cuando se crea un nuevo {$modelName}",
                'updated' => "Cuando se actualiza el {$modelName}",
                'status_changed' => "Cuando {$modelName} cambia de estatus",
                'state_changed' => "Cuando {$modelName} cambia de estado",
            ],
            default => [
                'created' => 'Cuando se crea un registro',
                'updated' => 'Cuando se actualiza un registro',
                'deleted' => 'Cuando se elimina un registro',
            ],
        };
    }

    /**
     * Obtener nombre amigable para eventos específicos del modelo
     */
    protected static function getFriendlyEventName(string $event, string $targetModel):
    string
    {
        $modelName = static::getModelFriendlyName($targetModel);

        return match ($event) {
            'created' => "Cuando se crea un nuevo {$modelName}",
            'updated' => "Cuando se actualiza el {$modelName}",
            'deleted' => "Cuando se elimina el {$modelName}",
            'state_changed' => "Cuando {$modelName} cambia de estado",
            'status_changed' => "Cuando {$modelName} cambia de estatus",
            default => ucfirst($event)
        };
    }

    /**
     * Obtener nombre amigable del modelo
     */
    protected static function getModelFriendlyName(string $modelClass): string
    {
        return match ($modelClass) {
            'App\\Models\\User' => 'usuario',
            'App\\Models\\Order' => 'pedido',
            'App\\Models\\Task' => 'tarea',
            'App\\Models\\Backup' => 'backup',
            'App\\Models\\Product' => 'producto',
            'App\\Models\\Customer' => 'cliente',
            'App\\Models\\Invoice' => 'factura',
            'App\\Models\\Ticket' => 'ticket',
            'App\\Models\\Documentation' => 'documentación',
            default => strtolower(class_basename($modelClass))
        };
    }

    /**
     * Verificar si un modelo usa Spatie Model States
     */
    protected static function modelHasSpatieStates(string $modelClass): bool
    {
        try {
            if (! class_exists($modelClass)) {
                return false;
            }

            // Verificar si usa el trait HasStates
            $traits = class_uses_recursive($modelClass);

            return in_array('Spatie\\ModelStates\\HasStates', $traits);
        } catch (\Exception $e) {
            return false;
        }
    }
}
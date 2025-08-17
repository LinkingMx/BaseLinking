<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModelVariableMappingResource\Pages;
use App\Models\ModelVariableMapping;
use App\Services\ModelIntrospectionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ModelVariableMappingResource extends Resource
{
    protected static ?string $model = ModelVariableMapping::class;

    protected static ?string $navigationIcon = 'heroicon-o-variable';

    protected static ?string $navigationGroup = 'Automatización';

    protected static ?string $navigationLabel = 'Variables Avanzadas';

    protected static ?string $modelLabel = 'Variable de Modelo';

    protected static ?string $pluralModelLabel = 'Variables de Modelos';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->description('Configura el modelo y los datos básicos de la variable')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Select::make('model_class')
                            ->label('Modelo')
                            ->required()
                            ->options(function () {
                                $introspectionService = app(ModelIntrospectionService::class);
                                $models = $introspectionService->getAvailableModels();

                                $options = [];
                                foreach ($models as $model) {
                                    $options[$model['class']] = $model['display_name'];
                                }

                                return $options;
                            })
                            ->searchable()
                            ->reactive(),

                        Forms\Components\Select::make('suggested_variable')
                            ->label('Variable Sugerida')
                            ->options(function (callable $get) {
                                $modelClass = $get('model_class');
                                if (! $modelClass) {
                                    return [];
                                }

                                try {
                                    $introspectionService = app(ModelIntrospectionService::class);
                                    $modelInfo = $introspectionService->getModelInfo($modelClass);
                                    $variables = $modelInfo['available_variables'] ?? [];

                                    $options = [];
                                    foreach ($variables as $variable) {
                                        $key = $variable['key'];
                                        $description = $variable['description'];
                                        $category = $variable['category'] ?? 'other';

                                        $options[$key] = "{$key} - {$description}";
                                    }

                                    return $options;
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->placeholder('Buscar variable sugerida...')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('variable_key', $state);
                                    // Auto-generar nombre descriptivo
                                    $parts = explode('_', $state);
                                    $name = implode(' ', array_map('ucfirst', $parts));
                                    $set('variable_name', $name);
                                }
                            })
                            ->helperText('Selecciona de las variables disponibles o crea una personalizada'),

                        Forms\Components\TextInput::make('variable_key')
                            ->label('Clave de Variable (Manual)')
                            ->unique(ignoreRecord: true)
                            ->rules(['regex:/^[a-z0-9_]+$/'])
                            ->helperText('Solo si no encontraste la variable en las sugerencias')
                            ->reactive(),

                        Forms\Components\TextInput::make('variable_name')
                            ->label('Nombre Descriptivo')
                            ->required()
                            ->helperText('Nombre legible para la variable'),

                        Forms\Components\Select::make('data_type')
                            ->label('Tipo de Dato')
                            ->required()
                            ->options([
                                ModelVariableMapping::DATA_TYPE_STRING => 'Texto',
                                ModelVariableMapping::DATA_TYPE_INTEGER => 'Número entero',
                                ModelVariableMapping::DATA_TYPE_BOOLEAN => 'Verdadero/Falso',
                                ModelVariableMapping::DATA_TYPE_DATE => 'Fecha',
                                ModelVariableMapping::DATA_TYPE_DATETIME => 'Fecha y hora',
                                ModelVariableMapping::DATA_TYPE_ARRAY => 'Lista',
                                ModelVariableMapping::DATA_TYPE_OBJECT => 'Objeto',
                            ])
                            ->default(ModelVariableMapping::DATA_TYPE_STRING),

                        Forms\Components\Select::make('category')
                            ->label('Categoría')
                            ->required()
                            ->options([
                                ModelVariableMapping::CATEGORY_CUSTOM => 'Personalizada',
                                ModelVariableMapping::CATEGORY_COMPUTED => 'Computada',
                                ModelVariableMapping::CATEGORY_RELATION => 'Relación',
                                ModelVariableMapping::CATEGORY_AGGREGATED => 'Agregada',
                                ModelVariableMapping::CATEGORY_CONDITIONAL => 'Condicional',
                            ])
                            ->default(ModelVariableMapping::CATEGORY_CUSTOM)
                            ->reactive(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuración de Mapeo')
                    ->description('Define cómo se obtiene el valor de la variable')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Select::make('mapping_config.type')
                            ->label('Tipo de Mapeo')
                            ->required()
                            ->options([
                                ModelVariableMapping::MAPPING_TYPE_FIELD => 'Campo directo',
                                ModelVariableMapping::MAPPING_TYPE_RELATION_FIELD => 'Campo de relación',
                                ModelVariableMapping::MAPPING_TYPE_METHOD => 'Método del modelo',
                                ModelVariableMapping::MAPPING_TYPE_COMPUTED => 'Valor computado',
                                ModelVariableMapping::MAPPING_TYPE_CONDITION => 'Valor condicional',
                            ])
                            ->reactive(),

                        // Campo directo
                        Forms\Components\TextInput::make('mapping_config.field')
                            ->label('Nombre del Campo')
                            ->required()
                            ->visible(fn (callable $get) => $get('mapping_config.type') === ModelVariableMapping::MAPPING_TYPE_FIELD),

                        // Campo de relación
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('mapping_config.relation')
                                ->label('Relación')
                                ->required()
                                ->helperText('Ej: creator, creator.department (para relaciones anidadas)'),

                            Forms\Components\TextInput::make('mapping_config.field')
                                ->label('Campo de la Relación')
                                ->required(),
                        ])
                            ->visible(fn (callable $get) => $get('mapping_config.type') === ModelVariableMapping::MAPPING_TYPE_RELATION_FIELD)
                            ->columns(2),

                        // Método del modelo
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('mapping_config.method')
                                ->label('Nombre del Método')
                                ->required(),

                            Forms\Components\KeyValue::make('mapping_config.parameters')
                                ->label('Parámetros')
                                ->keyLabel('Parámetro')
                                ->valueLabel('Valor'),
                        ])
                            ->visible(fn (callable $get) => $get('mapping_config.type') === ModelVariableMapping::MAPPING_TYPE_METHOD),

                        // Valor computado
                        Forms\Components\Group::make([
                            Forms\Components\Select::make('mapping_config.computation')
                                ->label('Tipo de Computación')
                                ->options([
                                    'count_relation' => 'Contar relación',
                                    'concat_fields' => 'Concatenar campos',
                                    'conditional_value' => 'Valor condicional',
                                    'format_date' => 'Formatear fecha',
                                    'calculate_age' => 'Calcular edad',
                                ])
                                ->reactive(),

                            // Configuración específica para cada tipo de computación
                            Forms\Components\TextInput::make('mapping_config.relation')
                                ->label('Relación a Contar')
                                ->visible(fn (callable $get) => $get('mapping_config.computation') === 'count_relation'),

                            Forms\Components\TagsInput::make('mapping_config.fields')
                                ->label('Campos a Concatenar')
                                ->visible(fn (callable $get) => $get('mapping_config.computation') === 'concat_fields'),

                            Forms\Components\TextInput::make('mapping_config.separator')
                                ->label('Separador')
                                ->default(' ')
                                ->visible(fn (callable $get) => $get('mapping_config.computation') === 'concat_fields'),

                            Forms\Components\TextInput::make('mapping_config.field')
                                ->label('Campo de Fecha')
                                ->visible(fn (callable $get) => in_array($get('mapping_config.computation'), ['format_date', 'calculate_age'])),

                            Forms\Components\TextInput::make('mapping_config.format')
                                ->label('Formato de Fecha')
                                ->default('d/m/Y H:i')
                                ->visible(fn (callable $get) => $get('mapping_config.computation') === 'format_date'),
                        ])
                            ->visible(fn (callable $get) => $get('mapping_config.type') === ModelVariableMapping::MAPPING_TYPE_COMPUTED),

                        // Valor condicional
                        Forms\Components\Repeater::make('mapping_config.conditions')
                            ->label('Condiciones')
                            ->schema([
                                Forms\Components\TextInput::make('field')
                                    ->label('Campo')
                                    ->placeholder('Ej: title, status, name')
                                    ->required(),

                                Forms\Components\Select::make('operator')
                                    ->label('Operador')
                                    ->options([
                                        '=' => 'Igual a',
                                        '!=' => 'Diferente de',
                                        '>' => 'Mayor que',
                                        '<' => 'Menor que',
                                        'contains' => 'Contiene texto',
                                        'not_contains' => 'No contiene texto',
                                        'in' => 'Está en lista',
                                        'not_null' => 'No es nulo',
                                        'is_null' => 'Es nulo',
                                    ])
                                    ->required()
                                    ->searchable(),

                                Forms\Components\TextInput::make('value')
                                    ->label('Valor a Comparar')
                                    ->placeholder('Ej: Revisar, activo, 25')
                                    ->helperText('Para "contains": texto a buscar'),

                                Forms\Components\TextInput::make('return')
                                    ->label('Valor a Retornar')
                                    ->placeholder('Ej: requiere revisión')
                                    ->helperText('Texto que se mostrará cuando se cumpla esta condición')
                                    ->required(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Añadir condición')
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('Eliminar condición')
                                    ->modalDescription('¿Estás seguro de que quieres eliminar esta condición?')
                            )
                            ->visible(fn (callable $get) => $get('mapping_config.type') === ModelVariableMapping::MAPPING_TYPE_CONDITION),

                        Forms\Components\TextInput::make('mapping_config.default')
                            ->label('Valor por Defecto')
                            ->placeholder('Ej: documentación completa')
                            ->helperText('Valor que se mostrará cuando ninguna condición se cumpla')
                            ->visible(fn (callable $get) => $get('mapping_config.type') === ModelVariableMapping::MAPPING_TYPE_CONDITION),
                    ]),

                Forms\Components\Section::make('Preview de Variable')
                    ->description('Visualiza cómo se verá tu variable en tiempo real')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Forms\Components\Placeholder::make('variable_preview')
                            ->label('')
                            ->content(function (callable $get) {
                                $modelClass = $get('model_class');
                                $variableKey = $get('variable_key');
                                $mappingType = $get('mapping_config.type');

                                if (! $modelClass || ! $variableKey || ! $mappingType) {
                                    return 'Configura el modelo, variable y tipo de mapeo para ver un preview';
                                }

                                try {
                                    // Crear una instancia de ejemplo del modelo para testing
                                    $model = new $modelClass;
                                    $previewValue = static::generatePreviewValue($model, $get);
                                    
                                    $variableName = $get('variable_name') ?: 'Variable sin nombre';
                                    $category = $get('category') ?: 'custom';
                                    $dataType = $get('data_type') ?: 'string';

                                    $html = '<div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">';
                                    $html .= '<div class="flex items-start justify-between mb-4">';
                                    $html .= '<div class="flex items-center gap-3">';
                                    $html .= '<div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-lg flex items-center justify-center">';
                                    $html .= '<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>';
                                    $html .= '</div>';
                                    $html .= '<div>';
                                    $html .= '<h3 class="font-semibold text-gray-900 dark:text-gray-100">'.$variableName.'</h3>';
                                    $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">Preview en tiempo real</p>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= '<div class="text-xs bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded text-blue-700 dark:text-blue-300">'.ucfirst($category).'</div>';
                                    $html .= '</div>';
                                    
                                    $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">';
                                    $html .= '<div class="space-y-2">';
                                    $html .= '<label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sintaxis de Variable</label>';
                                    $html .= '<code class="block bg-gray-900 dark:bg-gray-800 text-green-400 px-3 py-2 rounded text-sm font-mono">{{'.$variableKey.'}}</code>';
                                    $html .= '</div>';
                                    $html .= '<div class="space-y-2">';
                                    $html .= '<label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tipo de Dato</label>';
                                    $html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">'.ucfirst($dataType).'</span>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    
                                    $html .= '<div class="space-y-2">';
                                    $html .= '<label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Valor de Ejemplo</label>';
                                    $html .= '<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3">';
                                    $html .= '<code class="text-sm text-gray-900 dark:text-gray-100 font-mono">'.htmlspecialchars($previewValue ?? 'null').'</code>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    
                                    $html .= '</div>';

                                    return new \Illuminate\Support\HtmlString($html);

                                } catch (\Exception $e) {
                                    $html = '<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">';
                                    $html .= '<div class="flex items-center gap-2">';
                                    $html .= '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                                    $html .= '<span class="font-medium text-red-800 dark:text-red-200">Error generando preview</span>';
                                    $html .= '</div>';
                                    $html .= '<p class="mt-2 text-sm text-red-600 dark:text-red-400">'.$e->getMessage().'</p>';
                                    $html .= '</div>';
                                    return new \Illuminate\Support\HtmlString($html);
                                }
                            })
                            ->reactive(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Forms\Components\Section::make('Información Adicional')
                    ->description('Configuración adicional y metadatos de la variable')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->placeholder('Explica qué hace esta variable y cuándo usarla...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('example_value')
                            ->label('Valor de Ejemplo')
                            ->helperText('Ejemplo de valor que retornaría esta variable'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('model_class')
                    ->label('Modelo')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('variable_key')
                    ->label('Variable')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Variable copiada')
                    ->copyMessageDuration(1500)
                    ->formatStateUsing(fn (string $state): string => '{{'.$state.'}}')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('variable_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'gray',
                        'integer' => 'blue',
                        'boolean' => 'green',
                        'date', 'datetime' => 'yellow',
                        'array', 'object' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('mapping_config.type')
                    ->label('Mapeo')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'field' => 'Campo',
                        'relation_field' => 'Relación',
                        'method' => 'Método',
                        'computed' => 'Computado',
                        'condition' => 'Condicional',
                        default => $state ?? '-',
                    })
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('model_class')
                    ->label('Modelo')
                    ->options(function () {
                        $introspectionService = app(ModelIntrospectionService::class);
                        $models = $introspectionService->getAvailableModels();

                        $options = [];
                        foreach ($models as $model) {
                            $options[$model['class']] = $model['display_name'];
                        }

                        return $options;
                    }),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        ModelVariableMapping::CATEGORY_CUSTOM => 'Personalizada',
                        ModelVariableMapping::CATEGORY_COMPUTED => 'Computada',
                        ModelVariableMapping::CATEGORY_RELATION => 'Relación',
                        ModelVariableMapping::CATEGORY_AGGREGATED => 'Agregada',
                        ModelVariableMapping::CATEGORY_CONDITIONAL => 'Condicional',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('test')
                    ->label('Probar')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->action(function (ModelVariableMapping $record) {
                        // Aquí implementarías la lógica para probar la variable
                        // Por ahora solo mostramos información
                        \Filament\Notifications\Notification::make()
                            ->info()
                            ->title('Funcionalidad de prueba')
                            ->body('Esta función permitirá probar la variable con datos reales.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        }),
                ]),
            ])
            ->defaultSort('model_class')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModelVariableMappings::route('/'),
            'create' => Pages\CreateModelVariableMapping::route('/create'),
            'edit' => Pages\EditModelVariableMapping::route('/{record}/edit'),
            'generator' => Pages\VariableGenerator::route('/generator'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_model::variable::mapping') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_model::variable::mapping') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_model::variable::mapping') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_model::variable::mapping') ?? false;
    }

    /**
     * Genera un valor de preview para mostrar cómo funcionaría la variable
     */
    protected static function generatePreviewValue($model, callable $get): string
    {
        $mappingType = $get('mapping_config.type');
        $config = $get('mapping_config') ?? [];

        return match ($mappingType) {
            ModelVariableMapping::MAPPING_TYPE_FIELD => static::previewFieldValue($model, $config),
            ModelVariableMapping::MAPPING_TYPE_RELATION_FIELD => static::previewRelationFieldValue($model, $config),
            ModelVariableMapping::MAPPING_TYPE_METHOD => static::previewMethodValue($model, $config),
            ModelVariableMapping::MAPPING_TYPE_COMPUTED => static::previewComputedValue($model, $config),
            ModelVariableMapping::MAPPING_TYPE_CONDITION => static::previewConditionValue($model, $config),
            default => 'Tipo de mapeo desconocido'
        };
    }

    protected static function previewFieldValue($model, array $config): string
    {
        $field = $config['field'] ?? '';
        if (! $field) {
            return 'Campo no especificado';
        }

        // Generar valor de ejemplo basado en el nombre del campo
        return match (true) {
            str_contains($field, 'name') => 'Ejemplo Nombre',
            str_contains($field, 'email') => 'ejemplo@correo.com',
            str_contains($field, 'date') => now()->format('Y-m-d'),
            str_contains($field, 'status') => 'activo',
            str_contains($field, 'id') => '123',
            default => 'valor_ejemplo'
        };
    }

    protected static function previewRelationFieldValue($model, array $config): string
    {
        $relation = $config['relation'] ?? '';
        $field = $config['field'] ?? '';

        if (! $relation || ! $field) {
            return 'Relación o campo no especificado';
        }

        // Generar ejemplo basado en la relación y campo
        if (str_contains($relation, 'user') || str_contains($relation, 'creator') || str_contains($relation, 'editor')) {
            return match (true) {
                str_contains($field, 'name') => 'Juan Pérez',
                str_contains($field, 'email') => 'juan@empresa.com',
                str_contains($field, 'department') => 'Tecnología',
                default => 'valor_usuario'
            };
        }

        return 'valor_relacion_ejemplo';
    }

    protected static function previewMethodValue($model, array $config): string
    {
        $method = $config['method'] ?? '';
        if (! $method) {
            return 'Método no especificado';
        }

        return "resultado_de_{$method}()";
    }

    protected static function previewComputedValue($model, array $config): string
    {
        $computation = $config['computation'] ?? '';

        return match ($computation) {
            'count_relation' => '5',
            'concat_fields' => 'Campo1 Campo2',
            'conditional_value' => 'Valor condicional',
            'format_date' => now()->format('d/m/Y H:i'),
            'calculate_age' => '2 años',
            default => 'valor_computado'
        };
    }

    protected static function previewConditionValue($model, array $config): string
    {
        $conditions = $config['conditions'] ?? [];
        if (empty($conditions)) {
            return 'Sin condiciones definidas';
        }

        $firstCondition = $conditions[0] ?? [];

        return $firstCondition['return'] ?? 'valor_condicional';
    }
}

<?php

namespace App\Filament\Resources\ModelVariableMappingResource\Pages;

use App\Filament\Resources\ModelVariableMappingResource;
use App\Models\ModelVariableMapping;
use App\Services\ModelIntrospectionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class VariableGenerator extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use InteractsWithFormActions;

    protected static string $resource = ModelVariableMappingResource::class;

    protected static string $view = 'filament.pages.variable-generator';

    protected static ?string $title = 'Generador de Variables';

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Modelo')
                        ->schema([
                            Forms\Components\Select::make('model_class')
                                ->label('Selecciona el Modelo')
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
                                ->reactive()
                                ->helperText('Elige el modelo para el cual quieres crear variables personalizadas'),
                        ]),

                    Forms\Components\Wizard\Step::make('Tipo de Variable')
                        ->schema([
                            Forms\Components\ToggleButtons::make('variable_type')
                                ->label('¿Qué tipo de variable necesitas?')
                                ->required()
                                ->options([
                                    'simple_field' => 'Campo Simple',
                                    'user_info' => 'Información de Usuario',
                                    'relationship' => 'Relación',
                                    'formatted_date' => 'Fecha Formateada',
                                    'computed' => 'Valor Computado',
                                    'conditional' => 'Valor Condicional',
                                ])
                                ->icons([
                                    'simple_field' => 'heroicon-o-document-text',
                                    'user_info' => 'heroicon-o-user',
                                    'relationship' => 'heroicon-o-link',
                                    'formatted_date' => 'heroicon-o-calendar',
                                    'computed' => 'heroicon-o-calculator',
                                    'conditional' => 'heroicon-o-bolt',
                                ])
                                ->inline()
                                ->reactive()
                                ->helperText('Selecciona el tipo que mejor describe lo que necesitas'),
                        ]),

                    Forms\Components\Wizard\Step::make('Configuración')
                        ->schema([
                            // Campo Simple
                            Forms\Components\Select::make('simple_field')
                                ->label('Campo del Modelo')
                                ->options(function (callable $get) {
                                    $modelClass = $get('model_class');
                                    if (! $modelClass) {
                                        return [];
                                    }

                                    try {
                                        $introspectionService = app(ModelIntrospectionService::class);
                                        $modelInfo = $introspectionService->getModelInfo($modelClass);

                                        $options = [];
                                        foreach ($modelInfo['fields'] as $field => $info) {
                                            $options[$field] = ucfirst(str_replace('_', ' ', $field))." ({$field})";
                                        }

                                        return $options;
                                    } catch (\Exception $e) {
                                        return [];
                                    }
                                })
                                ->visible(fn (callable $get) => $get('variable_type') === 'simple_field')
                                ->searchable(),

                            // Información de Usuario
                            Forms\Components\Group::make([
                                Forms\Components\Select::make('user_relation')
                                    ->label('¿De qué usuario?')
                                    ->options([
                                        'creator' => 'Creador del registro',
                                        'editor' => 'Último editor',
                                        'assigned_user' => 'Usuario asignado',
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('user_field')
                                    ->label('¿Qué información del usuario?')
                                    ->options([
                                        'name' => 'Nombre completo',
                                        'email' => 'Correo electrónico',
                                        'department' => 'Departamento',
                                        'position' => 'Cargo/Posición',
                                    ])
                                    ->required(),
                            ])
                                ->visible(fn (callable $get) => $get('variable_type') === 'user_info')
                                ->columns(2),

                            // Relación
                            Forms\Components\Group::make([
                                Forms\Components\TextInput::make('relation_name')
                                    ->label('Nombre de la Relación')
                                    ->required()
                                    ->helperText('Ej: category, department, parent'),

                                Forms\Components\TextInput::make('relation_field')
                                    ->label('Campo de la Relación')
                                    ->required()
                                    ->helperText('Ej: name, title, description'),
                            ])
                                ->visible(fn (callable $get) => $get('variable_type') === 'relationship')
                                ->columns(2),

                            // Fecha Formateada
                            Forms\Components\Group::make([
                                Forms\Components\Select::make('date_field')
                                    ->label('Campo de Fecha')
                                    ->options(function (callable $get) {
                                        $modelClass = $get('model_class');
                                        if (! $modelClass) {
                                            return [];
                                        }

                                        try {
                                            $introspectionService = app(ModelIntrospectionService::class);
                                            $modelInfo = $introspectionService->getModelInfo($modelClass);

                                            $options = [];
                                            foreach ($modelInfo['fields'] as $field => $info) {
                                                if (in_array($info['type'], ['datetime', 'date', 'timestamp'])) {
                                                    $options[$field] = ucfirst(str_replace('_', ' ', $field));
                                                }
                                            }

                                            return $options;
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->required(),

                                Forms\Components\Select::make('date_format')
                                    ->label('Formato de Fecha')
                                    ->options([
                                        'd/m/Y' => '31/12/2024',
                                        'd/m/Y H:i' => '31/12/2024 14:30',
                                        'Y-m-d' => '2024-12-31',
                                        'F j, Y' => 'Diciembre 31, 2024',
                                        'j \d\e F \d\e Y' => '31 de Diciembre de 2024',
                                    ])
                                    ->required(),
                            ])
                                ->visible(fn (callable $get) => $get('variable_type') === 'formatted_date')
                                ->columns(2),

                            // Valor Computado
                            Forms\Components\Select::make('computation_type')
                                ->label('Tipo de Cálculo')
                                ->options([
                                    'count_relation' => 'Contar registros relacionados',
                                    'concat_fields' => 'Unir varios campos',
                                    'calculate_age' => 'Calcular tiempo transcurrido',
                                ])
                                ->visible(fn (callable $get) => $get('variable_type') === 'computed')
                                ->reactive(),
                        ]),

                    Forms\Components\Wizard\Step::make('Resultado')
                        ->schema([
                            Forms\Components\TextInput::make('generated_variable_key')
                                ->label('Nombre de Variable Generado')
                                ->disabled()
                                ->formatStateUsing(function (callable $get) {
                                    return $this->generateVariableKey($get);
                                }),

                            Forms\Components\TextInput::make('generated_variable_name')
                                ->label('Nombre Descriptivo')
                                ->formatStateUsing(function (callable $get) {
                                    return $this->generateVariableName($get);
                                }),

                            Forms\Components\Placeholder::make('preview')
                                ->label('Preview')
                                ->content(function (callable $get) {
                                    $key = $this->generateVariableKey($get);
                                    $example = $this->generateExampleValue($get);

                                    $html = '<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">';
                                    $html .= '<div class="flex items-center gap-2 mb-2">';
                                    $html .= '<span class="text-green-600 dark:text-green-400">✨</span>';
                                    $html .= '<span class="font-semibold text-green-800 dark:text-green-200">Variable Generada</span>';
                                    $html .= '</div>';
                                    $html .= '<div class="space-y-2">';
                                    $html .= '<div><code class="bg-green-100 dark:bg-green-800 px-2 py-1 rounded text-sm">{{'.$key.'}}</code></div>';
                                    $html .= '<div class="text-sm"><strong>Ejemplo:</strong> <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">'.htmlspecialchars($example).'</code></div>';
                                    $html .= '</div>';
                                    $html .= '</div>';

                                    return new \Illuminate\Support\HtmlString($html);
                                }),
                        ]),
                ])
                    ->submitAction(new \Illuminate\Support\HtmlString('<button type="submit" class="filament-button filament-button-size-md inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-3 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 filament-page-button-action">Crear Variable</button>')),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $variableKey = $this->generateVariableKey(fn ($key) => $data[$key] ?? null);
        $variableName = $this->generateVariableName(fn ($key) => $data[$key] ?? null);
        $mappingConfig = $this->generateMappingConfig(fn ($key) => $data[$key] ?? null);

        ModelVariableMapping::create([
            'model_class' => $data['model_class'],
            'variable_key' => $variableKey,
            'variable_name' => $variableName,
            'data_type' => 'string',
            'category' => ModelVariableMapping::CATEGORY_CUSTOM,
            'mapping_config' => $mappingConfig,
            'description' => $this->generateDescription(fn ($key) => $data[$key] ?? null),
            'example_value' => $this->generateExampleValue(fn ($key) => $data[$key] ?? null),
            'is_active' => true,
        ]);

        $this->redirect(ModelVariableMappingResource::getUrl('index'));
    }

    protected function generateVariableKey(callable $get): string
    {
        $type = $get('variable_type');

        return match ($type) {
            'simple_field' => $get('simple_field') ?: 'campo_simple',
            'user_info' => ($get('user_relation') ?: 'user').'_'.($get('user_field') ?: 'name'),
            'relationship' => ($get('relation_name') ?: 'relation').'_'.($get('relation_field') ?: 'name'),
            'formatted_date' => ($get('date_field') ?: 'date').'_formatted',
            'computed' => ($get('computation_type') ?: 'computed').'_value',
            'conditional' => 'conditional_value',
            default => 'custom_variable'
        };
    }

    protected function generateVariableName(callable $get): string
    {
        $type = $get('variable_type');

        return match ($type) {
            'simple_field' => ucfirst(str_replace('_', ' ', $get('simple_field') ?: 'Campo Simple')),
            'user_info' => ucfirst($get('user_relation') ?: 'Usuario').' - '.ucfirst($get('user_field') ?: 'Nombre'),
            'relationship' => ucfirst($get('relation_name') ?: 'Relación').' - '.ucfirst($get('relation_field') ?: 'Nombre'),
            'formatted_date' => ucfirst($get('date_field') ?: 'Fecha').' Formateada',
            'computed' => 'Valor '.ucfirst($get('computation_type') ?: 'Computado'),
            'conditional' => 'Valor Condicional',
            default => 'Variable Personalizada'
        };
    }

    protected function generateMappingConfig(callable $get): array
    {
        $type = $get('variable_type');

        return match ($type) {
            'simple_field' => [
                'type' => ModelVariableMapping::MAPPING_TYPE_FIELD,
                'field' => $get('simple_field'),
            ],
            'user_info' => [
                'type' => ModelVariableMapping::MAPPING_TYPE_RELATION_FIELD,
                'relation' => $get('user_relation'),
                'field' => $get('user_field'),
            ],
            'relationship' => [
                'type' => ModelVariableMapping::MAPPING_TYPE_RELATION_FIELD,
                'relation' => $get('relation_name'),
                'field' => $get('relation_field'),
            ],
            'formatted_date' => [
                'type' => ModelVariableMapping::MAPPING_TYPE_COMPUTED,
                'computation' => 'format_date',
                'field' => $get('date_field'),
                'format' => $get('date_format'),
            ],
            'computed' => [
                'type' => ModelVariableMapping::MAPPING_TYPE_COMPUTED,
                'computation' => $get('computation_type'),
            ],
            default => ['type' => ModelVariableMapping::MAPPING_TYPE_FIELD]
        };
    }

    protected function generateDescription(callable $get): string
    {
        $type = $get('variable_type');

        return match ($type) {
            'simple_field' => 'Campo '.($get('simple_field') ?: 'simple').' del modelo',
            'user_info' => ($get('user_field') ?: 'Información').' del '.($get('user_relation') ?: 'usuario'),
            'relationship' => ($get('relation_field') ?: 'Campo').' de la relación '.($get('relation_name') ?: 'relacionada'),
            'formatted_date' => 'Fecha '.($get('date_field') ?: 'del campo').' en formato '.($get('date_format') ?: 'personalizado'),
            'computed' => 'Valor computado usando '.($get('computation_type') ?: 'cálculo personalizado'),
            'conditional' => 'Valor que cambia según condiciones específicas',
            default => 'Variable personalizada generada automáticamente'
        };
    }

    protected function generateExampleValue(callable $get): string
    {
        $type = $get('variable_type');

        return match ($type) {
            'simple_field' => $this->getFieldExample($get('simple_field')),
            'user_info' => $this->getUserFieldExample($get('user_field')),
            'relationship' => 'Valor de relación',
            'formatted_date' => now()->format($get('date_format') ?: 'd/m/Y'),
            'computed' => 'Resultado computado',
            'conditional' => 'Valor condicional',
            default => 'Ejemplo'
        };
    }

    protected function getFieldExample(?string $field): string
    {
        if (! $field) {
            return 'valor';
        }

        return match (true) {
            str_contains($field, 'name') => 'Ejemplo Nombre',
            str_contains($field, 'email') => 'ejemplo@correo.com',
            str_contains($field, 'title') => 'Título de Ejemplo',
            str_contains($field, 'description') => 'Descripción de ejemplo',
            str_contains($field, 'status') => 'activo',
            str_contains($field, 'id') => '123',
            default => 'valor_ejemplo'
        };
    }

    protected function getUserFieldExample(?string $field): string
    {
        return match ($field) {
            'name' => 'Juan Pérez',
            'email' => 'juan@empresa.com',
            'department' => 'Tecnología',
            'position' => 'Desarrollador',
            default => 'valor_usuario'
        };
    }

    public function getTitle(): string|Htmlable
    {
        return 'Generador de Variables';
    }
}

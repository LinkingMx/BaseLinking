<?php

namespace App\Filament\Pages;

use App\Models\CustomStyle;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CssEditorPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket-square';
    protected static ?string $navigationLabel = 'Editor CSS';
    protected static ?string $title = 'Editor CSS Personalizado';
    protected static ?string $slug = 'css-editor';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Personalización';

    protected static string $view = 'filament.pages.css-editor-page';

    public ?int $selectedStyleId = null;
    public ?string $name = '';
    public ?string $description = '';
    public ?string $target = 'frontend';
    public ?string $css_content = '';
    public ?bool $is_active = false;
    public ?int $priority = 0;

    public function mount(): void
    {
        // Load first style if available
        $firstStyle = CustomStyle::first();
        if ($firstStyle) {
            $this->loadStyle($firstStyle->id);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Seleccionar o Crear Estilo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('selectedStyleId')
                                    ->label('Estilo Existente')
                                    ->placeholder('Seleccionar estilo para editar')
                                    ->options(CustomStyle::pluck('name', 'id')->toArray())
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        if ($state) {
                                            $this->loadStyle($state);
                                        }
                                    }),

                                Select::make('target')
                                    ->label('Objetivo')
                                    ->options(CustomStyle::getTargetOptions())
                                    ->required()
                                    ->live(),
                            ]),
                    ]),

                Section::make('Información del Estilo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre del Estilo')
                                    ->required()
                                    ->placeholder('Ej: Colores Personalizados Admin'),

                                TextInput::make('priority')
                                    ->label('Prioridad (Orden de Carga)')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Menor número = mayor prioridad'),
                            ]),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->placeholder('Describe qué hace este CSS...')
                            ->rows(2),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->helperText('Activar para aplicar estos estilos'),
                    ]),

                Section::make('Editor CSS')
                    ->schema([
                        Textarea::make('css_content')
                            ->label('Código CSS')
                            ->placeholder('/* Escribe tu CSS personalizado aquí */
.custom-class {
    color: #3b82f6;
    font-weight: 600;
}')
                            ->rows(20)
                            ->extraAttributes([
                                'style' => 'font-family: Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 14px;',
                                'spellcheck' => 'false',
                            ])
                            ->helperText('Usa CSS válido. Los cambios se aplicarán según el objetivo seleccionado.'),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('nuevo')
                ->label('Nuevo Estilo')
                ->icon('heroicon-o-plus')
                ->action(function () {
                    $this->resetForm();
                    Notification::make()
                        ->title('Formulario limpiado')
                        ->body('Puedes crear un nuevo estilo CSS.')
                        ->success()
                        ->send();
                }),

            Action::make('guardar')
                ->label('Guardar')
                ->icon('heroicon-o-bookmark')
                ->color('primary')
                ->action(function () {
                    $this->saveStyle();
                }),

            Action::make('aplicar')
                ->label('Guardar y Activar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $this->saveStyle(true);
                }),

            Action::make('preview')
                ->label('Vista Previa')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url('#', shouldOpenInNewTab: true)
                ->action(function () {
                    Notification::make()
                        ->title('Vista Previa')
                        ->body('Funcionalidad de vista previa en desarrollo.')
                        ->info()
                        ->send();
                }),
        ];
    }

    public function loadStyle(int $styleId): void
    {
        $style = CustomStyle::find($styleId);
        if ($style) {
            $this->selectedStyleId = $style->id;
            $this->name = $style->name;
            $this->description = $style->description;
            $this->target = $style->target;
            $this->css_content = $style->css_content;
            $this->is_active = $style->is_active;
            $this->priority = $style->priority;
        }
    }

    public function resetForm(): void
    {
        $this->selectedStyleId = null;
        $this->name = '';
        $this->description = '';
        $this->target = 'frontend';
        $this->css_content = '';
        $this->is_active = false;
        $this->priority = 0;
    }

    public function saveStyle(bool $activate = false): void
    {
        // Validación básica
        if (empty($this->name) || empty($this->css_content)) {
            Notification::make()
                ->title('Error de Validación')
                ->body('El nombre y el contenido CSS son requeridos.')
                ->danger()
                ->send();
            return;
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'target' => $this->target,
            'css_content' => $this->css_content,
            'is_active' => $activate ?: $this->is_active,
            'priority' => $this->priority ?: 0,
            'updated_by' => auth()->id(),
        ];

        if ($this->selectedStyleId) {
            // Actualizar estilo existente
            $style = CustomStyle::find($this->selectedStyleId);
            if ($style) {
                $style->createBackup(); // Crear backup antes de actualizar
                $style->update($data);
                $style->incrementVersion();
                
                Notification::make()
                    ->title('Estilo Actualizado')
                    ->body("El estilo '{$style->name}' ha sido actualizado" . ($activate ? ' y activado' : '') . '.')
                    ->success()
                    ->send();
            }
        } else {
            // Crear nuevo estilo
            $data['created_by'] = auth()->id();
            $style = CustomStyle::create($data);
            $this->selectedStyleId = $style->id;
            
            Notification::make()
                ->title('Estilo Creado')
                ->body("El estilo '{$style->name}' ha sido creado" . ($activate ? ' y activado' : '') . '.')
                ->success()
                ->send();
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Editor CSS Personalizado';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Editor CSS Personalizado';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Crea y edita estilos CSS personalizados para el frontend y el panel de administración.';
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalStateResource\Pages;
use App\Models\ApprovalState;
use App\Services\StateClassRegistry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApprovalStateResource extends Resource
{
    protected static ?string $model = ApprovalState::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Estados de Aprobación';

    protected static ?string $modelLabel = 'Estado de Aprobación';

    protected static ?string $pluralModelLabel = 'Estados de Aprobación';

    protected static ?string $navigationGroup = 'Automatización';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Select::make('model_type')
                            ->label('Modelo')
                            ->options([
                                'App\\Models\\Documentation' => 'Documentation',
                                'App\\Models\\User' => 'User',
                            ])
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nombre único del estado (ej: draft, pending_approval)'),

                        Forms\Components\TextInput::make('label')
                            ->label('Etiqueta')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nombre visible del estado (ej: Borrador, Pendiente de Aprobación)'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuración Visual')
                    ->schema([
                        Forms\Components\Select::make('color')
                            ->label('Color')
                            ->options([
                                'primary' => 'Primario',
                                'secondary' => 'Secundario',
                                'success' => 'Éxito',
                                'warning' => 'Advertencia',
                                'danger' => 'Peligro',
                                'info' => 'Información',
                                'gray' => 'Gris',
                            ])
                            ->default('gray'),

                        Forms\Components\Select::make('icon')
                            ->label('Icono')
                            ->options([
                                'heroicon-o-document-text' => 'Documento',
                                'heroicon-o-clock' => 'Reloj',
                                'heroicon-o-check-circle' => 'Verificado',
                                'heroicon-o-eye' => 'Visible',
                                'heroicon-o-x-circle' => 'Rechazado',
                                'heroicon-o-archive-box' => 'Archivado',
                                'heroicon-o-circle-stack' => 'Por defecto',
                            ])
                            ->default('heroicon-o-circle-stack'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Orden de aparición en listas'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Configuración de Comportamiento')
                    ->schema([
                        Forms\Components\Select::make('state_class')
                            ->label('Clase de Estado')
                            ->options(function () {
                                return app(StateClassRegistry::class)->getAvailableStateClasses();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Clase PHP que implementa la lógica específica de este estado. Si no se especifica, se intentará resolver automáticamente.')
                            ->rules([
                                fn () => function (string $attribute, $value, \Closure $fail) {
                                    if ($value && ! app(StateClassRegistry::class)->isValidStateClass($value)) {
                                        $fail('La clase de estado seleccionada no es válida.');
                                    }
                                },
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_initial')
                            ->label('Estado Inicial')
                            ->helperText('Este es el estado por defecto para nuevos registros'),

                        Forms\Components\Toggle::make('is_final')
                            ->label('Estado Final')
                            ->helperText('Este es un estado final del proceso'),

                        Forms\Components\Toggle::make('requires_approval')
                            ->label('Requiere Aprobación')
                            ->helperText('Requiere aprobación manual para salir de este estado'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('El estado está disponible para uso'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('label')
                    ->label('Estado')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('state_class')
                    ->label('Clase de Estado')
                    ->formatStateUsing(function (?string $state): string {
                        if (! $state) {
                            return 'Auto-resolver';
                        }

                        return class_basename($state);
                    })
                    ->tooltip(fn (?string $state): string => $state ?? 'Se resolverá automáticamente basado en convenciones')
                    ->color(fn (?string $state): string => $state ? 'success' : 'gray')
                    ->icon(fn (?string $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-cog-6-tooth')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_initial')
                    ->label('Inicial')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_final')
                    ->label('Final')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('requires_approval')
                    ->label('Req. Aprobación')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('model_type')
                    ->label('Modelo')
                    ->options([
                        'App\\Models\\Documentation' => 'Documentation',
                        'App\\Models\\User' => 'User',
                    ]),

                Tables\Filters\SelectFilter::make('state_class')
                    ->label('Clase de Estado')
                    ->options(function () {
                        $options = app(StateClassRegistry::class)->getAvailableStateClasses();

                        return array_merge(['__auto__' => 'Auto-resolver'], $options);
                    })
                    ->query(function ($query, array $data) {
                        if (isset($data['value'])) {
                            if ($data['value'] === '__auto__') {
                                return $query->whereNull('state_class');
                            }

                            return $query->where('state_class', $data['value']);
                        }

                        return $query;
                    }),

                Tables\Filters\TernaryFilter::make('is_initial')
                    ->label('Estado Inicial'),

                Tables\Filters\TernaryFilter::make('is_final')
                    ->label('Estado Final'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('model_type')
            ->defaultSort('sort_order');
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
            'index' => Pages\ListApprovalStates::route('/'),
            'create' => Pages\CreateApprovalState::route('/create'),
            'edit' => Pages\EditApprovalState::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any_approval::state') ?? false;
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManualResource\Pages;
use App\Models\ManualResource as ManualResourceModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ManualResource extends Resource
{
    protected static ?string $model = ManualResourceModel::class;
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'Recursos';
    protected static ?string $modelLabel = 'Recurso';
    protected static ?string $pluralModelLabel = 'Recursos';
    protected static bool $shouldRegisterNavigation = false; // No aparece en sidebar
    protected static ?string $navigationGroup = 'Configuración Manual';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Clave')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('UserResource, WorkflowWizardResource, etc.')
                            ->helperText('Identificador único para el recurso'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->placeholder('Gestión de Usuarios'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->placeholder('Descripción del recurso'),

                        Forms\Components\TextInput::make('class_name')
                            ->label('Nombre de Clase')
                            ->placeholder('App\Filament\Resources\UserResource')
                            ->helperText('Nombre completo de la clase del recurso'),

                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->placeholder('https://ejemplo.com/recurso')
                            ->helperText('URL si es un recurso externo'),

                        Forms\Components\TextInput::make('icon')
                            ->label('Icono')
                            ->placeholder('heroicon-o-users')
                            ->helperText('Nombre del icono Heroicon'),

                        Forms\Components\Select::make('color')
                            ->label('Color')
                            ->options(ManualResourceModel::getColorOptions())
                            ->default('primary'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Clave')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('class_name')
                    ->label('Clase')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),

                Tables\Columns\TextColumn::make('manualSections_count')
                    ->label('Secciones')
                    ->counts('manualSections'),
            ])
            ->filters([
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
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageManuals::route('/'),
        ];
    }
}

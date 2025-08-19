<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManualCategoryResource\Pages;
use App\Models\ManualCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ManualCategoryResource extends Resource
{
    protected static ?string $model = ManualCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Categorías';
    protected static ?string $modelLabel = 'Categoría';
    protected static ?string $pluralModelLabel = 'Categorías';
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
                            ->placeholder('usuarios, workflows, etc.')
                            ->helperText('Identificador único para la categoría'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->placeholder('Gestión de Usuarios'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->placeholder('Descripción de la categoría'),

                        Forms\Components\TextInput::make('icon')
                            ->label('Icono')
                            ->placeholder('heroicon-o-users')
                            ->helperText('Nombre del icono Heroicon'),

                        Forms\Components\Select::make('color')
                            ->label('Color')
                            ->options(ManualCategory::getColorOptions())
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
            'index' => Pages\ManageManualCategories::route('/'),
        ];
    }
}

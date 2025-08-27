<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomStyleResource\Pages;
use App\Models\CustomStyle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomStyleResource extends Resource
{
    protected static ?string $model = CustomStyle::class;
    protected static ?string $navigationIcon = 'heroicon-o-swatch';
    protected static ?string $navigationLabel = 'Gestión de Estilos';
    protected static ?string $modelLabel = 'Estilo CSS';
    protected static ?string $pluralModelLabel = 'Estilos CSS';
    protected static ?string $navigationGroup = 'Personalización';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->placeholder('Ej: Estilos Personalizados Admin'),

                                Forms\Components\Select::make('target')
                                    ->label('Objetivo')
                                    ->options(CustomStyle::getTargetOptions())
                                    ->required()
                                    ->default('frontend'),

                                Forms\Components\TextInput::make('priority')
                                    ->label('Prioridad')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Menor número = mayor prioridad'),

                                Forms\Components\TextInput::make('version')
                                    ->label('Versión')
                                    ->default('1.0.0')
                                    ->disabled(),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_minified')
                                    ->label('Minificado')
                                    ->default(false),
                            ]),
                    ]),

                Forms\Components\Section::make('Contenido CSS')
                    ->schema([
                        Forms\Components\Textarea::make('css_content')
                            ->label('Código CSS')
                            ->rows(15)
                            ->extraAttributes([
                                'style' => 'font-family: Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 14px;',
                                'spellcheck' => 'false',
                            ])
                            ->placeholder('/* Escribe tu CSS personalizado aquí */'),
                    ])
                    ->collapsible(),
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

                Tables\Columns\SelectColumn::make('target')
                    ->label('Objetivo')
                    ->options(CustomStyle::getTargetOptions())
                    ->selectablePlaceholder(false),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo'),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state < 5 => 'success',
                        $state < 10 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('version')
                    ->label('Versión')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('css_content')
                    ->label('Líneas de CSS')
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? (string) substr_count($state, "\n") + 1 . ' líneas' : '0 líneas'
                    )
                    ->color('gray'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('target')
                    ->label('Objetivo')
                    ->options(CustomStyle::getTargetOptions()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->actions([
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (CustomStyle $record) {
                        $newStyle = $record->replicate();
                        $newStyle->name = $record->name . ' (Copia)';
                        $newStyle->is_active = false;
                        $newStyle->created_by = auth()->id();
                        $newStyle->updated_by = auth()->id();
                        $newStyle->save();
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('backup')
                    ->label('Backup')
                    ->icon('heroicon-o-archive-box')
                    ->action(function (CustomStyle $record) {
                        $record->createBackup();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar Seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->color('success'),
                        
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar Seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('priority');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomStyles::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManualSectionResource\Pages;
use App\Models\ManualSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ManualSectionResource extends Resource
{
    protected static ?string $model = ManualSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Manual de Usuario';

    protected static ?string $modelLabel = 'Sección del Manual';

    protected static ?string $pluralModelLabel = 'Manual de Usuario';

    protected static ?string $navigationGroup = 'Documentación';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText('Breve descripción de qué cubre esta sección'),

                        Forms\Components\Select::make('category')
                            ->label('Categoría')
                            ->options(ManualSection::getCategories())
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('resource_related')
                            ->label('Recurso Relacionado')
                            ->options(ManualSection::getResourceOptions())
                            ->searchable()
                            ->preload()
                            ->helperText('Recurso de Filament al que se refiere esta sección'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contenido')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenido')
                            ->required()
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->helperText('Usa formato rich text para crear contenido atractivo'),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Organización y Configuración')
                    ->schema([
                        Forms\Components\Select::make('icon')
                            ->label('Icono')
                            ->options([
                                'heroicon-o-academic-cap' => 'Académico',
                                'heroicon-o-adjustments-horizontal' => 'Configuración',
                                'heroicon-o-archive-box' => 'Archivo',
                                'heroicon-o-arrow-right-circle' => 'Proceso',
                                'heroicon-o-book-open' => 'Libro',
                                'heroicon-o-circle-stack' => 'Estados',
                                'heroicon-o-cog-6-tooth' => 'Workflows',
                                'heroicon-o-document-text' => 'Documento',
                                'heroicon-o-envelope' => 'Email',
                                'heroicon-o-exclamation-triangle' => 'Importante',
                                'heroicon-o-eye' => 'Vista',
                                'heroicon-o-folder' => 'Categoría',
                                'heroicon-o-information-circle' => 'Información',
                                'heroicon-o-question-mark-circle' => 'FAQ',
                                'heroicon-o-shield-check' => 'Seguridad',
                                'heroicon-o-users' => 'Usuarios',
                                'heroicon-o-wrench-screwdriver' => 'Herramientas',
                            ])
                            ->default('heroicon-o-document-text')
                            ->searchable(),

                        Forms\Components\Select::make('difficulty_level')
                            ->label('Nivel de Dificultad')
                            ->options(ManualSection::getDifficultyLevels())
                            ->default('beginner')
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Orden de aparición en listados'),

                        Forms\Components\TagsInput::make('tags')
                            ->label('Etiquetas')
                            ->helperText('Palabras clave para mejorar la búsqueda')
                            ->placeholder('Agrega etiquetas...')
                            ->splitKeys(['Tab', ','])
                            ->reorderable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('La sección está visible para usuarios'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Destacado')
                            ->default(false)
                            ->helperText('Aparece en secciones destacadas del manual'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('icon')
                    ->label('')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->formatStateUsing(fn (string $state): string => ManualSection::getCategories()[$state] ?? $state)
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('resource_related')
                    ->label('Recurso')
                    ->formatStateUsing(fn (?string $state): string => $state ? (ManualSection::getResourceOptions()[$state] ?? $state) : '-')
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('difficulty_level')
                    ->label('Nivel')
                    ->formatStateUsing(fn (string $state): string => ManualSection::getDifficultyLevels()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('category')
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(ManualSection::getCategories())
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->label('Nivel de Dificultad')
                    ->options(ManualSection::getDifficultyLevels())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('resource_related')
                    ->label('Recurso Relacionado')
                    ->options(ManualSection::getResourceOptions())
                    ->multiple()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Destacado'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('feature')
                        ->label('Destacar')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('No hay secciones del manual')
            ->emptyStateDescription('Crea la primera sección del manual de usuario para comenzar.')
            ->emptyStateIcon('heroicon-o-book-open');
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
            'index' => Pages\ListManualSections::route('/'),
            'create' => Pages\CreateManualSection::route('/create'),
            'view' => Pages\ViewManualSection::route('/{record}'),
            'edit' => Pages\EditManualSection::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['creator', 'updater']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }

    // Métodos de autorización
    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return true;
    }

    public static function canDeleteAny(): bool
    {
        return true;
    }
}

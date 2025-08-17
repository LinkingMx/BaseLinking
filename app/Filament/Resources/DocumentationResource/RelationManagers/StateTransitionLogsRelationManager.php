<?php

namespace App\Filament\Resources\DocumentationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StateTransitionLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'stateTransitionLogs';

    protected static ?string $title = 'Historial de Aprobaciones';

    protected static ?string $modelLabel = 'Transición';

    protected static ?string $pluralModelLabel = 'Historial de Transiciones';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public function form(Form $form): Form
    {
        // Solo lectura - no permitir edición de logs
        return $form
            ->schema([
                Forms\Components\TextInput::make('user.name')
                    ->label('Usuario')
                    ->disabled(),

                Forms\Components\TextInput::make('fromState.label')
                    ->label('Estado Origen')
                    ->disabled(),

                Forms\Components\TextInput::make('toState.label')
                    ->label('Estado Destino')
                    ->disabled(),

                Forms\Components\Textarea::make('comment')
                    ->label('Comentario')
                    ->disabled()
                    ->rows(3),

                Forms\Components\TextInput::make('created_at')
                    ->label('Fecha')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->size('sm')
                    ->icon('heroicon-m-clock'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->weight('medium')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('fromState.label')
                    ->label('Desde')
                    ->badge()
                    ->color(fn ($record) => $record->fromState?->color ?? 'gray')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('toState.label')
                    ->label('Hacia')
                    ->badge()
                    ->color(fn ($record) => $record->toState?->color ?? 'gray')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentario')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->comment)
                    ->wrap()
                    ->color('gray')
                    ->icon('heroicon-m-chat-bubble-left-ellipsis')
                    ->placeholder('Sin comentario'),

                Tables\Columns\TextColumn::make('stateTransition.label')
                    ->label('Transición')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->icon('heroicon-m-arrow-right'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name'),

                Tables\Filters\SelectFilter::make('from_state_id')
                    ->label('Estado Origen')
                    ->relationship('fromState', 'label'),

                Tables\Filters\SelectFilter::make('to_state_id')
                    ->label('Estado Destino')
                    ->relationship('toState', 'label'),
            ])
            ->headerActions([
                // No permitir crear logs manualmente
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Detalles de la Transición'),
                // No permitir editar o eliminar logs
            ])
            ->bulkActions([
                // No permitir acciones masivas en logs
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->deferLoading()
            ->striped()
            ->emptyStateHeading('Sin historial de transiciones')
            ->emptyStateDescription('Este documento aún no tiene transiciones de estado registradas.')
            ->emptyStateIcon('heroicon-o-clock');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

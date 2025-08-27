<?php

namespace App\Filament\Resources\CustomStyleResource\Pages;

use App\Filament\Resources\CustomStyleResource;
use App\Models\CustomStyle;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomStyles extends ManageRecords
{
    protected static string $resource = CustomStyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();
                    $data['updated_by'] = auth()->id();
                    return $data;
                }),

            Actions\Action::make('clearCache')
                ->label('Limpiar Cache CSS')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    // Clear any CSS cache if needed
                    \Artisan::call('cache:clear');
                    
                    Notification::make()
                        ->title('Cache Limpiado')
                        ->body('El cache de CSS ha sido limpiado exitosamente.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('activateAll')
                ->label('Activar Todos (Admin)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    CustomStyle::where('target', 'admin')->update(['is_active' => true]);
                    
                    Notification::make()
                        ->title('Estilos Activados')
                        ->body('Todos los estilos de admin han sido activados.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation(),

            Actions\Action::make('deactivateAll')
                ->label('Desactivar Todos')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function () {
                    CustomStyle::query()->update(['is_active' => false]);
                    
                    Notification::make()
                        ->title('Estilos Desactivados')
                        ->body('Todos los estilos han sido desactivados.')
                        ->warning()
                        ->send();
                })
                ->requiresConfirmation(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ApprovalStateResource\Pages;

use App\Filament\Resources\ApprovalStateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditApprovalState extends EditRecord
{
    protected static string $resource = ApprovalStateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Estado de Aprobación Actualizado')
            ->body("El estado '{$this->getRecord()->label}' ha sido actualizado exitosamente.")
            ->duration(5000);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Estado de Aprobación Eliminado')
                        ->body('El estado ha sido eliminado exitosamente.')
                        ->duration(5000)
                ),
        ];
    }
}

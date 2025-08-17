<?php

namespace App\Filament\Resources\ManualSectionResource\Pages;

use App\Filament\Resources\ManualSectionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditManualSection extends EditRecord
{
    protected static string $resource = ManualSectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Sección del Manual Actualizada')
            ->body("La sección '{$this->getRecord()->title}' ha sido actualizada exitosamente.")
            ->duration(5000);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Sección del Manual Eliminada')
                        ->body('La sección ha sido eliminada exitosamente.')
                        ->duration(5000)
                ),
        ];
    }
}

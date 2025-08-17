<?php

namespace App\Filament\Resources\ManualSectionResource\Pages;

use App\Filament\Resources\ManualSectionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateManualSection extends CreateRecord
{
    protected static string $resource = ManualSectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Sección del Manual Creada')
            ->body("La sección '{$this->getRecord()->title}' ha sido creada exitosamente.")
            ->duration(5000);
    }
}

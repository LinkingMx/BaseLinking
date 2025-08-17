<?php

namespace App\Filament\Resources\ApprovalStateResource\Pages;

use App\Filament\Resources\ApprovalStateResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateApprovalState extends CreateRecord
{
    protected static string $resource = ApprovalStateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Estado de Aprobación Creado')
            ->body("El estado '{$this->getRecord()->label}' ha sido creado exitosamente para el modelo {$this->getModelDisplayName()}.")
            ->duration(5000);
    }

    private function getModelDisplayName(): string
    {
        $modelType = $this->getRecord()->model_type;

        return match ($modelType) {
            'App\\Models\\Documentation' => 'Documentation',
            'App\\Models\\User' => 'User',
            default => class_basename($modelType),
        };
    }
}

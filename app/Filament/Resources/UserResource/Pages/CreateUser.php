<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Handle role assignment after user creation
     */
    protected function handleRecordCreation(array $data): Model
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $record = parent::handleRecordCreation($data);

        if (! empty($roles)) {
            $record->syncRoles($roles);
        }

        return $record;
    }

    /**
     * Send custom success notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Usuario Creado Exitosamente')
            ->body('El usuario ha sido creado y los roles han sido asignados correctamente.')
            ->duration(5000);
    }

    /**
     * Redirect to users listing page after creation
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

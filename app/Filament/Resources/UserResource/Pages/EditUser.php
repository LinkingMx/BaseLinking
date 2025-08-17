<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Ver Usuario')
                ->icon('heroicon-o-eye'),
            Actions\DeleteAction::make()
                ->label('Eliminar Usuario'),
        ];
    }

    /**
     * Mutate form data before filling the form
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles->pluck('name')->toArray();

        return $data;
    }

    /**
     * Handle role assignment after user update
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $record = parent::handleRecordUpdate($record, $data);

        // Prevent removing super_admin role from the current user
        $currentUser = auth()->user();
        if ($record->id === $currentUser->id && $currentUser->hasRole('super_admin') && ! in_array('super_admin', $roles)) {
            $roles[] = 'super_admin';

            Notification::make()
                ->warning()
                ->title('Advertencia de Seguridad')
                ->body('No puedes remover tu propio rol de super_admin. Este rol se ha mantenido por seguridad.')
                ->duration(7000)
                ->send();
        }

        $record->syncRoles($roles);

        return $record;
    }

    /**
     * Send custom success notification
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Usuario Actualizado Exitosamente')
            ->body('El usuario y sus roles han sido actualizados correctamente.')
            ->duration(5000);
    }

    /**
     * Redirect to users listing page after update
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

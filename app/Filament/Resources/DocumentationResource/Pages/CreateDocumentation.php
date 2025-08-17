<?php

namespace App\Filament\Resources\DocumentationResource\Pages;

use App\Filament\Resources\DocumentationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentation extends CreateRecord
{
    protected static string $resource = DocumentationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asegurar que el documento se crea en draft y con el usuario actual
        $data['status'] = 'draft';
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Documento creado y enviado para aprobación';
    }
}

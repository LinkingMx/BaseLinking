<?php

namespace App\Filament\Resources\ManualSectionResource\Pages;

use App\Filament\Resources\ManualSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewManualSection extends ViewRecord
{
    protected static string $resource = ManualSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Sección')
                ->icon('heroicon-o-pencil'),
            Actions\DeleteAction::make()
                ->label('Eliminar Sección')
                ->icon('heroicon-o-trash'),
        ];
    }
}

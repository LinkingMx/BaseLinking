<?php

namespace App\Filament\Resources\ManualResource\Pages;

use App\Filament\Resources\ManualResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageManuals extends ManageRecords
{
    protected static string $resource = ManualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

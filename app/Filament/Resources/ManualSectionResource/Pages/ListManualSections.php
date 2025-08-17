<?php

namespace App\Filament\Resources\ManualSectionResource\Pages;

use App\Filament\Resources\ManualSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListManualSections extends ListRecords
{
    protected static string $resource = ManualSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

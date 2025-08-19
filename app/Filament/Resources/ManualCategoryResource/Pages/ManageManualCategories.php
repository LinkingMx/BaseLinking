<?php

namespace App\Filament\Resources\ManualCategoryResource\Pages;

use App\Filament\Resources\ManualCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageManualCategories extends ManageRecords
{
    protected static string $resource = ManualCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

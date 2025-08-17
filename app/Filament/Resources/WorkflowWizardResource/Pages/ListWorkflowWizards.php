<?php

namespace App\Filament\Resources\WorkflowWizardResource\Pages;

use App\Filament\Resources\WorkflowWizardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowWizards extends ListRecords
{
    protected static string $resource = WorkflowWizardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Workflow')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->createAnother(false),
        ];
    }

    public function getTitle(): string
    {
        return 'Asistente de Workflows';
    }

    public function getSubheading(): ?string
    {
        return 'Automatiza tareas repetitivas de forma fácil e intuitiva';
    }
}

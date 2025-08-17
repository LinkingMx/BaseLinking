<?php

namespace App\Filament\Resources\WorkflowWizardResource\Pages;

use App\Filament\Resources\WorkflowWizardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkflowWizard extends EditRecord
{
    protected static string $resource = WorkflowWizardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('duplicate')
                ->label('Duplicar Workflow')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $newWorkflow = $this->record->replicate();
                    $newWorkflow->name = $this->record->name.' (Copia)';
                    $newWorkflow->is_active = false;
                    $newWorkflow->version = 1;
                    $newWorkflow->save();

                    // Duplicar pasos y templates
                    foreach ($this->record->stepDefinitions as $step) {
                        $newStep = $step->replicate();
                        $newStep->advanced_workflow_id = $newWorkflow->id;
                        $newStep->save();

                        foreach ($step->templates as $template) {
                            $newTemplate = $template->replicate();
                            $newTemplate->workflow_step_definition_id = $newStep->id;
                            $newTemplate->save();
                        }
                    }

                    return redirect()->to(static::getResource()::getUrl('edit', ['record' => $newWorkflow->id]));
                }),

            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }

    public function getTitle(): string
    {
        return 'Editar Workflow: '.$this->record->name;
    }

    public function getSubheading(): ?string
    {
        return 'Modifica la configuración de tu workflow automático';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Extraer datos del primer step y template para mostrar en el wizard
        $firstStep = $this->record->stepDefinitions()->first();
        $firstTemplate = $firstStep?->templates()->first();

        if ($firstTemplate) {
            $data['email_subject'] = $firstTemplate->subject;
            $data['email_content'] = strip_tags($firstTemplate->content); // Extraer texto del HTML
            $data['email_template_style'] = $firstTemplate->template_config['style'] ?? 'modern';
            $data['notification_recipients'] = $firstTemplate->template_config['recipients'] ?? [];
            $data['custom_emails'] = $firstTemplate->template_config['custom_emails'] ?? [];
        }

        if ($firstStep) {
            $conditions = $firstStep->conditions;
            $data['trigger_event'] = $conditions['trigger_events'][0] ?? 'created';
        }

        return $data;
    }
}

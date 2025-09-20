<?php

namespace App\Filament\Resources\WorkflowWizardResource\Pages;

use App\Filament\Resources\WorkflowWizardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

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

        // PASO 1: Cargar datos básicos del workflow
        $data['name'] = $this->record->name;
        $data['description'] = $this->record->description;
        $data['automation_type'] = 'notify_email';
        
        // PASO 2: Cargar configuración del disparador
        $data['target_model'] = $this->record->target_model;
        if ($firstStep) {
            $conditions = $firstStep->conditions ?? [];
            $data['trigger_event'] = $conditions['trigger_events'][0] ?? 'created';
        }

        // PASO 3: Cargar configuración de destinatarios
        if ($firstTemplate) {
            $recipientConfig = $firstTemplate->recipient_config ?? [];
            $recipientType = $firstTemplate->recipient_type ?? 'creator';
            
            $recipients = [];
            if ($recipientType === 'creator') $recipients[] = 'creator';
            if ($recipientType === 'role') $recipients[] = 'admin';
            if ($recipientType === 'dynamic') $recipients[] = 'assigned';
            if ($recipientType === 'email' && !empty($recipientConfig['emails'])) {
                $recipients[] = 'custom';
            }
            $data['notification_recipients'] = $recipients;
            $data['custom_emails'] = $recipientConfig['emails'] ?? [];
        }

        // PASO 4: Cargar la plantilla de email seleccionada
        if ($firstTemplate) {
            $data['existing_template_key'] = $firstTemplate->email_template_key;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Actualizar datos básicos del workflow
        $mutatedData = [
            'name' => $data['name'] ?? $this->record->name,
            'description' => $data['description'] ?? $this->record->description,
            'target_model' => $data['target_model'] ?? $this->record->target_model,
        ];

        // Actualizar el step y template relacionados
        $this->updateWorkflowStepAndTemplate($data);

        return $mutatedData;
    }

    protected function updateWorkflowStepAndTemplate(array $data): void
    {
        $firstStep = $this->record->stepDefinitions()->first();
        $firstTemplate = $firstStep?->templates()->first();

        if (!$firstStep || !$firstTemplate) {
            return;
        }

        // Actualizar condiciones del step
        $firstStep->update([
            'conditions' => [
                'trigger_events' => [$data['trigger_event'] ?? 'created'],
            ],
        ]);

        // Actualizar configuración de destinatarios
        $recipientType = $this->determineRecipientType($data['notification_recipients'] ?? []);
        $recipientConfig = $this->buildRecipientConfig($data);
        
        // Lógica simplificada: solo guardar la clave de la plantilla
        $newTemplateKey = $data['existing_template_key'] ?? null;
        $templateVars = ['source_template' => $newTemplateKey];

        $firstTemplate->update([
            'recipient_type' => $recipientType,
            'recipient_config' => $recipientConfig,
            'email_template_key' => $newTemplateKey,
            'template_variables' => $templateVars,
        ]);
    }

    protected function determineRecipientType(array $recipients): string
    {
        if (empty($recipients)) {
            return 'creator';
        }

        // Si solo tiene 'creator', es tipo creator
        if (count($recipients) === 1 && in_array('creator', $recipients)) {
            return 'creator';
        }

        // Si solo tiene 'admin', es tipo admin
        if (count($recipients) === 1 && in_array('admin', $recipients)) {
            return 'admin';
        }

        // Si solo tiene 'assigned', es tipo assigned
        if (count($recipients) === 1 && in_array('assigned', $recipients)) {
            return 'assigned';
        }

        // Si solo tiene 'team', es tipo team
        if (count($recipients) === 1 && in_array('team', $recipients)) {
            return 'team';
        }

        // Si tiene múltiples tipos o incluye 'custom', es custom
        return 'custom';
    }

    protected function buildRecipientConfig(array $data): array
    {
        return [
            'recipients' => $data['notification_recipients'] ?? [],
            'custom_emails' => $data['custom_emails'] ?? [],
        ];
    }

    

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Workflow actualizado')
            ->body('Los cambios en el workflow han sido guardados correctamente.')
            ->icon('heroicon-o-pencil-square');
    }
}


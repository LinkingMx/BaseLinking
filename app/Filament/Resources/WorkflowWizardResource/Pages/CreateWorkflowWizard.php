<?php

namespace App\Filament\Resources\WorkflowWizardResource\Pages;

use App\Filament\Resources\WorkflowWizardResource;
use App\Models\AdvancedWorkflow;
use App\Models\EmailTemplate;
use App\Models\WorkflowStepDefinition;
use App\Models\WorkflowStepTemplate;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateWorkflowWizard extends CreateRecord
{
    protected static string $resource = WorkflowWizardResource::class;

    public function getTitle(): string
    {
        return 'Crear Nuevo Workflow';
    }

    public function getSubheading(): ?string
    {
        return 'Sigue los pasos para crear tu automatización perfecta';
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Crear el workflow principal
        $workflow = AdvancedWorkflow::create([
            'name' => $data['name'],
            'target_model' => $data['target_model'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'version' => 1,
            'trigger_conditions' => $this->buildTriggerConditions($data),
        ]);

        // Crear el paso del workflow
        $step = WorkflowStepDefinition::create([
            'advanced_workflow_id' => $workflow->id,
            'step_name' => 'Enviar Notificación por Email',
            'step_type' => WorkflowStepDefinition::TYPE_NOTIFICATION,
            'step_order' => 1,
            'is_active' => true,
            'conditions' => $this->buildStepConditions($data),
            'step_config' => $this->buildStepConfiguration($data),
        ]);

        $recipientType = $this->determineRecipientType($data['notification_recipients'] ?? []);
        $recipientConfig = $this->buildRecipientConfig($data);
        
        // Lógica simplificada: siempre se usa una plantilla existente
        $templateKey = $data['existing_template_key'];
        $templateVariables = ['source_template' => $templateKey];

        // Crear la configuración del paso del workflow con la clave de plantilla correcta
        WorkflowStepTemplate::create([
            'workflow_step_definition_id' => $step->id,
            'recipient_type' => $recipientType,
            'recipient_config' => $recipientConfig,
            'email_template_key' => $templateKey,
            'template_variables' => $templateVariables,
        ]);

        return $workflow;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Workflow creado exitosamente!')
            ->body('El nuevo workflow ha sido añadido y está listo para usarse.')
            ->icon('heroicon-o-sparkles');
    }

    protected function buildTriggerConditions(array $data): array
    {
        return [
            'event' => $data['trigger_event'],
            'model' => $data['target_model'],
        ];
    }

    protected function buildStepConditions(array $data): array
    {
        return [
            'trigger_events' => [$data['trigger_event']],
        ];
    }

    protected function buildStepConfiguration(array $data): array
    {
        return [
            'email_config' => [
                'recipients' => $data['notification_recipients'] ?? [],
                'custom_emails' => $data['custom_emails'] ?? [],
            ],
        ];
    }

    protected function determineRecipientType(array $recipients): string
    {
        if (in_array('custom', $recipients)) {
            return 'email';
        }
        if (in_array('creator', $recipients)) {
            return 'creator';
        }
        if (in_array('admin', $recipients)) {
            return 'role';
        }
        if (in_array('assigned', $recipients)) {
            return 'dynamic';
        }

        return 'email'; // default fallback
    }

    protected function buildRecipientConfig(array $data): array
    {
        $recipients = $data['notification_recipients'] ?? [];
        $config = [];

        if (in_array('custom', $recipients)) {
            $config['emails'] = $data['custom_emails'] ?? [];
        }

        if (in_array('admin', $recipients)) {
            $config['role_names'] = ['super_admin', 'admin'];
        }

        if (in_array('assigned', $recipients)) {
            $config['dynamic_type'] = 'assigned_user';
        }

        return $config;
    }
}

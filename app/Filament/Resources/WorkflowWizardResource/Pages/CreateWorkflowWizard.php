<?php

namespace App\Filament\Resources\WorkflowWizardResource\Pages;

use App\Filament\Resources\WorkflowWizardResource;
use App\Models\AdvancedWorkflow;
use App\Models\WorkflowStepDefinition;
use App\Models\WorkflowStepTemplate;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

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
            'step_type' => 'send_email',
            'step_order' => 1,
            'is_active' => true,
            'conditions' => $this->buildStepConditions($data),
            'step_config' => $this->buildStepConfiguration($data),
        ]);

        // Configurar template de email en el step
        $recipientType = $this->determineRecipientType($data['notification_recipients'] ?? []);
        $recipientConfig = $this->buildRecipientConfig($data);

        WorkflowStepTemplate::create([
            'workflow_step_definition_id' => $step->id,
            'recipient_type' => $recipientType,
            'recipient_config' => $recipientConfig,
            'email_template_key' => 'workflow_notification',
            'template_variables' => [
                'email_subject' => $data['email_subject'],
                'email_content' => $this->buildEmailContent($data),
                'template_style' => $data['email_template_style'] ?? 'modern',
            ],
        ]);

        return $workflow;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return '¡Workflow creado exitosamente!';
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
                'template_key' => 'main_notification',
                'recipients' => $data['notification_recipients'] ?? [],
                'custom_emails' => $data['custom_emails'] ?? [],
            ],
        ];
    }

    protected function buildEmailContent(array $data): string
    {
        $content = $data['email_content'] ?? '';
        $style = $data['email_template_style'] ?? 'modern';

        // Envolver contenido según el estilo seleccionado
        return match ($style) {
            'simple' => $content,
            'modern' => $this->wrapModernTemplate($content),
            'corporate' => $this->wrapCorporateTemplate($content),
            'friendly' => $this->wrapFriendlyTemplate($content),
            default => $content,
        };
    }

    protected function wrapModernTemplate(string $content): string
    {
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white;'>
                <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>{{app_name}}</h1>
            </div>
            <div style='background: white; padding: 30px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;'>
                <div style='color: #374151; font-size: 16px; line-height: 1.6;'>
                    {$content}
                </div>
            </div>
            <div style='background: #f9fafb; padding: 20px; text-align: center; border: 1px solid #e5e7eb; border-top: none; color: #6b7280; font-size: 14px;'>
                Este email fue enviado automáticamente por {{app_name}}
            </div>
        </div>
        ";
    }

    protected function wrapCorporateTemplate(string $content): string
    {
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: \"Segoe UI\", sans-serif;'>
            <div style='background: #1f2937; padding: 25px; color: white;'>
                <h1 style='margin: 0; font-size: 24px; font-weight: normal;'>{{app_name}}</h1>
            </div>
            <div style='background: white; padding: 40px; border: 1px solid #d1d5db;'>
                <div style='color: #111827; font-size: 16px; line-height: 1.7;'>
                    {$content}
                </div>
            </div>
            <div style='background: #f3f4f6; padding: 15px; text-align: center; color: #6b7280; font-size: 12px;'>
                {{app_name}} - Notificación Automática
            </div>
        </div>
        ";
    }

    protected function wrapFriendlyTemplate(string $content): string
    {
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: \"Comic Sans MS\", cursive;'>
            <div style='background: #fbbf24; padding: 25px; text-align: center; color: #92400e;'>
                <h1 style='margin: 0; font-size: 26px; font-weight: bold;'>{{app_name}}</h1>
            </div>
            <div style='background: #fffbeb; padding: 30px; border: 2px solid #fbbf24;'>
                <div style='color: #92400e; font-size: 16px; line-height: 1.6;'>
                    {$content}
                </div>
            </div>
            <div style='background: #fef3c7; padding: 20px; text-align: center; color: #92400e; font-size: 14px;'>
¡Gracias por usar {{app_name}}!
            </div>
        </div>
        ";
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

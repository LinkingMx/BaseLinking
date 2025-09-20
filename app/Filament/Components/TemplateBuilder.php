<?php

namespace App\Filament\Components;

use App\Models\EmailTemplate;
use Filament\Forms\Components\Concerns\HasState;
use Filament\Forms\Components\Field;
use Filament\Notifications\Notification;

class TemplateBuilder extends Field
{
    use HasState;

    protected string $view = 'filament.components.template-builder';

    protected ?string $targetModel = null;

    protected array $availableVariables = [];

    protected string $templateStyle = 'modern';

    protected ?string $previewSubject = null;

    protected bool $canSaveAsTemplate = true;

    protected ?string $savedTemplateCategory = 'workflow_generated';

    public function targetModel(?string $model): static
    {
        $this->targetModel = $model;
        $this->loadAvailableVariables();

        return $this;
    }

    public function templateStyle(string $style): static
    {
        $this->templateStyle = $style;

        return $this;
    }

    public function previewSubject(?string $subject): static
    {
        $this->previewSubject = $subject;

        return $this;
    }

    public function canSaveAsTemplate(bool $condition = true): static
    {
        $this->canSaveAsTemplate = $condition;

        return $this;
    }

    public function savedTemplateCategory(string $category): static
    {
        $this->savedTemplateCategory = $category;

        return $this;
    }

    public function getTargetModel(): ?string
    {
        return $this->targetModel;
    }

    public function getCanSaveAsTemplate(): bool
    {
        return $this->canSaveAsTemplate;
    }

    public function getTemplateStyle(): string
    {
        return $this->templateStyle;
    }

    public function getPreviewSubject(): ?string
    {
        return $this->previewSubject;
    }

    public function getAvailableVariables(): array
    {
        return $this->availableVariables;
    }

    protected function loadAvailableVariables(): void
    {
        if (! $this->targetModel) {
            $this->availableVariables = $this->getGlobalVariables();

            return;
        }

        try {
            $introspectionService = app(\App\Services\ModelIntrospectionService::class);
            $modelInfo = $introspectionService->getModelInfo($this->targetModel);
            $modelVariables = $modelInfo['available_variables'] ?? [];

            $this->availableVariables = array_merge($this->getGlobalVariables(), $modelVariables);
        } catch (\Exception $e) {
            $this->availableVariables = $this->getGlobalVariables();
        }
    }

    protected function getGlobalVariables(): array
    {
        return [
            [
                'key' => 'app_name',
                'description' => 'Nombre de la aplicación',
                'category' => 'global',
            ],
            [
                'key' => 'current_date',
                'description' => 'Fecha actual',
                'category' => 'global',
            ],
            [
                'key' => 'current_time',
                'description' => 'Hora actual',
                'category' => 'global',
            ],
            [
                'key' => 'site_url',
                'description' => 'URL del sitio web',
                'category' => 'global',
            ],
        ];
    }

    public function getTemplatePresets(): array
    {
        return [
            'welcome' => [
                'name' => 'Bienvenida',
                'icon' => '🎉',
                'subject' => '¡Bienvenido a {{app_name}}!',
                'content' => "Hola {{nombre}},\n\n¡Te damos la bienvenida a {{app_name}}!\n\nGracias por unirte a nosotros. Estamos emocionados de tenerte como parte de nuestra comunidad.\n\nSi tienes alguna pregunta, no dudes en contactarnos.\n\n¡Saludos!\nEl equipo de {{app_name}}",
            ],
            'notification' => [
                'name' => 'Notificación',
                'icon' => '🔔',
                'subject' => 'Actualización importante - {{app_name}}',
                'content' => "Hola {{nombre}},\n\nTe contactamos para informarte sobre una actualización importante:\n\n{{descripcion}}\n\nFecha: {{fecha}}\nEstado: {{estado}}\n\nGracias por tu atención.\n\nSaludos,\n{{app_name}}",
            ],
            'reminder' => [
                'name' => 'Recordatorio',
                'icon' => '⏰',
                'subject' => 'Recordatorio: {{titulo}}',
                'content' => "Hola {{nombre}},\n\nEste es un recordatorio sobre:\n\n{{titulo}}\n\nDescripción: {{descripcion}}\nFecha límite: {{fecha_limite}}\n\nNo olvides completar esta tarea a tiempo.\n\nSaludos,\n{{app_name}}",
            ],
            'confirmation' => [
                'name' => 'Confirmación',
                'icon' => '✅',
                'subject' => 'Confirmación: {{accion}} completada',
                'content' => "Hola {{nombre}},\n\nTe confirmamos que la siguiente acción ha sido completada exitosamente:\n\n{{accion}}\n\nDetalles:\n- Fecha: {{fecha}}\n- Resultado: {{resultado}}\n\nGracias.\n\n{{app_name}}",
            ],
            'alert' => [
                'name' => 'Alerta',
                'icon' => '⚠️',
                'subject' => 'Alerta importante - {{tipo_alerta}}',
                'content' => "Hola {{nombre}},\n\nHemos detectado una situación que requiere tu atención:\n\n{{tipo_alerta}}\n\nDetalles:\n{{descripcion}}\n\nTiempo detectado: {{fecha_deteccion}}\n\nPor favor, revisa esta situación lo antes posible.\n\n{{app_name}}",
            ],
        ];
    }

    public function getStylePresets(): array
    {
        return [
            'simple' => [
                'name' => 'Simple',
                'description' => 'Texto plano, sin formato especial',
                'preview_bg' => 'bg-white',
                'preview_border' => 'border-gray-200',
            ],
            'modern' => [
                'name' => 'Moderno',
                'description' => 'Diseño colorido con gradientes y botones',
                'preview_bg' => 'bg-gradient-to-r from-blue-500 to-purple-600',
                'preview_border' => 'border-blue-300',
            ],
            'corporate' => [
                'name' => 'Corporativo',
                'description' => 'Estilo formal y profesional',
                'preview_bg' => 'bg-gray-800',
                'preview_border' => 'border-gray-400',
            ],
            'friendly' => [
                'name' => 'Amigable',
                'description' => 'Colores cálidos y estilo casual',
                'preview_bg' => 'bg-yellow-400',
                'preview_border' => 'border-yellow-300',
            ],
        ];
    }

    public function generatePreview(string $content, string $subject = '', array $sampleData = []): string
    {
        // Datos de ejemplo para la preview
        $defaultSampleData = [
            'nombre' => 'Juan Pérez',
            'email' => 'juan@empresa.com',
            'app_name' => 'Mi Aplicación',
            'current_date' => now()->format('d/m/Y'),
            'current_time' => now()->format('H:i'),
            'titulo' => 'Tarea de ejemplo',
            'descripcion' => 'Esta es una descripción de ejemplo',
            'estado' => 'Completado',
            'fecha' => now()->format('d/m/Y'),
            'fecha_limite' => now()->addDays(7)->format('d/m/Y'),
            'accion' => 'Registro de usuario',
            'resultado' => 'Exitoso',
            'tipo_alerta' => 'Uso elevado de CPU',
            'fecha_deteccion' => now()->format('d/m/Y H:i'),
        ];

        $sampleData = array_merge($defaultSampleData, $sampleData);

        // Reemplazar variables en subject y content
        $previewSubject = $this->replaceVariables($subject, $sampleData);
        $previewContent = $this->replaceVariables($content, $sampleData);

        return $this->wrapWithStyle($previewContent, $previewSubject);
    }

    protected function replaceVariables(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace('{{'.$key.'}}', $value, $text);
        }

        return $text;
    }

    protected function wrapWithStyle(string $content, string $subject): string
    {
        return match ($this->templateStyle) {
            'modern' => $this->wrapModernStyle($content, $subject),
            'corporate' => $this->wrapCorporateStyle($content, $subject),
            'friendly' => $this->wrapFriendlyStyle($content, $subject),
            'simple' => $this->wrapSimpleStyle($content, $subject),
            default => $this->wrapModernStyle($content, $subject),
        };
    }

    protected function wrapSimpleStyle(string $content, string $subject): string
    {
        return nl2br(htmlspecialchars($content));
    }

    protected function wrapModernStyle(string $content, string $subject): string
    {
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px; overflow: hidden;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white;'>
                <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>".htmlspecialchars($subject)."</h1>
            </div>
            <div style='background: white; padding: 30px; color: #374151; font-size: 16px; line-height: 1.6;'>
                ".nl2br(htmlspecialchars($content))."
            </div>
            <div style='background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px; border-top: 1px solid #e5e7eb;'>
                Este email fue enviado automáticamente por Mi Aplicación
            </div>
        </div>
        ";
    }

    protected function wrapCorporateStyle(string $content, string $subject): string
    {
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: \"Segoe UI\", sans-serif; border: 1px solid #d1d5db; border-radius: 4px; overflow: hidden;'>
            <div style='background: #1f2937; padding: 25px; color: white; border-bottom: 2px solid #374151;'>
                <h1 style='margin: 0; font-size: 24px; font-weight: normal;'>".htmlspecialchars($subject)."</h1>
            </div>
            <div style='background: white; padding: 40px; color: #111827; font-size: 16px; line-height: 1.7;'>
                ".nl2br(htmlspecialchars($content))."
            </div>
            <div style='background: #f3f4f6; padding: 15px; text-align: center; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;'>
                Mi Aplicación - Notificación Automática
            </div>
        </div>
        ";
    }

    protected function wrapFriendlyStyle(string $content, string $subject): string
    {
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: \"Arial\", sans-serif; border: 3px solid #fbbf24; border-radius: 12px; overflow: hidden;'>
            <div style='background: #fbbf24; padding: 25px; text-align: center; color: #92400e;'>
                <h1 style='margin: 0; font-size: 26px; font-weight: bold;'>".htmlspecialchars($subject)."</h1>
            </div>
            <div style='background: #fffbeb; padding: 30px; color: #92400e; font-size: 16px; line-height: 1.6;'>
                ".nl2br(htmlspecialchars($content))."
            </div>
            <div style='background: #fef3c7; padding: 20px; text-align: center; color: #92400e; font-size: 14px;'>
¡Gracias por usar Mi Aplicación!
            </div>
        </div>
        ";
    }

    /**
     * Guardar el contenido actual como plantilla reutilizable
     */
    public function saveAsTemplate(string $templateKey, string $templateName, array $templateData): bool
    {
        try {
            // Verificar si ya existe una plantilla con esa clave
            $existingTemplate = EmailTemplate::where('key', $templateKey)->first();
            
            if ($existingTemplate) {
                throw new \Exception("Ya existe una plantilla con la clave '{$templateKey}'");
            }

            // Crear el contenido final con el estilo aplicado
            $finalContent = $this->wrapWithStyle(
                $templateData['content'] ?? '',
                $templateData['style'] ?? 'modern',
                $templateData['subject'] ?? ''
            );

            // Crear la nueva plantilla
            EmailTemplate::create([
                'key' => $templateKey,
                'name' => $templateName,
                'subject' => $templateData['subject'] ?? '',
                'content' => $finalContent,
                'category' => $this->savedTemplateCategory,
                'model_type' => $this->targetModel,
                'is_active' => true,
                'language' => 'es',
                'variables' => $this->extractVariablesFromContent($templateData['content'] ?? '', $templateData['subject'] ?? ''),
            ]);

            // Mostrar notificación de éxito
            Notification::make()
                ->title('Plantilla guardada')
                ->body("La plantilla '{$templateName}' se ha guardado correctamente y puede ser reutilizada.")
                ->success()
                ->send();

            return true;

        } catch (\Exception $e) {
            // Mostrar notificación de error
            Notification::make()
                ->title('Error al guardar plantilla')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }

    /**
     * Extraer variables del contenido y asunto
     */
    protected function extractVariablesFromContent(string $content, string $subject): array
    {
        $text = $content . ' ' . $subject;
        preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);
        
        $variables = [];
        if (!empty($matches[1])) {
            foreach (array_unique($matches[1]) as $variable) {
                $variables[] = [
                    'key' => trim($variable),
                    'description' => 'Variable generada automáticamente desde workflow',
                    'required' => false,
                ];
            }
        }

        return $variables;
    }

    /**
     * Cargar plantillas existentes para mostrar como opciones
     */
    public function getExistingTemplates(): array
    {
        return EmailTemplate::where('category', $this->savedTemplateCategory)
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(function ($template) {
                return [$template->key => [
                    'name' => $template->name,
                    'subject' => $template->subject,
                    'content' => $this->extractContentFromWrapped($template->content),
                    'variables' => $template->variables ?? [],
                ]];
            })
            ->toArray();
    }

    /**
     * Extraer contenido original de una plantilla con wrapper
     */
    protected function extractContentFromWrapped(string $wrappedContent): string
    {
        // Intentar extraer el contenido del wrapper
        $patterns = [
            // Patrón para template moderno
            '/<div[^>]*background:\s*white[^>]*>(.*?)<\/div>\s*<div[^>]*background:\s*#f9fafb/s',
            // Patrón para template corporativo
            '/<div[^>]*background:\s*white[^>]*>(.*?)<\/div>\s*<div[^>]*background:\s*#f3f4f6/s',
            // Patrón para template amigable
            '/<div[^>]*background:\s*#fffbeb[^>]*>(.*?)<\/div>\s*<div[^>]*background:\s*#fef3c7/s',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $wrappedContent, $matches)) {
                $extractedContent = strip_tags($matches[1]);
                if (trim($extractedContent)) {
                    return trim(html_entity_decode($extractedContent));
                }
            }
        }

        // Si no puede extraer, devolver contenido limpio
        return trim(strip_tags(html_entity_decode($wrappedContent)));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TemplateBuilderController extends Controller
{
    /**
     * Guardar una plantilla desde el TemplateBuilder
     */
    public function saveTemplate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'template_key' => 'required|string|regex:/^[a-zA-Z0-9_]+$/|unique:email_templates,key',
                'template_name' => 'required|string|max:255',
                'template_data' => 'required|array',
                'template_data.subject' => 'required|string|max:255',
                'template_data.content' => 'required|string',
                'template_data.style' => 'required|string|in:simple,modern,corporate,friendly',
            ]);

            // Construir el contenido final con el estilo aplicado
            $finalContent = $this->buildEmailContentWithStyle(
                $validated['template_data']['content'],
                $validated['template_data']['style'],
                $validated['template_data']['subject']
            );

            // Extraer variables del contenido
            $variables = $this->extractVariables(
                $validated['template_data']['content'],
                $validated['template_data']['subject']
            );

            // Crear la plantilla
            $template = EmailTemplate::create([
                'key' => $validated['template_key'],
                'name' => $validated['template_name'],
                'subject' => $validated['template_data']['subject'],
                'content' => $finalContent,
                'category' => 'workflow_generated',
                'model_type' => null, // Se puede especificar después
                'is_active' => true,
                'language' => 'es',
                'variables' => $variables,
                'metadata' => [
                    'created_from' => 'template_builder',
                    'original_style' => $validated['template_data']['style'],
                    'original_content' => $validated['template_data']['content'],
                    'created_at' => now()->toISOString(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plantilla guardada correctamente',
                'template' => [
                    'id' => $template->id,
                    'key' => $template->key,
                    'name' => $template->name,
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la plantilla: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Construir el contenido del email con el estilo aplicado
     */
    protected function buildEmailContentWithStyle(string $content, string $style, string $subject): string
    {
        switch ($style) {
            case 'simple':
                return $content;

            case 'modern':
                return $this->wrapModernTemplate($content, $subject);

            case 'corporate':
                return $this->wrapCorporateTemplate($content, $subject);

            case 'friendly':
                return $this->wrapFriendlyTemplate($content, $subject);

            default:
                return $content;
        }
    }

    /**
     * Template moderno
     */
    protected function wrapModernTemplate(string $content, string $subject): string
    {
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px; overflow: hidden;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white;'>
                <h1 style='margin: 0; font-size: 24px; font-weight: bold;'>" . htmlspecialchars($subject) . "</h1>
            </div>
            <div style='background: white; padding: 30px; color: #374151; font-size: 16px; line-height: 1.6;'>
                " . nl2br(htmlspecialchars($content)) . "
            </div>
            <div style='background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px;'>
                Este email fue enviado automáticamente por Mi Aplicación
            </div>
        </div>
        ";
    }

    /**
     * Template corporativo
     */
    protected function wrapCorporateTemplate(string $content, string $subject): string
    {
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: \"Segoe UI\", sans-serif; border: 1px solid #d1d5db; border-radius: 4px; overflow: hidden;'>
            <div style='background: #1f2937; padding: 25px; color: white; border-bottom: 2px solid #374151;'>
                <h1 style='margin: 0; font-size: 24px; font-weight: normal;'>" . htmlspecialchars($subject) . "</h1>
            </div>
            <div style='background: white; padding: 40px; color: #111827; font-size: 16px; line-height: 1.7;'>
                " . nl2br(htmlspecialchars($content)) . "
            </div>
            <div style='background: #f3f4f6; padding: 15px; text-align: center; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;'>
                Mi Aplicación - Notificación Automática
            </div>
        </div>
        ";
    }

    /**
     * Template amigable
     */
    protected function wrapFriendlyTemplate(string $content, string $subject): string
    {
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: \"Arial\", sans-serif; border: 3px solid #fbbf24; border-radius: 12px; overflow: hidden;'>
            <div style='background: #fbbf24; padding: 25px; text-align: center; color: #92400e;'>
                <h1 style='margin: 0; font-size: 26px; font-weight: bold;'>" . htmlspecialchars($subject) . "</h1>
            </div>
            <div style='background: #fffbeb; padding: 30px; color: #92400e; font-size: 16px; line-height: 1.6;'>
                " . nl2br(htmlspecialchars($content)) . "
            </div>
            <div style='background: #fef3c7; padding: 20px; text-align: center; color: #92400e; font-size: 14px;'>
                ¡Gracias por usar Mi Aplicación!
            </div>
        </div>
        ";
    }

    /**
     * Extraer variables del contenido y asunto
     */
    protected function extractVariables(string $content, string $subject): array
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
}

<?php

namespace App\Http\Controllers;

use App\Services\EmailTemplateService;
use Illuminate\Http\Request;

class EmailTemplatePreviewController extends Controller
{
    public function __construct(
        private EmailTemplateService $emailTemplateService
    ) {}

    /**
     * Mostrar preview del wrapper base de email
     */
    public function showWrapper()
    {
        // Contenido de ejemplo para el wrapper
        $sampleContent = '
            <div style="padding: 30px;">
                <h2 style="color: #1f2937; margin-bottom: 20px;">🎉 ¡Bienvenido a nuestro sistema!</h2>
                
                <p style="margin-bottom: 15px; color: #374151; line-height: 1.6;">
                    Hola <strong>Juan Pérez</strong>,
                </p>
                
                <p style="margin-bottom: 20px; color: #374151; line-height: 1.6;">
                    Este es un ejemplo de cómo se ve el template base de emails de la aplicación. 
                    Todos los emails enviados desde el sistema utilizan este diseño consistente.
                </p>
                
                <div style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); 
                           padding: 20px; 
                           border-radius: 8px; 
                           margin: 25px 0; 
                           color: white;">
                    <h3 style="margin: 0 0 10px 0; font-size: 18px;">📧 Características del Template</h3>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>Diseño responsive</li>
                        <li>Compatible con todos los clientes de email</li>
                        <li>Header con logo dinámico</li>
                        <li>Footer profesional</li>
                        <li>Variables dinámicas integradas</li>
                    </ul>
                </div>
                
                <div style="background: #f3f4f6; 
                           border-left: 4px solid #10b981; 
                           padding: 15px; 
                           margin: 20px 0; 
                           border-radius: 0 8px 8px 0;">
                    <p style="margin: 0; color: #065f46; font-weight: 500;">
                        💡 <strong>Variables de ejemplo utilizadas:</strong>
                    </p>
                    <ul style="margin: 10px 0 0 0; color: #374151; font-size: 14px;">
                        <li><code>{{app_name}}</code> = ' . config('app.name') . '</li>
                        <li><code>{{user_name}}</code> = Juan Pérez</li>
                        <li><code>{{current_date}}</code> = ' . now()->format('d/m/Y') . '</li>
                        <li><code>{{current_time}}</code> = ' . now()->format('H:i:s') . '</li>
                    </ul>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . config('app.url') . '/admin" 
                       style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
                              color: white; 
                              padding: 12px 30px; 
                              text-decoration: none; 
                              border-radius: 25px; 
                              font-weight: bold; 
                              display: inline-block;
                              box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);">
                        🚀 Ir al Panel de Administración
                    </a>
                </div>
                
                <p style="color: #6b7280; font-size: 14px; margin-top: 25px; text-align: center;">
                    Este es el template wrapper.blade.php en acción 🎨
                </p>
            </div>
        ';

        // Renderizar el wrapper con el contenido de ejemplo
        return view('emails.wrapper', [
            'content' => $sampleContent
        ]);
    }

    /**
     * Mostrar preview de un template específico con variables de ejemplo
     */
    public function showTemplate(string $templateKey)
    {
        try {
            // Variables de ejemplo para diferentes tipos de templates
            $sampleVariables = $this->getSampleVariables($templateKey);
            
            // Procesar template
            $processedTemplate = $this->emailTemplateService->processTemplate($templateKey, $sampleVariables);
            
            // Renderizar con wrapper
            return view('emails.wrapper', [
                'content' => $processedTemplate['content']
            ]);
            
        } catch (\Exception $e) {
            return response()->view('errors.email-template-not-found', [
                'templateKey' => $templateKey,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Obtener variables de ejemplo según el tipo de template
     */
    private function getSampleVariables(string $templateKey): array
    {
        $baseVariables = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'current_date' => now()->format('d/m/Y'),
            'current_time' => now()->format('H:i:s'),
            'user_name' => 'Juan Pérez',
            'user_email' => 'juan.perez@empresa.com',
        ];

        // Variables específicas según el tipo de template
        $specificVariables = match (true) {
            str_contains($templateKey, 'backup') => [
                'backup_name' => 'Respaldo Diario - ' . now()->format('Y-m-d'),
                'backup_size' => '2.5 GB',
                'backup_date' => now()->format('d/m/Y H:i:s'),
                'backup_status' => 'Completado exitosamente',
                'backup_duration' => '15 minutos',
                'backup_destination' => 'Google Drive',
            ],
            str_contains($templateKey, 'user') => [
                'verification_url' => config('app.url') . '/email/verify',
                'reset_url' => config('app.url') . '/password/reset',
                'user_role' => 'Administrador',
            ],
            str_contains($templateKey, 'document') => [
                'document_title' => 'Manual de Procedimientos',
                'document_id' => 'DOC-2024-001',
                'document_status' => 'En revisión',
                'document_url' => config('app.url') . '/documents/1',
                'creator_name' => 'María García',
                'created_date' => now()->subDays(2)->format('d/m/Y'),
            ],
            str_contains($templateKey, 'model') || str_contains($templateKey, 'bank') => [
                'model_name' => 'Banco',
                'model_id' => '123',
                'model_title' => 'Banco de Crédito del Perú',
                'model_status' => 'Activo',
                'action_type' => 'crear',
                'action_user' => 'Carlos Rodríguez',
                'action_date' => now()->format('d/m/Y H:i:s'),
                'record_url' => config('app.url') . '/admin/banks/123',
            ],
            default => []
        };

        return array_merge($baseVariables, $specificVariables);
    }
}

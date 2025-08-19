<?php

namespace Database\Seeders;

use App\Models\ManualResource;
use Illuminate\Database\Seeder;

class ManualResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            [
                'key' => 'UserResource',
                'name' => 'Gestión de Usuarios',
                'description' => 'Recurso para administrar usuarios del sistema',
                'class_name' => 'App\Filament\Resources\UserResource',
                'icon' => 'heroicon-o-users',
                'color' => 'success',
                'sort_order' => 1,
            ],
            [
                'key' => 'WorkflowWizardResource',
                'name' => 'Asistente de Workflows',
                'description' => 'Herramienta para crear workflows paso a paso',
                'class_name' => 'App\Filament\Resources\WorkflowWizardResource',
                'icon' => 'heroicon-o-sparkles',
                'color' => 'primary',
                'sort_order' => 2,
            ],
            [
                'key' => 'AdvancedWorkflowResource',
                'name' => 'Workflows Avanzados',
                'description' => 'Gestión avanzada de workflows complejos',
                'class_name' => 'App\Filament\Resources\AdvancedWorkflowResource',
                'icon' => 'heroicon-o-arrow-right-circle',
                'color' => 'info',
                'sort_order' => 3,
            ],
            [
                'key' => 'ApprovalStateResource',
                'name' => 'Estados de Aprobación',
                'description' => 'Gestión de estados y procesos de aprobación',
                'class_name' => 'App\Filament\Resources\ApprovalStateResource',
                'icon' => 'heroicon-o-check-circle',
                'color' => 'warning',
                'sort_order' => 4,
            ],
            [
                'key' => 'StateTransitionResource',
                'name' => 'Transiciones de Estado',
                'description' => 'Configuración de transiciones entre estados',
                'class_name' => 'App\Filament\Resources\StateTransitionResource',
                'icon' => 'heroicon-o-arrow-path',
                'color' => 'secondary',
                'sort_order' => 5,
            ],
            [
                'key' => 'DocumentationResource',
                'name' => 'Documentación',
                'description' => 'Gestión de documentos del sistema',
                'class_name' => 'App\Filament\Resources\DocumentationResource',
                'icon' => 'heroicon-o-document-text',
                'color' => 'gray',
                'sort_order' => 6,
            ],
            [
                'key' => 'EmailConfigurationResource',
                'name' => 'Configuración de Email',
                'description' => 'Configuración de servidores y plantillas de email',
                'class_name' => 'App\Filament\Resources\EmailConfigurationResource',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'primary',
                'sort_order' => 7,
            ],
            [
                'key' => 'EmailTemplateResource',
                'name' => 'Plantillas de Email',
                'description' => 'Gestión de plantillas para notificaciones',
                'class_name' => 'App\Filament\Resources\EmailTemplateResource',
                'icon' => 'heroicon-o-envelope',
                'color' => 'info',
                'sort_order' => 8,
            ],
            [
                'key' => 'BackupManager',
                'name' => 'Gestión de Respaldos',
                'description' => 'Herramientas para backup y restauración',
                'class_name' => 'App\Filament\Resources\BackupManagerResource',
                'icon' => 'heroicon-o-archive-box',
                'color' => 'danger',
                'sort_order' => 9,
            ],
            [
                'key' => 'SystemMonitoring',
                'name' => 'Monitoreo del Sistema',
                'description' => 'Herramientas de monitoreo y análisis de rendimiento',
                'class_name' => 'App\Filament\Resources\SystemMonitoringResource',
                'icon' => 'heroicon-o-chart-bar',
                'color' => 'success',
                'sort_order' => 10,
            ],
        ];

        foreach ($resources as $resource) {
            ManualResource::updateOrCreate(
                ['key' => $resource['key']],
                $resource
            );
        }
    }
}

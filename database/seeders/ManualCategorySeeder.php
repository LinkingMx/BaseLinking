<?php

namespace Database\Seeders;

use App\Models\ManualCategory;
use Illuminate\Database\Seeder;

class ManualCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'key' => 'introduccion',
                'name' => 'Introducción',
                'description' => 'Información básica y primeros pasos',
                'icon' => 'heroicon-o-home',
                'color' => 'primary',
                'sort_order' => 1,
            ],
            [
                'key' => 'usuarios',
                'name' => 'Gestión de Usuarios',
                'description' => 'Administración de usuarios, roles y permisos',
                'icon' => 'heroicon-o-users',
                'color' => 'success',
                'sort_order' => 2,
            ],
            [
                'key' => 'workflows',
                'name' => 'Workflows y Automatización',
                'description' => 'Creación y gestión de workflows',
                'icon' => 'heroicon-o-arrow-right-circle',
                'color' => 'info',
                'sort_order' => 3,
            ],
            [
                'key' => 'estados',
                'name' => 'Estados y Transiciones',
                'description' => 'Gestión de estados y procesos de aprobación',
                'icon' => 'heroicon-o-circle-stack',
                'color' => 'warning',
                'sort_order' => 4,
            ],
            [
                'key' => 'documentacion',
                'name' => 'Gestión de Documentos',
                'description' => 'Administración de documentos y archivos',
                'icon' => 'heroicon-o-document-text',
                'color' => 'secondary',
                'sort_order' => 5,
            ],
            [
                'key' => 'configuracion',
                'name' => 'Configuración del Sistema',
                'description' => 'Configuraciones generales y avanzadas',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'gray',
                'sort_order' => 6,
            ],
            [
                'key' => 'backup',
                'name' => 'Respaldos y Mantenimiento',
                'description' => 'Gestión de respaldos y mantenimiento del sistema',
                'icon' => 'heroicon-o-archive-box',
                'color' => 'danger',
                'sort_order' => 7,
            ],
            [
                'key' => 'monitoreo',
                'name' => 'Monitoreo del Sistema',
                'description' => 'Herramientas de monitoreo y análisis',
                'icon' => 'heroicon-o-chart-bar',
                'color' => 'info',
                'sort_order' => 8,
            ],
            [
                'key' => 'comunicaciones',
                'name' => 'Email y Comunicaciones',
                'description' => 'Configuración de emails y notificaciones',
                'icon' => 'heroicon-o-envelope',
                'color' => 'primary',
                'sort_order' => 9,
            ],
            [
                'key' => 'faq',
                'name' => 'Preguntas Frecuentes',
                'description' => 'Respuestas a preguntas comunes',
                'icon' => 'heroicon-o-question-mark-circle',
                'color' => 'secondary',
                'sort_order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            ManualCategory::updateOrCreate(
                ['key' => $category['key']],
                $category
            );
        }
    }
}

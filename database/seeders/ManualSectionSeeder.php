<?php

namespace Database\Seeders;

use App\Models\ManualSection;
use App\Models\User;

class ManualSectionSeeder extends \Illuminate\Database\Seeder
{
    public function run(): void
    {
        $adminUser = User::first();

        $sections = [
            // Introducción
            [
                'title' => 'Bienvenido al Sistema BaseLinking',
                'description' => 'Introducción general al sistema de gestión empresarial',
                'content' => '<h2>¡Bienvenido a BaseLinking!</h2><p>BaseLinking es un sistema completo de gestión empresarial que te permite administrar usuarios, documentos, workflows y configuraciones de manera eficiente.</p><h3>Características principales:</h3><ul><li>Gestión de usuarios y roles</li><li>Sistema de workflows automatizados</li><li>Estados de aprobación dinámicos</li><li>Gestión de documentos</li><li>Sistema de respaldos automático</li><li>Monitoreo en tiempo real</li></ul>',
                'category' => 'introduccion',
                'icon' => 'heroicon-o-home',
                'difficulty_level' => 'beginner',
                'is_featured' => true,
                'sort_order' => 1,
                'tags' => ['bienvenida', 'introduccion', 'basico'],
            ],

            // Usuarios
            [
                'title' => 'Gestión de Usuarios',
                'description' => 'Cómo crear, editar y administrar usuarios del sistema',
                'content' => '<h2>Gestión de Usuarios</h2><p>El sistema de usuarios te permite administrar quién tiene acceso al sistema y qué permisos tienen.</p><h3>Crear un nuevo usuario:</h3><ol><li>Ve a <strong>Gestión de Usuarios > Usuarios</strong></li><li>Haz clic en <strong>Crear Usuario</strong></li><li>Completa los campos requeridos</li><li>Asigna roles apropiados</li><li>Guarda los cambios</li></ol><h3>Roles disponibles:</h3><ul><li><strong>Super Admin:</strong> Acceso completo al sistema</li><li><strong>Admin:</strong> Gestión general</li><li><strong>Usuario:</strong> Acceso básico</li></ul>',
                'category' => 'usuarios',
                'resource_related' => 'UserResource',
                'icon' => 'heroicon-o-users',
                'difficulty_level' => 'beginner',
                'sort_order' => 1,
                'tags' => ['usuarios', 'roles', 'permisos'],
            ],

            // Workflows
            [
                'title' => 'Introducción a Workflows',
                'description' => 'Conceptos básicos sobre el sistema de workflows automatizados',
                'content' => '<h2>Sistema de Workflows</h2><p>Los workflows te permiten automatizar procesos de negocio mediante reglas y acciones predefinidas.</p><h3>Tipos de workflows:</h3><ul><li><strong>Asistente de Workflows:</strong> Interfaz simplificada para usuarios básicos</li><li><strong>Workflows Avanzados:</strong> Configuración completa para usuarios expertos</li></ul><h3>Casos de uso comunes:</h3><ul><li>Aprobación de documentos</li><li>Notificaciones automáticas</li><li>Cambios de estado</li><li>Asignación de tareas</li></ul>',
                'category' => 'workflows',
                'resource_related' => 'WorkflowWizardResource',
                'icon' => 'heroicon-o-cog-6-tooth',
                'difficulty_level' => 'intermediate',
                'is_featured' => true,
                'sort_order' => 1,
                'tags' => ['workflows', 'automatizacion', 'procesos'],
            ],

            [
                'title' => 'Crear un Workflow con el Asistente',
                'description' => 'Guía paso a paso para crear workflows usando el asistente',
                'content' => '<h2>Crear un Workflow con el Asistente</h2><p>El Asistente de Workflows te guía a través de 5 pasos simples para crear automatizaciones.</p><h3>Paso 1: Tipo de Automatización</h3><p>Selecciona qué tipo de proceso quieres automatizar.</p><h3>Paso 2: Modelo Objetivo</h3><p>Elige sobre qué tipo de datos actuará el workflow.</p><h3>Paso 3: Evento Desencadenador</h3><p>Define cuándo se ejecutará el workflow.</p><h3>Paso 4: Plantilla de Email</h3><p>Configura las notificaciones que se enviarán.</p><h3>Paso 5: Vista Previa</h3><p>Revisa y confirma la configuración antes de activar.</p>',
                'category' => 'workflows',
                'resource_related' => 'WorkflowWizardResource',
                'icon' => 'heroicon-o-sparkles',
                'difficulty_level' => 'beginner',
                'sort_order' => 2,
                'tags' => ['asistente', 'crear', 'paso-a-paso'],
            ],

            // Estados
            [
                'title' => 'Estados de Aprobación',
                'description' => 'Cómo funcionan los estados y transiciones en el sistema',
                'content' => '<h2>Estados de Aprobación</h2><p>Los estados definen las diferentes fases por las que puede pasar un documento o proceso.</p><h3>Estados típicos:</h3><ul><li><strong>Borrador:</strong> Estado inicial, se puede editar</li><li><strong>Pendiente de Aprobación:</strong> Esperando revisión</li><li><strong>Aprobado:</strong> Listo para publicación</li><li><strong>Rechazado:</strong> Requiere modificaciones</li><li><strong>Publicado:</strong> Visible para todos</li><li><strong>Archivado:</strong> Estado final</li></ul><h3>Configuración:</h3><p>Cada estado puede tener color, icono y comportamiento específico.</p>',
                'category' => 'estados',
                'resource_related' => 'ApprovalStateResource',
                'icon' => 'heroicon-o-circle-stack',
                'difficulty_level' => 'intermediate',
                'sort_order' => 1,
                'tags' => ['estados', 'aprobacion', 'flujos'],
            ],

            // Configuración
            [
                'title' => 'Configuración General del Sistema',
                'description' => 'Cómo personalizar la configuración básica del sistema',
                'content' => '<h2>Configuración General</h2><p>Personaliza la apariencia y comportamiento general del sistema.</p><h3>Configuraciones disponibles:</h3><ul><li><strong>Información General:</strong> Nombre de la aplicación, logos, contacto</li><li><strong>Apariencia:</strong> Colores, fuentes, tema</li><li><strong>Localización:</strong> Idioma, zona horaria, formatos</li></ul><h3>Acceso:</h3><p>Ve a <strong>Configuración</strong> en el menú principal para ajustar estas opciones.</p>',
                'category' => 'configuracion',
                'icon' => 'heroicon-o-adjustments-horizontal',
                'difficulty_level' => 'beginner',
                'sort_order' => 1,
                'tags' => ['configuracion', 'personalizacion', 'sistema'],
            ],

            // FAQ
            [
                'title' => 'Preguntas Frecuentes',
                'description' => 'Respuestas a las dudas más comunes del sistema',
                'content' => '<h2>Preguntas Frecuentes</h2><h3>¿Cómo puedo cambiar mi contraseña?</h3><p>Ve a tu perfil en el menú superior derecho y selecciona "Mi Perfil".</p><h3>¿Qué pasa si olvido mi contraseña?</h3><p>Usa la opción "¿Olvidaste tu contraseña?" en la página de login.</p><h3>¿Cómo puedo ver el historial de cambios?</h3><p>Cada recurso tiene una pestaña de "Logs de Actividad" donde puedes ver el historial.</p><h3>¿Puedo personalizar las notificaciones?</h3><p>Sí, en la sección de Email Templates puedes modificar las plantillas de notificación.</p>',
                'category' => 'faq',
                'icon' => 'heroicon-o-question-mark-circle',
                'difficulty_level' => 'beginner',
                'is_featured' => true,
                'sort_order' => 1,
                'tags' => ['faq', 'preguntas', 'ayuda', 'soporte'],
            ],
        ];

        foreach ($sections as $sectionData) {
            $sectionData['created_by'] = $adminUser?->id;
            $sectionData['updated_by'] = $adminUser?->id;
            $sectionData['is_active'] = true;

            ManualSection::firstOrCreate(
                [
                    'title' => $sectionData['title'],
                    'category' => $sectionData['category'],
                ],
                $sectionData
            );
        }
    }
}

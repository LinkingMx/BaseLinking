<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateExamplesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Template para respaldo exitoso
        EmailTemplate::firstOrCreate(
            ['key' => 'backup-success'],
            [
                'name' => 'Respaldo Exitoso',
                'subject' => '✅ Respaldo {{backup_name}} completado exitosamente',
                'content' => '<div style="padding: 30px;">
                    <h2 style="color: #10b981; margin-bottom: 20px;">🎉 ¡Respaldo Completado!</h2>
                    
                    <p style="margin-bottom: 15px;">Hola {{user_name}},</p>
                    
                    <p style="margin-bottom: 20px;">
                        El respaldo <strong>{{backup_name}}</strong> se ha completado exitosamente.
                    </p>
                    
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0;">
                        <h3 style="color: #166534; margin: 0 0 15px 0;">📊 Detalles del Respaldo</h3>
                        <ul style="margin: 0; color: #374151;">
                            <li><strong>Tamaño:</strong> {{backup_size}}</li>
                            <li><strong>Duración:</strong> {{backup_duration}}</li>
                            <li><strong>Destino:</strong> {{backup_destination}}</li>
                            <li><strong>Fecha:</strong> {{backup_date}}</li>
                        </ul>
                    </div>
                    
                    <p style="color: #6b7280; font-size: 14px;">
                        Este respaldo se guardó automáticamente en tu almacenamiento configurado.
                    </p>
                </div>',
                'language' => 'es',
                'is_active' => true,
                'description' => 'Template para notificar respaldos exitosos'
            ]
        );

        // Template para bienvenida de usuario
        EmailTemplate::firstOrCreate(
            ['key' => 'user-welcome'],
            [
                'name' => 'Bienvenida de Usuario',
                'subject' => '🎉 ¡Bienvenido a {{app_name}}!',
                'content' => '<div style="padding: 30px;">
                    <h2 style="color: #3b82f6; margin-bottom: 20px;">¡Hola {{user_name}}! 👋</h2>
                    
                    <p style="margin-bottom: 20px;">
                        ¡Nos complace darte la bienvenida a <strong>{{app_name}}</strong>! 
                        Tu cuenta ha sido creada exitosamente.
                    </p>
                    
                    <div style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); 
                               color: white; 
                               padding: 20px; 
                               border-radius: 8px; 
                               margin: 25px 0;">
                        <h3 style="margin: 0 0 15px 0;">🚀 ¿Qué puedes hacer ahora?</h3>
                        <ul style="margin: 0; padding-left: 20px;">
                            <li>Explorar todas las funcionalidades</li>
                            <li>Configurar tu perfil personalizado</li>
                            <li>Contactar con nuestro equipo de soporte</li>
                        </ul>
                    </div>
                    
                    <div style="text-align: center; margin: 25px 0;">
                        <a href="{{app_url}}/dashboard" 
                           style="background: #10b981; 
                                  color: white; 
                                  padding: 12px 30px; 
                                  text-decoration: none; 
                                  border-radius: 25px; 
                                  font-weight: bold; 
                                  display: inline-block;">
                            Acceder a mi cuenta
                        </a>
                    </div>
                    
                    <p style="color: #6b7280; font-size: 14px; margin-top: 25px;">
                        Si tienes alguna pregunta, no dudes en contactarnos.
                    </p>
                </div>',
                'language' => 'es',
                'is_active' => true,
                'description' => 'Template de bienvenida para nuevos usuarios'
            ]
        );

        // Template para banco creado
        EmailTemplate::firstOrCreate(
            ['key' => 'bank-created'],
            [
                'name' => 'Banco Creado',
                'subject' => '🏦 Nuevo {{model_name}} registrado: {{model_title}}',
                'content' => '<div style="padding: 30px;">
                    <h2 style="color: #059669; margin-bottom: 20px;">🏦 ¡Nuevo Banco Registrado!</h2>
                    
                    <p style="margin-bottom: 15px;">Hola {{user_name}},</p>
                    
                    <p style="margin-bottom: 20px;">
                        Se ha registrado un nuevo {{model_name}} en el sistema.
                    </p>
                    
                    <div style="background: #f0fdfa; border-left: 4px solid #14b8a6; padding: 20px; margin: 20px 0;">
                        <h3 style="color: #0f766e; margin: 0 0 15px 0;">📋 Información del Registro</h3>
                        <ul style="margin: 0; color: #374151;">
                            <li><strong>Nombre:</strong> {{model_title}}</li>
                            <li><strong>ID:</strong> {{model_id}}</li>
                            <li><strong>Acción:</strong> {{action_type}}</li>
                            <li><strong>Usuario:</strong> {{action_user}}</li>
                            <li><strong>Fecha:</strong> {{action_date}}</li>
                        </ul>
                    </div>
                    
                    <div style="text-align: center; margin: 25px 0;">
                        <a href="{{record_url}}" 
                           style="background: #1f2937; 
                                  color: white; 
                                  padding: 10px 25px; 
                                  text-decoration: none; 
                                  border-radius: 6px; 
                                  font-weight: 500; 
                                  display: inline-block;">
                            Ver {{model_name}}
                        </a>
                    </div>
                </div>',
                'language' => 'es',
                'is_active' => true,
                'description' => 'Template para notificar cuando se crea un nuevo banco'
            ]
        );

        // Template para documento aprobado
        EmailTemplate::firstOrCreate(
            ['key' => 'document-approved'],
            [
                'name' => 'Documento Aprobado',
                'subject' => '✅ Documento "{{document_title}}" ha sido aprobado',
                'content' => '<div style="padding: 30px;">
                    <h2 style="color: #059669; margin-bottom: 20px;">✅ ¡Documento Aprobado!</h2>
                    
                    <p style="margin-bottom: 15px;">Hola {{user_name}},</p>
                    
                    <p style="margin-bottom: 20px;">
                        Te informamos que el documento <strong>"{{document_title}}"</strong> 
                        ha sido aprobado y está disponible para su consulta.
                    </p>
                    
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0;">
                        <h3 style="color: #166534; margin: 0 0 15px 0;">📄 Detalles del Documento</h3>
                        <ul style="margin: 0; color: #374151;">
                            <li><strong>ID:</strong> {{document_id}}</li>
                            <li><strong>Estado:</strong> {{document_status}}</li>
                            <li><strong>Creador:</strong> {{creator_name}}</li>
                            <li><strong>Fecha de creación:</strong> {{created_date}}</li>
                        </ul>
                    </div>
                    
                    <div style="text-align: center; margin: 25px 0;">
                        <a href="{{document_url}}" 
                           style="background: #7c3aed; 
                                  color: white; 
                                  padding: 12px 30px; 
                                  text-decoration: none; 
                                  border-radius: 6px; 
                                  font-weight: 500; 
                                  display: inline-block;">
                            Ver Documento
                        </a>
                    </div>
                    
                    <p style="color: #6b7280; font-size: 14px; margin-top: 25px;">
                        El documento ya está disponible en la sección de documentación.
                    </p>
                </div>',
                'language' => 'es',
                'is_active' => true,
                'description' => 'Template para notificar cuando un documento es aprobado'
            ]
        );
    }
}

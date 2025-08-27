<?php

namespace Database\Seeders;

use App\Models\CustomStyle;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomStyleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        $styles = [
            [
                'name' => 'Colores Personalizados Admin',
                'description' => 'Personalización de colores primarios para el panel de administración',
                'target' => 'admin',
                'css_content' => ':root {
    --primary-50: rgb(239 246 255);
    --primary-100: rgb(219 234 254);
    --primary-200: rgb(191 219 254);
    --primary-300: rgb(147 197 253);
    --primary-400: rgb(96 165 250);
    --primary-500: rgb(59 130 246);
    --primary-600: rgb(37 99 235);
    --primary-700: rgb(29 78 216);
    --primary-800: rgb(30 64 175);
    --primary-900: rgb(30 58 138);
    --primary-950: rgb(23 37 84);
}

/* Personalizar botones principales */
.fi-btn-primary {
    background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.fi-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
}',
                'is_active' => false,
                'priority' => 1,
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ],
            [
                'name' => 'Estilos Frontend Corporativos',
                'description' => 'Estilos corporativos para páginas públicas del sistema',
                'target' => 'frontend',
                'css_content' => '/* Estilos Corporativos */
.btn-corporate {
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-corporate:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

/* Header personalizado */
.header-corporate {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 20px 0;
}

/* Cards con efecto hover */
.card-hover {
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}',
                'is_active' => false,
                'priority' => 2,
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ],
            [
                'name' => 'Animaciones y Transiciones',
                'description' => 'Animaciones CSS personalizadas para mejorar la experiencia de usuario',
                'target' => 'both',
                'css_content' => '/* Animaciones personalizadas */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 40px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

@keyframes bounce {
    0%, 20%, 53%, 80%, 100% {
        transform: translate3d(0, 0, 0);
    }
    40%, 43% {
        transform: translate3d(0, -8px, 0);
    }
    70% {
        transform: translate3d(0, -4px, 0);
    }
    90% {
        transform: translate3d(0, -2px, 0);
    }
}

/* Clases utilitarias */
.animate-fadeInUp {
    animation: fadeInUp 0.6s ease-out;
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-bounce {
    animation: bounce 1s infinite;
}

/* Transiciones suaves */
.transition-all {
    transition: all 0.3s ease;
}

.hover-scale:hover {
    transform: scale(1.05);
}

.hover-shadow:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}',
                'is_active' => false,
                'priority' => 10,
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ],
            [
                'name' => 'Ocultar Elementos Admin',
                'description' => 'CSS para ocultar elementos específicos del panel de administración',
                'target' => 'admin',
                'css_content' => '/* Ocultar elementos específicos del admin */

/* Ocultar widget de Filament Info (ejemplo) */
.fi-wi-stats-overview-widget:last-child {
    display: none !important;
}

/* Ocultar elementos del sidebar (ejemplo) */
.fi-sidebar-nav-item[href*="activity-log"] {
    display: none !important;
}

/* Personalizar el logo */
.fi-logo {
    filter: brightness(1.1) contrast(1.1);
}

/* Reducir padding en móviles */
@media (max-width: 768px) {
    .fi-main {
        padding: 1rem;
    }
}

/* Estilo personalizado para notificaciones */
.fi-no-content {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 8px;
    border: 1px dashed #d1d5db;
}',
                'is_active' => false,
                'priority' => 5,
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ],
        ];

        foreach ($styles as $style) {
            CustomStyle::create($style);
        }
    }
}

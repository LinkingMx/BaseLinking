<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        @if ($this->css_content)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-eye class="w-5 h-5" />
                        Vista Previa del CSS
                    </div>
                </x-slot>

                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4">
                    <div class="mb-4">
                        <x-filament::badge color="info">
                            Objetivo: {{ \App\Models\CustomStyle::getTargetOptions()[$this->target] ?? $this->target }}
                        </x-filament::badge>

                        @if ($this->is_active)
                            <x-filament::badge color="success">
                                Activo
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="gray">
                                Inactivo
                            </x-filament::badge>
                        @endif

                        <x-filament::badge color="warning">
                            Prioridad: {{ $this->priority }}
                        </x-filament::badge>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded border p-3 text-sm font-mono">
                        <pre class="whitespace-pre-wrap text-xs overflow-x-auto">{{ $this->css_content }}</pre>
                    </div>
                </div>
            </x-filament::section>
        @endif

        @if ($this->target === 'frontend' || $this->target === 'both')
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-information-circle class="w-5 h-5" />
                        Información - Frontend
                    </div>
                </x-slot>

                <div class="prose dark:prose-invert max-w-none">
                    <p>Los estilos para <strong>Frontend</strong> se aplicarán en:</p>
                    <ul>
                        <li>Páginas públicas del sistema</li>
                        <li>Páginas de autenticación (login, registro)</li>
                        <li>Cualquier vista pública que uses</li>
                    </ul>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>Nota:</strong> Los estilos se cargan después de los CSS del framework.
                    </p>
                </div>
            </x-filament::section>
        @endif

        @if ($this->target === 'admin' || $this->target === 'both')
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-information-circle class="w-5 h-5" />
                        Información - Admin Panel
                    </div>
                </x-slot>

                <div class="prose dark:prose-invert max-w-none">
                    <p>Los estilos para <strong>Admin Panel</strong> se aplicarán en:</p>
                    <ul>
                        <li>Todo el panel de administración Filament</li>
                        <li>Páginas, recursos, formularios, tablas</li>
                        <li>Componentes personalizados de Filament</li>
                    </ul>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>Ejemplos útiles:</strong> Personalizar colores de Filament, estilos de componentes
                        específicos, etc.
                    </p>
                </div>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-light-bulb class="w-5 h-5" />
                    Consejos y Plantillas
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-blue-50 dark:bg-blue-950 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Personalizar Colores Filament</h4>
                    <pre class="text-xs bg-white dark:bg-gray-800 p-2 rounded text-blue-800 dark:text-blue-200">:root {
    --primary-50: rgb(239 246 255);
    --primary-500: rgb(59 130 246);
    --primary-600: rgb(37 99 235);
}</pre>
                </div>

                <div class="bg-green-50 dark:bg-green-950 rounded-lg p-4">
                    <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">Estilos para Frontend</h4>
                    <pre class="text-xs bg-white dark:bg-gray-800 p-2 rounded text-green-800 dark:text-green-200">.btn-custom {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border-radius: 8px;
}</pre>
                </div>

                <div class="bg-purple-50 dark:bg-purple-950 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-900 dark:text-purple-100 mb-2">Ocultar Elementos</h4>
                    <pre class="text-xs bg-white dark:bg-gray-800 p-2 rounded text-purple-800 dark:text-purple-200">.fi-sidebar-nav-item[href*="users"] {
    display: none !important;
}</pre>
                </div>

                <div class="bg-orange-50 dark:bg-orange-950 rounded-lg p-4">
                    <h4 class="font-semibold text-orange-900 dark:text-orange-100 mb-2">Animaciones CSS</h4>
                    <pre class="text-xs bg-white dark:bg-gray-800 p-2 rounded text-orange-800 dark:text-orange-200">@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.fade-in { animation: fadeIn 0.3s; }</pre>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>

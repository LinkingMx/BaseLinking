<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="templateBuilder" class="space-y-6">
        
        <!-- Template Presets -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <x-heroicon-o-document-text class="w-5 h-5" />
Plantillas Prediseñadas
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Comienza con una plantilla y personalízala</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($getTemplatePresets() as $key => $preset)
                    <button 
                        type="button"
                        @click="loadPreset('{{ $key }}')"
                        class="text-left p-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:border-primary-300 hover:shadow-sm transition-all duration-200 group"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl">{{ $preset['icon'] }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $preset['name'] }}</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                            {{ Str::limit($preset['content'], 80) }}
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
        
        <!-- Style Selector -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <x-heroicon-o-paint-brush class="w-5 h-5" />
Estilo del Email
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach ($getStylePresets() as $styleKey => $style)
                    <button 
                        type="button"
                        @click="selectStyle('{{ $styleKey }}')"
                        :class="currentStyle === '{{ $styleKey }}' ? 'ring-2 ring-primary-500 border-primary-300' : 'border-gray-200 dark:border-gray-600'"
                        class="p-3 bg-white dark:bg-gray-700 border rounded-lg hover:border-primary-300 transition-all duration-200 text-left"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-4 h-4 rounded {{ $style['preview_bg'] }} {{ $style['preview_border'] }} border"></div>
                            <span class="font-medium text-sm">{{ $style['name'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $style['description'] }}</p>
                    </button>
                @endforeach
            </div>
        </div>
        
        <!-- Content Editor -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Editor Side -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
Asunto del Email
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="emailSubject"
                            @input="updatePreview"
                            placeholder="Escribe el asunto del email..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        />
                        <button 
                            type="button"
                            @click="showVariablePicker = !showVariablePicker"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1 text-gray-400 hover:text-primary-500 transition-colors"
                            title="Insertar variable"
                        >
                            <x-heroicon-o-variable class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
Contenido del Email
                        </label>
                        <div class="flex items-center gap-2">
                            <button 
                                type="button"
                                @click="showVariablePicker = !showVariablePicker"
                                class="text-xs px-2 py-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-md hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors"
                                title="Mostrar variables disponibles"
                            >
                                Variables
                            </button>
                            <button 
                                type="button"
                                @click="insertSampleVariables"
                                class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                title="Insertar variables comunes"
                            >
                                Ejemplo
                            </button>
                        </div>
                    </div>
                    
                    <textarea 
                        x-model="emailContent"
                        @input="updatePreview"
                        rows="12"
                        placeholder="Escribe el contenido de tu email aquí...&#10;&#10;Puedes usar variables como:&#10;{{nombre}} - Nombre del usuario&#10;{{email}} - Email del usuario&#10;{{app_name}} - Nombre de la aplicación"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white font-mono text-sm resize-none"
                    ></textarea>
                </div>
                
                <!-- Variable Picker Collapsible -->
                <div x-show="showVariablePicker" x-transition class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium text-gray-900 dark:text-white">Variables Disponibles</h4>
                        <button @click="showVariablePicker = false" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto">
                        @foreach ($getAvailableVariables() as $variable)
                            <button 
                                type="button"
                                @click="insertVariable('{{ $variable['key'] }}')"
                                class="text-left p-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded text-xs hover:border-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all"
                            >
                                <code class="text-primary-600 dark:text-primary-400 font-mono">{{"{{"}}{{ $variable['key'] }}{{"}}"}}</code>
                                <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $variable['description'] }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="flex gap-2 flex-wrap">
                    <button 
                        type="button"
                        @click="insertVariable('nombre')"
                        class="text-xs px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors"
                    >
                        + {{nombre}}
                    </button>
                    <button 
                        type="button"
                        @click="insertVariable('email')"
                        class="text-xs px-3 py-1 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full hover:bg-green-200 dark:hover:bg-green-800 transition-colors"
                    >
                        + {{email}}
                    </button>
                    <button 
                        type="button"
                        @click="insertVariable('app_name')"
                        class="text-xs px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-full hover:bg-purple-200 dark:hover:bg-purple-800 transition-colors"
                    >
                        + {{app_name}}
                    </button>
                    <button 
                        type="button"
                        @click="insertVariable('current_date')"
                        class="text-xs px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-full hover:bg-yellow-200 dark:hover:bg-yellow-800 transition-colors"
                    >
                        + {{fecha}}
                    </button>
                </div>
            </div>
            
            <!-- Preview Side -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-eye class="w-5 h-5" />
Vista Previa
                    </h3>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="previewDevice = 'desktop'"
                            :class="previewDevice === 'desktop' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600'"
                            class="p-1.5 rounded transition-colors"
                            title="Vista escritorio"
                        >
                            <x-heroicon-o-computer-desktop class="w-4 h-4" />
                        </button>
                        <button 
                            @click="previewDevice = 'mobile'"
                            :class="previewDevice === 'mobile' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600'"
                            class="p-1.5 rounded transition-colors"
                            title="Vista móvil"
                        >
                            <x-heroicon-o-device-phone-mobile class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                
                <div 
                    :class="previewDevice === 'mobile' ? 'max-w-sm mx-auto' : 'w-full'"
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-lg transition-all duration-300"
                >
                    <!-- Email Header -->
                    <div class="bg-gray-100 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-envelope class="w-4 h-4 text-gray-500" />
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                    <strong>Para:</strong> juan@empresa.com
                                </p>
                                <p class="text-xs text-gray-900 dark:text-gray-100 truncate font-medium" x-text="emailSubject || 'Asunto del email'"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Content -->
                    <div class="p-0 bg-gray-50 dark:bg-gray-900 min-h-96">
                        <div x-html="previewContent" class="prose prose-sm max-w-none"></div>
                    </div>
                </div>
                
                <!-- Preview Info -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-information-circle class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <p class="font-medium">Vista Previa en Tiempo Real</p>
                            <p class="text-blue-700 dark:text-blue-300 mt-1">Esta es una representación de cómo se verá el email. Las variables se reemplazarán con datos reales cuando se envíe.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($getCanSaveAsTemplate())
        <!-- Save as Template Section -->
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-green-800 dark:text-green-200 flex items-center gap-2">
                    <x-heroicon-o-bookmark class="w-5 h-5" />
                    Guardar como Plantilla Reutilizable
                </h3>
                <button 
                    type="button"
                    @click="showSaveTemplate = !showSaveTemplate"
                    class="text-sm px-3 py-1 bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-300 rounded-md hover:bg-green-200 dark:hover:bg-green-700 transition-colors"
                >
                    <span x-text="showSaveTemplate ? 'Ocultar' : 'Mostrar'"></span>
                </button>
            </div>
            
            <div x-show="showSaveTemplate" x-transition class="space-y-4">
                <p class="text-sm text-green-700 dark:text-green-300">
                    ¿Te gusta como quedó este email? Guárdalo como plantilla para reutilizarlo en otros workflows.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-1">
                            Nombre de la plantilla
                        </label>
                        <input 
                            type="text" 
                            x-model="templateName"
                            placeholder="ej: Bienvenida a nuevos usuarios"
                            class="w-full px-3 py-2 border border-green-300 dark:border-green-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-green-900/50 dark:text-green-100 text-sm"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-1">
                            Clave única
                        </label>
                        <input 
                            type="text" 
                            x-model="templateKey"
                            placeholder="ej: user_welcome"
                            class="w-full px-3 py-2 border border-green-300 dark:border-green-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-green-900/50 dark:text-green-100 text-sm font-mono"
                        />
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">Solo letras, números y guiones bajos</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-information-circle class="w-4 h-4 text-green-600 dark:text-green-400" />
                        <span class="text-xs text-green-600 dark:text-green-400">
                            La plantilla incluirá el asunto, contenido y estilo actual
                        </span>
                    </div>
                    
                    <button 
                        type="button"
                        @click="saveAsTemplate"
                        :disabled="!templateName || !templateKey || savingTemplate"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-green-300 disabled:cursor-not-allowed transition-colors text-sm font-medium flex items-center gap-2"
                    >
                        <x-heroicon-o-bookmark class="w-4 h-4" />
                        <span x-text="savingTemplate ? 'Guardando...' : 'Guardar Plantilla'"></span>
                    </button>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Hidden inputs for form integration -->
        <input type="hidden" :value="emailSubject" name="{{ $getName() }}_subject" />
        <input type="hidden" :value="emailContent" name="{{ $getName() }}_content" />
        <input type="hidden" :value="currentStyle" name="{{ $getName() }}_style" />
    </div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('templateBuilder', () => ({
                emailSubject: '{{ $getPreviewSubject() ?? "" }}',
                emailContent: '',
                currentStyle: '{{ $getTemplateStyle() }}',
                previewContent: '',
                showVariablePicker: false,
                previewDevice: 'desktop',
                showSaveTemplate: false,
                templateName: '',
                templateKey: '',
                savingTemplate: false,
                
                presets: @json($getTemplatePresets()),
                
                init() {
                    this.updatePreview();
                    
                    // Listen for variable selection events
                    window.addEventListener('variable-selected', (event) => {
                        this.insertVariable(event.detail.key);
                    });
                },
                
                loadPreset(presetKey) {
                    const preset = this.presets[presetKey];
                    if (preset) {
                        this.emailSubject = preset.subject;
                        this.emailContent = preset.content;
                        this.updatePreview();
                    }
                },
                
                selectStyle(style) {
                    this.currentStyle = style;
                    this.updatePreview();
                },
                
                insertVariable(key) {
                    const variable = '{{' + key + '}}';
                    
                    // Get cursor position in textarea
                    const textarea = document.querySelector('textarea[x-model="emailContent"]');
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    
                    // Insert variable at cursor position
                    this.emailContent = this.emailContent.slice(0, start) + variable + this.emailContent.slice(end);
                    
                    // Update preview
                    this.updatePreview();
                    
                    // Restore cursor position
                    this.$nextTick(() => {
                        textarea.focus();
                        textarea.setSelectionRange(start + variable.length, start + variable.length);
                    });
                },
                
                insertSampleVariables() {
                    const sampleText = `Hola {{nombre}},

¡Gracias por usar {{app_name}}!

Este es un mensaje de ejemplo que muestra cómo puedes usar variables en tus emails.

Información de tu cuenta:
- Email: {{email}}
- Fecha de registro: {{current_date}}

¡Saludos!
El equipo de {{app_name}}`;
                    
                    this.emailContent = sampleText;
                    this.updatePreview();
                },
                
                updatePreview() {
                    // Generate preview with sample data
                    const sampleData = {
                        'nombre': 'Juan Pérez',
                        'email': 'juan@empresa.com',
                        'app_name': 'Mi Aplicación',
                        'current_date': new Date().toLocaleDateString('es-ES'),
                        'current_time': new Date().toLocaleTimeString('es-ES'),
                    };
                    
                    let content = this.emailContent;
                    let subject = this.emailSubject;
                    
                    // Replace variables with sample data
                    Object.keys(sampleData).forEach(key => {
                        const regex = new RegExp('{{' + key + '}}', 'g');
                        content = content.replace(regex, sampleData[key]);
                        subject = subject.replace(regex, sampleData[key]);
                    });
                    
                    // Apply styling
                    this.previewContent = this.wrapWithStyle(content, subject);
                },
                
                wrapWithStyle(content, subject) {
                    const htmlContent = content.replace(/\n/g, '<br>');
                    
                    switch(this.currentStyle) {
                        case 'simple':
                            return `<div style="padding: 20px; font-family: Arial, sans-serif;">${htmlContent}</div>`;
                            
                        case 'modern':
                            return `
                                <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px; overflow: hidden;">
                                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white;">
                                        <h1 style="margin: 0; font-size: 24px; font-weight: bold;">${subject}</h1>
                                    </div>
                                    <div style="background: white; padding: 30px; color: #374151; font-size: 16px; line-height: 1.6;">
                                        ${htmlContent}
                                    </div>
                                    <div style="background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px;">
                                        Este email fue enviado automáticamente por Mi Aplicación
                                    </div>
                                </div>
                            `;
                            
                        case 'corporate':
                            return `
                                <div style="max-width: 600px; margin: 0 auto; font-family: 'Segoe UI', sans-serif; border: 1px solid #d1d5db; border-radius: 4px; overflow: hidden;">
                                    <div style="background: #1f2937; padding: 25px; color: white;">
                                        <h1 style="margin: 0; font-size: 20px; font-weight: normal;">${subject}</h1>
                                    </div>
                                    <div style="background: white; padding: 40px; color: #111827; font-size: 16px; line-height: 1.7;">
                                        ${htmlContent}
                                    </div>
                                    <div style="background: #f3f4f6; padding: 15px; text-align: center; color: #6b7280; font-size: 12px;">
                                        Mi Aplicación - Notificación Automática
                                    </div>
                                </div>
                            `;
                            
                        case 'friendly':
                            return `
                                <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; border: 3px solid #fbbf24; border-radius: 12px; overflow: hidden;">
                                    <div style="background: #fbbf24; padding: 25px; text-align: center; color: #92400e;">
                                        <h1 style="margin: 0; font-size: 22px; font-weight: bold;">${subject}</h1>
                                    </div>
                                    <div style="background: #fffbeb; padding: 30px; color: #92400e; font-size: 16px; line-height: 1.6;">
                                        ${htmlContent}
                                    </div>
                                    <div style="background: #fef3c7; padding: 20px; text-align: center; color: #92400e; font-size: 14px;">
¡Gracias por usar Mi Aplicación!
                                    </div>
                                </div>
                            `;
                            
                        default:
                            return htmlContent;
                    }
                },

                async saveAsTemplate() {
                    if (!this.templateName || !this.templateKey) {
                        return;
                    }

                    this.savingTemplate = true;

                    try {
                        // Validar clave única (solo letras, números y guiones bajos)
                        if (!/^[a-zA-Z0-9_]+$/.test(this.templateKey)) {
                            throw new Error('La clave solo puede contener letras, números y guiones bajos');
                        }

                        const templateData = {
                            subject: this.emailSubject,
                            content: this.emailContent,
                            style: this.currentStyle
                        };

                        // Hacer la petición para guardar la plantilla
                        const response = await fetch('{{ route("filament.admin.save-template") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                template_key: this.templateKey,
                                template_name: this.templateName,
                                template_data: templateData
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Mostrar notificación de éxito
                            if (window.$wireui) {
                                window.$wireui.notify({
                                    title: 'Plantilla guardada',
                                    description: `La plantilla "${this.templateName}" se ha guardado correctamente`,
                                    icon: 'success'
                                });
                            }

                            // Limpiar formulario
                            this.templateName = '';
                            this.templateKey = '';
                            this.showSaveTemplate = false;
                        } else {
                            throw new Error(result.message || 'Error al guardar la plantilla');
                        }

                    } catch (error) {
                        // Mostrar notificación de error
                        if (window.$wireui) {
                            window.$wireui.notify({
                                title: 'Error',
                                description: error.message,
                                icon: 'error'
                            });
                        } else {
                            alert('Error: ' + error.message);
                        }
                    } finally {
                        this.savingTemplate = false;
                    }
                }
            }))
        });
    </script>
</x-dynamic-component>
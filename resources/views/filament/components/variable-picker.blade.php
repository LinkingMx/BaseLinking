<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="variablePicker" class="space-y-4">
        <div class="grid grid-cols-1 gap-4">
            <!-- Search Bar -->
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input 
                    type="text" 
                    x-model="searchTerm"
                    placeholder="Buscar variables... (ej: nombre, email, fecha)"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                />
            </div>
            
            <!-- Categories -->
            <div class="space-y-4">
                @foreach ($getVariablesByCategory() as $category => $variables)
                    <div x-show="categoryHasVisibleVariables('{{ $category }}')" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            {{ $category }}
                            <span class="text-xs bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded-full" x-text="getVisibleVariablesCount('{{ $category }}')"></span>
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($variables as $variable)
                                <div 
                                    x-show="variableMatchesSearch('{{ $variable['key'] }}', '{{ $variable['description'] }}')"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="group cursor-pointer bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3 hover:border-primary-300 hover:shadow-sm transition-all duration-200"
                                    @click="insertVariable('{{ $variable['key'] }}')"
                                >
                                    <div class="flex items-start gap-3">
                                        <span class="text-xl flex-shrink-0 mt-0.5">{{ $getVariableIcon($variable['key']) }}</span>
                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <code class="text-sm font-mono bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 px-2 py-1 rounded text-xs">
                                                    {{ '{{' . $variable['key'] . '}}' }}
                                                </code>
                                                <button 
                                                    type="button"
                                                    @click.stop="copyToClipboard('{{ $variable['key'] }}')"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded"
                                                    title="Copiar variable"
                                                >
                                                    <x-heroicon-o-clipboard class="w-4 h-4 text-gray-500" />
                                                </button>
                                            </div>
                                            
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $variable['description'] }}</p>
                                            
                                            <div class="text-xs text-gray-500 dark:text-gray-500 bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded">
                                                <span class="font-medium">Ejemplo:</span> {{ $getVariableExample($variable['key']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Empty State -->
            <div x-show="!hasVisibleVariables()" x-transition class="text-center py-8">
                <x-heroicon-o-magnifying-glass class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                <p class="text-gray-500 dark:text-gray-400">No se encontraron variables que coincidan con tu búsqueda</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Intenta con otros términos como "nombre", "email" o "fecha"</p>
            </div>
        </div>
        
        <!-- Usage Instructions -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-4">
            <div class="flex items-start gap-3">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-medium mb-1">Cómo usar las variables:</p>
                    <ul class="space-y-1 text-blue-700 dark:text-blue-300">
                        <li>• Haz clic en cualquier variable para insertarla en tu texto</li>
                        <li>• Las variables se reemplazarán automáticamente con datos reales</li>
                        <li>• Puedes combinar múltiples variables en el mismo mensaje</li>
                        <li>• Usa el buscador para encontrar rápidamente la variable que necesitas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('variablePicker', () => ({
                searchTerm: '',
                
                variableMatchesSearch(key, description) {
                    if (!this.searchTerm) return true;
                    
                    const searchTerm = this.searchTerm.toLowerCase();
                    const keyMatch = key.toLowerCase().includes(searchTerm);
                    const descMatch = description.toLowerCase().includes(searchTerm);
                    
                    return keyMatch || descMatch;
                },
                
                categoryHasVisibleVariables(category) {
                    const variables = @json($getVariablesByCategory());
                    const categoryVars = variables[category] || [];
                    
                    return categoryVars.some(variable => 
                        this.variableMatchesSearch(variable.key, variable.description)
                    );
                },
                
                getVisibleVariablesCount(category) {
                    const variables = @json($getVariablesByCategory());
                    const categoryVars = variables[category] || [];
                    
                    return categoryVars.filter(variable => 
                        this.variableMatchesSearch(variable.key, variable.description)
                    ).length;
                },
                
                hasVisibleVariables() {
                    const variables = @json($getVariablesByCategory());
                    
                    return Object.values(variables).some(categoryVars => 
                        categoryVars.some(variable => 
                            this.variableMatchesSearch(variable.key, variable.description)
                        )
                    );
                },
                
                insertVariable(key) {
                    const variable = '{{' + key + '}}';
                    
                    // Emitir evento para que otros components lo capturen
                    window.dispatchEvent(new CustomEvent('variable-selected', {
                        detail: { variable: variable, key: key }
                    }));
                    
                    // Mostrar notificación
                    this.$dispatch('notify', {
                        message: `Variable ${variable} seleccionada`,
                        type: 'success'
                    });
                },
                
                async copyToClipboard(key) {
                    const variable = '{{' + key + '}}';
                    
                    try {
                        await navigator.clipboard.writeText(variable);
                        this.$dispatch('notify', {
                            message: `${variable} copiado al portapapeles`,
                            type: 'success'
                        });
                    } catch (err) {
                        console.error('Error copiando al portapapeles:', err);
                        this.$dispatch('notify', {
                            message: 'Error al copiar la variable',
                            type: 'error'
                        });
                    }
                }
            }))
        });
    </script>
</x-dynamic-component>
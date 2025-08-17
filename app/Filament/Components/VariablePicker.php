<?php

namespace App\Filament\Components;

use App\Services\ModelIntrospectionService;
use Filament\Forms\Components\Concerns\HasState;
use Filament\Forms\Components\Field;

class VariablePicker extends Field
{
    use HasState;

    protected string $view = 'filament.components.variable-picker';

    protected ?string $targetModel = null;

    protected array $availableVariables = [];

    public function targetModel(?string $model): static
    {
        $this->targetModel = $model;
        $this->loadAvailableVariables();

        return $this;
    }

    public function getTargetModel(): ?string
    {
        return $this->targetModel;
    }

    public function getAvailableVariables(): array
    {
        return $this->availableVariables;
    }

    protected function loadAvailableVariables(): void
    {
        if (! $this->targetModel) {
            $this->availableVariables = [];

            return;
        }

        try {
            $introspectionService = app(ModelIntrospectionService::class);
            $modelInfo = $introspectionService->getModelInfo($this->targetModel);
            $this->availableVariables = $modelInfo['available_variables'] ?? [];
        } catch (\Exception $e) {
            $this->availableVariables = [];
        }
    }

    public function getVariablesByCategory(): array
    {
        $grouped = [];

        foreach ($this->availableVariables as $variable) {
            $category = $this->getFriendlyCategory($variable['category'] ?? 'other');
            $grouped[$category][] = $variable;
        }

        return $grouped;
    }

    protected function getFriendlyCategory(string $category): string
    {
        return match ($category) {
            'model_field' => 'Información Básica',
            'relation_field' => 'Información Relacionada',
            'user_field' => 'Datos de Usuario',
            'date_formatted' => 'Fechas y Tiempo',
            'computed' => 'Valores Calculados',
            'custom' => 'Variables Personalizadas',
            default => 'Otros'
        };
    }

    public function getVariableIcon(string $key): string
    {
        return ''; // No icons, following project's no-emoji policy
    }

    public function getVariableExample(string $key): string
    {
        return match (true) {
            str_contains($key, 'name') => 'Juan Pérez',
            str_contains($key, 'email') => 'juan@empresa.com',
            str_contains($key, 'phone') => '+52 55 1234 5678',
            str_contains($key, 'date') => now()->format('d/m/Y'),
            str_contains($key, 'created') => now()->format('d/m/Y H:i'),
            str_contains($key, 'status') => 'Activo',
            str_contains($key, 'state') => 'Completado',
            str_contains($key, 'price') => '$1,299.99',
            str_contains($key, 'amount') => '$500.00',
            str_contains($key, 'id') => '#12345',
            str_contains($key, 'number') => 'ORD-2024-001',
            str_contains($key, 'title') => 'Título de ejemplo',
            str_contains($key, 'description') => 'Descripción de ejemplo...',
            str_contains($key, 'url') => 'https://ejemplo.com',
            str_contains($key, 'address') => 'Calle Ejemplo 123',
            default => 'Valor de ejemplo'
        };
    }
}

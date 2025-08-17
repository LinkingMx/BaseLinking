<?php

namespace App\Services;

use App\Models\ApprovalState;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StateClassRegistry
{
    /**
     * Cache key para el mapeo de estados
     */
    private const CACHE_KEY = 'state_class_registry';

    private const CACHE_TTL = 3600; // 1 hora

    /**
     * Resolver clase de estado desde ApprovalState
     */
    public function resolveStateClass(ApprovalState $approvalState): ?string
    {
        // Si ya tiene state_class definido, usarlo directamente
        if (! empty($approvalState->state_class)) {
            if (class_exists($approvalState->state_class)) {
                return $approvalState->state_class;
            }

            Log::warning('State class not found', [
                'state_class' => $approvalState->state_class,
                'approval_state_id' => $approvalState->id,
            ]);
        }

        // Fallback a auto-resolución basada en convenciones
        return $this->autoResolveStateClass($approvalState);
    }

    /**
     * Auto-resolución basada en convenciones de naming
     */
    private function autoResolveStateClass(ApprovalState $approvalState): ?string
    {
        $stateName = $approvalState->name;
        $modelType = $approvalState->model_type;

        // Mapeo de convenciones comunes
        $conventions = $this->getStateClassConventions();

        foreach ($conventions as $pattern => $className) {
            if ($stateName === $pattern) {
                if (class_exists($className)) {
                    return $className;
                }
            }
        }

        // Intentar resolución por nombre usando PascalCase
        $pascalCaseStateName = str($stateName)
            ->replace('_', ' ')
            ->title()
            ->replace(' ', '')
            ->append('State');

        $potentialClass = "App\\States\\{$pascalCaseStateName}";

        if (class_exists($potentialClass)) {
            return $potentialClass;
        }

        Log::warning('Could not auto-resolve state class', [
            'approval_state_id' => $approvalState->id,
            'state_name' => $stateName,
            'model_type' => $modelType,
            'attempted_class' => $potentialClass,
        ]);

        return null;
    }

    /**
     * Obtener convenciones de mapeo de estados
     */
    private function getStateClassConventions(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return [
                // Estados básicos
                'draft' => \App\States\DraftState::class,
                'pending_approval' => \App\States\PendingApprovalState::class,
                'approved' => \App\States\ApprovedState::class,
                'rejected' => \App\States\RejectedState::class,
                'published' => \App\States\PublishedState::class,
                'archived' => \App\States\ArchivedState::class,

                // Estados específicos de workflow
                'pending_supervisor' => \App\States\PendingSupervisorState::class,
                'approved_supervisor_pending_travel' => \App\States\ApprovedSupervisorPendingTravelState::class,
                'approved_travel_pending_treasury' => \App\States\ApprovedTravelPendingTreasuryState::class,
                'fully_approved' => \App\States\FullyApprovedState::class,
            ];
        });
    }

    /**
     * Limpiar cache de convenciones
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Registrar nueva convención de estado
     */
    public function registerStateClass(string $stateName, string $stateClass): void
    {
        if (! class_exists($stateClass)) {
            throw new \InvalidArgumentException("State class {$stateClass} does not exist");
        }

        $conventions = $this->getStateClassConventions();
        $conventions[$stateName] = $stateClass;

        Cache::put(self::CACHE_KEY, $conventions, self::CACHE_TTL);

        Log::info('State class registered', [
            'state_name' => $stateName,
            'state_class' => $stateClass,
        ]);
    }

    /**
     * Obtener todas las clases de estado disponibles
     */
    public function getAvailableStateClasses(): array
    {
        $stateClasses = [];
        $stateDirectory = app_path('States');

        if (! is_dir($stateDirectory)) {
            return [];
        }

        $files = glob($stateDirectory.'/*.php');

        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClassName = "App\\States\\{$className}";

            if (class_exists($fullClassName) && $className !== 'DocumentationState') {
                $stateClasses[$fullClassName] = $className;
            }
        }

        return $stateClasses;
    }

    /**
     * Validar que una clase de estado es válida
     */
    public function isValidStateClass(string $stateClass): bool
    {
        if (! class_exists($stateClass)) {
            return false;
        }

        try {
            $reflection = new \ReflectionClass($stateClass);

            // Verificar que extiende de una clase de estado válida
            return $reflection->isSubclassOf(\Spatie\ModelStates\State::class) ||
                   $reflection->isSubclassOf(\App\States\DocumentationState::class);
        } catch (\Exception $e) {
            Log::error('Error validating state class', [
                'state_class' => $stateClass,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Obtener información de una clase de estado
     */
    public function getStateClassInfo(string $stateClass): array
    {
        if (! $this->isValidStateClass($stateClass)) {
            return [];
        }

        try {
            $reflection = new \ReflectionClass($stateClass);

            return [
                'class' => $stateClass,
                'name' => $reflection->getShortName(),
                'file' => $reflection->getFileName(),
                'methods' => array_map(
                    fn ($method) => $method->getName(),
                    $reflection->getMethods(\ReflectionMethod::IS_PUBLIC)
                ),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting state class info', [
                'state_class' => $stateClass,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}

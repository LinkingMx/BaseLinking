<?php

namespace App\Services;

use App\Models\ApprovalState;
use App\Models\StateTransition;
use App\Models\StateTransitionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StateTransitionLogService
{
    /**
     * Crear log de transición de estado
     */
    public function createTransitionLog(
        Model $model,
        StateTransition $transition,
        ApprovalState $fromState,
        ApprovalState $toState,
        ?string $comment = null,
        ?User $user = null,
        array $metadata = []
    ): ?StateTransitionLog {
        try {
            $log = StateTransitionLog::createLog(
                $model,
                $transition,
                $fromState,
                $toState,
                $comment,
                $user,
                $metadata
            );

            // Limpiar cache relacionado
            $this->clearRelatedCache($model);

            // Log para debugging
            Log::info('State transition log created', [
                'log_id' => $log->id,
                'model' => get_class($model),
                'model_id' => $model->getKey(),
                'transition' => $transition->name,
                'from_state' => $fromState->name,
                'to_state' => $toState->name,
                'user_id' => $user?->id,
                'has_comment' => ! empty($comment),
            ]);

            return $log;

        } catch (\Exception $e) {
            Log::error('Failed to create state transition log', [
                'error' => $e->getMessage(),
                'model' => get_class($model),
                'model_id' => $model->getKey(),
                'transition_id' => $transition->id,
                'from_state_id' => $fromState->id,
                'to_state_id' => $toState->id,
            ]);

            return null;
        }
    }

    /**
     * Obtener historial de transiciones para un modelo
     */
    public function getTransitionHistory(Model $model, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return StateTransitionLog::getLogsForModel($model, $limit);
    }

    /**
     * Obtener historial paginado para interfaces
     */
    public function getPaginatedHistory(Model $model, int $perPage = 25)
    {
        return StateTransitionLog::getLogsForModelPaginated($model, $perPage);
    }

    /**
     * Obtener estadísticas de transiciones para un modelo
     */
    public function getTransitionStats(Model $model): array
    {
        $cacheKey = 'transition_stats_'.get_class($model).'_'.$model->getKey();

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($model) {
            $logs = StateTransitionLog::forModel($model)->get();

            $stats = [
                'total_transitions' => $logs->count(),
                'unique_users' => $logs->pluck('user_id')->unique()->count(),
                'transitions_with_comments' => $logs->whereNotNull('comment')->count(),
                'most_common_transition' => null,
                'first_transition_date' => null,
                'last_transition_date' => null,
            ];

            if ($logs->isNotEmpty()) {
                $stats['first_transition_date'] = $logs->min('created_at');
                $stats['last_transition_date'] = $logs->max('created_at');

                // Transición más común
                $transitionCounts = $logs->groupBy('state_transition_id')
                    ->map->count()
                    ->sortDesc();

                if ($transitionCounts->isNotEmpty()) {
                    $mostCommonTransitionId = $transitionCounts->keys()->first();
                    $mostCommonTransition = $logs->where('state_transition_id', $mostCommonTransitionId)->first();
                    $stats['most_common_transition'] = $mostCommonTransition?->stateTransition?->label;
                }
            }

            return $stats;
        });
    }

    /**
     * Limpiar logs antiguos (para mantenimiento)
     */
    public function cleanupOldLogs(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        $deletedCount = StateTransitionLog::where('created_at', '<', $cutoffDate)
            ->delete();

        Log::info('Old state transition logs cleaned up', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate,
        ]);

        return $deletedCount;
    }

    /**
     * Limpiar cache relacionado con logs de transición
     */
    private function clearRelatedCache(Model $model): void
    {
        $modelClass = get_class($model);
        $modelId = $model->getKey();

        // Patrones de cache a limpiar
        $patterns = [
            'state_logs_'.$modelClass.'_'.$modelId.'_*',
            'transition_stats_'.$modelClass.'_'.$modelId,
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Verificar integridad de logs de transición
     */
    public function verifyLogIntegrity(Model $model): array
    {
        $logs = StateTransitionLog::forModel($model)
            ->with(['stateTransition', 'fromState', 'toState', 'user'])
            ->orderBy('created_at')
            ->get();

        $issues = [];
        $previousLog = null;

        foreach ($logs as $log) {
            // Verificar que las referencias existen
            if (! $log->stateTransition) {
                $issues[] = "Log ID {$log->id}: Missing state transition reference";
            }

            if (! $log->fromState) {
                $issues[] = "Log ID {$log->id}: Missing from state reference";
            }

            if (! $log->toState) {
                $issues[] = "Log ID {$log->id}: Missing to state reference";
            }

            if (! $log->user) {
                $issues[] = "Log ID {$log->id}: Missing user reference";
            }

            // Verificar continuidad de estados
            if ($previousLog && $previousLog->to_state_id !== $log->from_state_id) {
                $issues[] = "Log ID {$log->id}: State continuity broken from previous log";
            }

            $previousLog = $log;
        }

        return [
            'total_logs' => $logs->count(),
            'issues_found' => count($issues),
            'issues' => $issues,
            'is_valid' => empty($issues),
        ];
    }
}

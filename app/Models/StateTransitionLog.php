<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StateTransitionLog extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'state_transition_id',
        'from_state_id',
        'to_state_id',
        'user_id',
        'comment',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relación polimórfica con el modelo que cambió de estado
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    /**
     * Relación con la transición utilizada
     */
    public function stateTransition(): BelongsTo
    {
        return $this->belongsTo(StateTransition::class);
    }

    /**
     * Relación con el estado origen
     */
    public function fromState(): BelongsTo
    {
        return $this->belongsTo(ApprovalState::class, 'from_state_id');
    }

    /**
     * Relación con el estado destino
     */
    public function toState(): BelongsTo
    {
        return $this->belongsTo(ApprovalState::class, 'to_state_id');
    }

    /**
     * Relación con el usuario que ejecutó la transición
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para logs de un modelo específico
     */
    public function scopeForModel($query, Model $model)
    {
        return $query->where('model_type', get_class($model))
            ->where('model_id', $model->id);
    }

    /**
     * Scope para ordenar por fecha más reciente primero
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Obtener descripción legible de la transición
     */
    public function getDescriptionAttribute(): string
    {
        return "{$this->user->name} cambió el estado de '{$this->fromState->label}' a '{$this->toState->label}'";
    }

    /**
     * Obtener logs para un modelo específico con cache y paginación
     */
    public static function getLogsForModel(Model $model, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = 'state_logs_'.get_class($model).'_'.$model->id.'_limit_'.$limit;

        return cache()->remember($cacheKey, now()->addMinutes(15), function () use ($model, $limit) {
            return static::forModel($model)
                ->with(['user:id,name,email', 'fromState:id,label,color', 'toState:id,label,color', 'stateTransition:id,label'])
                ->latest()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Obtener logs paginados para interfaces grandes
     */
    public static function getLogsForModelPaginated(Model $model, int $perPage = 20)
    {
        return static::forModel($model)
            ->with(['user:id,name,email', 'fromState:id,label,color', 'toState:id,label,color', 'stateTransition:id,label'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Limpiar cache de logs cuando se crea uno nuevo
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($log) {
            $cachePattern = 'state_logs_'.$log->model_type.'_'.$log->model_id.'_*';
            cache()->forget($cachePattern);
        });
    }

    /**
     * Crear log de transición
     */
    public static function createLog(
        Model $model,
        StateTransition $stateTransition,
        ApprovalState $fromState,
        ApprovalState $toState,
        ?string $comment = null,
        ?User $user = null,
        array $metadata = []
    ): self {
        return static::create([
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'state_transition_id' => $stateTransition->id,
            'from_state_id' => $fromState->id,
            'to_state_id' => $toState->id,
            'user_id' => ($user ?? auth()->user())->id,
            'comment' => $comment,
            'metadata' => array_merge([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ], $metadata),
        ]);
    }
}

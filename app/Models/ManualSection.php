<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ManualSection extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'content',
        'category',
        'resource_related',
        'sort_order',
        'is_active',
        'is_featured',
        'tags',
        'icon',
        'difficulty_level',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'tags' => 'array',
    ];

    // Relaciones
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(ManualCategory::class, 'category', 'key');
    }

    public function resourceModel(): BelongsTo
    {
        return $this->belongsTo(ManualResource::class, 'resource_related', 'key');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDifficulty($query, string $level)
    {
        return $query->where('difficulty_level', $level);
    }

    // Métodos utilitarios
    public static function getCategories(): array
    {
        return ManualCategory::getOptions();
    }

    public static function getCategoryOptions(): array
    {
        return self::getCategories();
    }

    public static function getDifficultyLevels(): array
    {
        return [
            'beginner' => 'Principiante',
            'intermediate' => 'Intermedio',
            'advanced' => 'Avanzado',
        ];
    }

    public static function getResourceOptions(): array
    {
        return \App\Models\ManualResource::getOptions();
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'category', 'is_active', 'is_featured'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

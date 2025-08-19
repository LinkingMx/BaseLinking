<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualResource extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'class_name',
        'url',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function manualSections(): HasMany
    {
        return $this->hasMany(ManualSection::class, 'resource_related', 'key');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getOptions(): array
    {
        return static::active()
            ->orderBy('sort_order')
            ->pluck('name', 'key')
            ->toArray();
    }

    public static function getColorOptions(): array
    {
        return [
            'primary' => 'Primario',
            'secondary' => 'Secundario',
            'success' => 'Éxito',
            'warning' => 'Advertencia',
            'danger' => 'Peligro',
            'info' => 'Información',
            'gray' => 'Gris',
        ];
    }
}

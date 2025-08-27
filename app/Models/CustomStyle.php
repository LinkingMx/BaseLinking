<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomStyle extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'target',
        'css_content',
        'scss_content',
        'is_active',
        'priority',
        'is_minified',
        'backup_content',
        'version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_minified' => 'boolean',
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTarget($query, string $target)
    {
        return $query->where('target', $target)->orWhere('target', 'both');
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    // Métodos utilitarios
    public static function getTargetOptions(): array
    {
        return [
            'frontend' => 'Frontend (Páginas Públicas)',
            'admin' => 'Admin Panel (Filament)',
            'both' => 'Ambos (Frontend y Admin)',
        ];
    }

    public static function getActiveStyles(string $target = 'frontend'): string
    {
        $styles = static::active()
            ->forTarget($target)
            ->byPriority()
            ->get();

        return $styles->pluck('css_content')->filter()->implode("\n\n");
    }

    public function createBackup(): void
    {
        $this->update([
            'backup_content' => $this->css_content,
        ]);
    }

    public function restoreFromBackup(): void
    {
        if ($this->backup_content) {
            $this->update([
                'css_content' => $this->backup_content,
            ]);
        }
    }

    public function incrementVersion(): void
    {
        $version = $this->version;
        $parts = explode('.', $version);
        $parts[2] = (int)$parts[2] + 1;
        
        $this->update([
            'version' => implode('.', $parts),
        ]);
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'target', 'is_active', 'version'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

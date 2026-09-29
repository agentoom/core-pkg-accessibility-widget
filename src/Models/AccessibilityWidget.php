<?php

namespace Agentoom\AccessibilityWidget\Models;

use Agentoom\Core\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AccessibilityWidget extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'is_active',
        'position',
        'logo',
        'icon_trigger',
        'settings',
        'allowed_domains',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
            'allowed_domains' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AccessibilityWidget $widget) {
            if (empty($widget->slug)) {
                $widget->slug = Str::random(16);
                while (static::where('slug', $widget->slug)->exists()) {
                    $widget->slug = Str::random(16);
                }
            }

            if (empty($widget->settings)) {
                $widget->settings = config('accessibility-widget.defaults');
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// R-LEG-01/02 : pages légales
class Page extends Model
{
    protected $fillable = [
        'slug', 'title', 'content', 'is_published', 'meta_description', 'content_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'content_updated_at' => 'datetime',
        ];
    }

    /** R-LEG-02 : une page n'est visible que publiée ET non vide. */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereNotNull('content')
            ->where('content', '!=', '');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

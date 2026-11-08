<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// R-CONTENU-01 : une section de page modifiable par l'admin
class ContentSection extends Model
{
    protected $fillable = ['page', 'key', 'content', 'is_visible', 'sort_order'];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeForPage(Builder $query, string $page): void
    {
        $query->where('page', $page)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true);
    }
}

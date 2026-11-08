<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// URLs propres et uniques en italien : « Legna di faggio » → « legna-di-faggio », puis « legna-di-faggio-2 »…
final class SlugGenerator
{
    /** @param class-string<Model> $modelClass */
    public static function unique(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'elemento';
        $slug = $base;
        $i = 2;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}

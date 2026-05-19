<?php

namespace App\Support;

use Illuminate\Support\Str;

class SlugHelper
{
    /**
     * Converte texto em slug URL (minúsculas, hífens, sem acentos).
     * Se $value estiver vazio, usa $fallback (ex.: título).
     */
    public static function normalize(?string $value, ?string $fallback = null): ?string
    {
        $slug = $value !== null && trim($value) !== ''
            ? Str::slug(trim($value))
            : '';

        if ($slug !== '') {
            return $slug;
        }

        if ($fallback !== null && trim($fallback) !== '') {
            return Str::slug(trim($fallback));
        }

        return null;
    }

    /**
     * Garante slug único acrescentando -2, -3, … quando necessário.
     */
    public static function ensureUnique(string $slug, callable $exists): string
    {
        $base = $slug;
        $i = 2;

        while ($exists($slug)) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}

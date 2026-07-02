<?php

namespace App\Support;

use Illuminate\Http\Request;

class PaginationPerPage
{
    public const OPTIONS = [10, 20, 50, 100];

    public static function resolve(Request $request, int $default = 10): int
    {
        $perPage = (int) $request->query('per_page', $default);

        return in_array($perPage, self::OPTIONS, true) ? $perPage : $default;
    }
}

<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginationRange
{
    /**
     * Gera sequência compacta: ex. 1, 2, ..., 11, 12
     *
     * @return array<int|string>
     */
    public static function pages(LengthAwarePaginator $paginator, int $leading = 2, int $trailing = 2): array
    {
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();

        if ($last <= $leading + $trailing + 1) {
            return range(1, $last);
        }

        $pages = range(1, $leading);
        $trailingStart = $last - $trailing + 1;
        $windowStart = max($leading + 1, $current - 1);
        $windowEnd = min($trailingStart - 1, $current + 1);

        if ($windowStart <= $windowEnd) {
            if ($windowStart > $leading + 1) {
                $pages[] = '...';
            }

            for ($i = $windowStart; $i <= $windowEnd; $i++) {
                $pages[] = $i;
            }
        } elseif ($leading + 1 < $trailingStart) {
            $pages[] = '...';
        }

        if ($windowEnd < $trailingStart - 1 && ($pages[array_key_last($pages)] ?? null) !== '...') {
            $pages[] = '...';
        }

        for ($i = $trailingStart; $i <= $last; $i++) {
            $pages[] = $i;
        }

        return self::normalize($pages);
    }

    /**
     * @param  array<int|string>  $pages
     * @return array<int|string>
     */
    private static function normalize(array $pages): array
    {
        $result = [];

        foreach ($pages as $page) {
            if ($page === '...' && ($result[array_key_last($result)] ?? null) === '...') {
                continue;
            }

            if ($page !== '...' && in_array($page, $result, true)) {
                continue;
            }

            $result[] = $page;
        }

        return $result;
    }
}

<?php

namespace App\Support;

use Illuminate\Http\Request;

class ResolvesIndexPagination
{
    /**
     * @var list<int>
     */
    public const ALLOWED_PER_PAGE = [10, 15, 25, 50, 100];

    public static function perPage(Request $request, int $default = 15): int
    {
        $perPage = (int) $request->integer('per_page', $default);

        return in_array($perPage, self::ALLOWED_PER_PAGE, true) ? $perPage : $default;
    }

    public static function search(Request $request, string $key = 'q'): ?string
    {
        $search = trim((string) $request->string($key));

        return $search !== '' ? $search : null;
    }
}

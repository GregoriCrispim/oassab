<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RespondsWithFormModal
{
    protected function wantsFormModal(Request $request): bool
    {
        return $request->ajax()
            || $request->wantsJson()
            || $request->header('X-Form-Modal') === '1';
    }

    protected function formModalJson(string $title, string $partial, array $data): JsonResponse
    {
        return response()->json([
            'title' => $title,
            'html' => view($partial, $data)->render(),
        ]);
    }

    protected function formModalRedirect(string $indexUrl, string $formUrl): RedirectResponse
    {
        return redirect()->to($indexUrl.'?modal='.urlencode($formUrl));
    }

    protected function formModalSuccess(string $redirectUrl, string $message): JsonResponse
    {
        $separator = str_contains($redirectUrl, '?') ? '&' : '?';

        return response()->json([
            'redirect' => $redirectUrl.$separator.'status='.urlencode($message),
            'message' => $message,
        ]);
    }
}

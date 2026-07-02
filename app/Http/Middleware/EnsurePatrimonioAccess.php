<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatrimonioAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessPatrimonio()) {
            abort(403, 'Acesso negado ao módulo patrimonial.');
        }

        return $next($request);
    }
}

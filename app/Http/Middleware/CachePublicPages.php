<?php

namespace App\Http\Middleware;

use App\Services\ContentCache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Cache de página inteira (HTML) para visitantes anônimos em GET.
 *
 * Estratégia:
 * - Só cacheia GET sem parâmetros de query e usuário deslogado.
 * - Só cacheia respostas 200 com Content-Type text/html.
 * - É invalidado automaticamente pelo PostObserver via ContentCache::flushAll(),
 *   que faz Cache::flush() no store padrão (file).
 *
 * Stores como o "file" não suportam tags, então usamos chaves simples
 * baseadas na URI; o flush apaga tudo, o que é o comportamento desejado.
 */
class CachePublicPages
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (! $this->isCacheable($request)) {
            return $next($request);
        }

        $key = ContentCache::pageKey($request->path());

        if ($cached = Cache::get($key)) {
            return new Response($cached, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Page-Cache' => 'HIT',
                'Cache-Control' => 'public, max-age=0, must-revalidate',
            ]);
        }

        /** @var SymfonyResponse $response */
        $response = $next($request);

        if ($this->isStorable($response)) {
            Cache::put($key, $response->getContent(), ContentCache::TTL);
            $response->headers->set('X-Page-Cache', 'MISS');
        }

        return $response;
    }

    private function isCacheable(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->user()) {
            return false;
        }

        if ($request->hasAny(['preview', 'nocache'])) {
            return false;
        }

        // Não cachear se houver flash messages na sessão (ex.: pós-login do admin).
        if ($request->hasSession() && $request->session()->has('status')) {
            return false;
        }

        // Querystring simples é tolerada apenas se vazia.
        return count($request->query()) === 0;
    }

    private function isStorable(SymfonyResponse $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'text/html');
    }
}

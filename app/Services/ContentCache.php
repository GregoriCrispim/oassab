<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Cache de conteúdo público (queries do site).
 *
 * - Chaves nomeadas e listadas em $keys.
 * - Quando o admin grava/exclui um Post, o PostObserver chama flushAll().
 * - O store padrão (file) é resolvido via config/cache.php.
 */
class ContentCache
{
    /** Vida útil "longa" (1 ano). Invalidação acontece via observer. */
    public const TTL = 31_536_000;

    /** Tag/prefixo de cache de página HTML completo. */
    public const PAGE_PREFIX = 'page-html:';

    /**
     * Lista de todas as chaves "lógicas" usadas pelo site público.
     * Mantida explicitamente para que o flush seja simples e robusto
     * em stores que não suportam tags (como o driver "file").
     *
     * @var array<int, string>
     */
    public static array $keys = [
        'home:latest',
        'list:noticias',
        'list:projetos',
        'list:transparencia',
        'list:transparency-documents',
        'list:editais',
        'categories:all',
    ];

    public static function remember(string $key, Closure $callback)
    {
        return Cache::remember($key, self::TTL, $callback);
    }

    public static function forget(string $key): void
    {
        Cache::forget($key);
    }

    public static function postKey(string $slug): string
    {
        return 'post:'.$slug;
    }

    public static function neighborsKey(string $slug): string
    {
        return 'post:'.$slug.':neighbors';
    }

    public static function editalKey(string $slug): string
    {
        return 'edital:'.$slug;
    }

    public static function editalNeighborsKey(string $slug): string
    {
        return 'edital:'.$slug.':neighbors';
    }

    public static function pageKey(string $uri): string
    {
        return self::PAGE_PREFIX.($uri === '' ? '_root' : $uri);
    }

    /**
     * Apaga TODO o cache de conteúdo público.
     * Usado pelo PostObserver em saved/deleted.
     *
     * Como o driver "file" não suporta tags, fazemos um flush completo do store.
     * É seguro porque o cache é dedicado a conteúdo público (sessions e logs
     * usam outros stores/diretórios).
     */
    public static function flushAll(): void
    {
        Cache::flush();
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Versão do cache público (HTML)
    |--------------------------------------------------------------------------
    |
    | Incremente ao publicar correções de conteúdo/imagens para invalidar
    | páginas HTML cacheadas sem depender de php artisan cache:clear no servidor.
    |
    */
    'public_cache_version' => env('PUBLIC_CACHE_VERSION', '4'),

];

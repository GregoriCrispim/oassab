<?php

namespace App\Services\Patrimonio;

use App\Models\Patrimonio;
use App\Models\PatrimonioUnidade;

class CodigoPatrimonioService
{
    public function gerar(string $prefixo = 'PAT'): string
    {
        return $this->gerarUnicos(1, $prefixo)[0];
    }

    public function gerarUnicos(int $quantidade, string $prefixo = 'PAT'): array
    {
        $numero = $this->maiorNumero($prefixo);
        $codigos = [];

        for ($i = 0; $i < $quantidade; $i++) {
            $numero++;
            $codigos[] = $prefixo.'-'.str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
        }

        return $codigos;
    }

    private function maiorNumero(string $prefixo): int
    {
        $pattern = '/^'.preg_quote($prefixo, '/').'-(\d+)$/i';
        $max = 0;

        $fontes = Patrimonio::query()->pluck('codigo')
            ->merge(PatrimonioUnidade::query()->pluck('codigo'));

        Patrimonio::query()
            ->whereNotNull('codigos_inventario')
            ->pluck('codigos_inventario')
            ->each(function ($codigos) use (&$fontes) {
                foreach ((array) $codigos as $codigo) {
                    $fontes->push($codigo);
                }
            });

        foreach ($fontes as $codigo) {
            if (preg_match($pattern, (string) $codigo, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max;
    }
}

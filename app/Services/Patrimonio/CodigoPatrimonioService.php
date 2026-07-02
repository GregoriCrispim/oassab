<?php

namespace App\Services\Patrimonio;

use App\Models\Patrimonio;

class CodigoPatrimonioService
{
    public function gerar(string $prefixo = 'PAT'): string
    {
        $ultimo = Patrimonio::query()->orderByDesc('id')->value('codigo');

        if ($ultimo) {
            $numero = (int) preg_replace('/[^0-9]/', '', $ultimo);
            $novoNumero = $numero + 1;
        } else {
            $novoNumero = 1;
        }

        return $prefixo.'-'.str_pad((string) $novoNumero, 3, '0', STR_PAD_LEFT);
    }

    public function gerarUnicos(int $quantidade, string $prefixo = 'PAT'): array
    {
        $codigos = [];
        $base = Patrimonio::query()->orderByDesc('id')->value('codigo');
        $numero = $base ? (int) preg_replace('/[^0-9]/', '', $base) : 0;

        for ($i = 0; $i < $quantidade; $i++) {
            $numero++;
            $codigos[] = $prefixo.'-'.str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
        }

        return $codigos;
    }
}

<?php

namespace App\Services\Patrimonio;

class DepreciacaoService
{
    /**
     * @return array{valor_depreciado: float, valor_atual: float, anos_decorridos: float}
     */
    public function calcular(float $valorAquisicao, float $indiceDepreciacao, string $dataAquisicao): array
    {
        try {
            $dataAquisicaoObj = new \DateTime($dataAquisicao);
            $dataAtual = new \DateTime;

            $intervalo = $dataAquisicaoObj->diff($dataAtual);
            $anosDecorridos = $intervalo->y + ($intervalo->m / 12) + ($intervalo->d / 365);

            $valorDepreciado = $valorAquisicao * ($indiceDepreciacao / 100) * $anosDecorridos;
            $valorAtual = max(0, $valorAquisicao - $valorDepreciado);

            return [
                'valor_depreciado' => round($valorDepreciado, 2),
                'valor_atual' => round($valorAtual, 2),
                'anos_decorridos' => round($anosDecorridos, 2),
            ];
        } catch (\Exception) {
            return [
                'valor_depreciado' => 0,
                'valor_atual' => $valorAquisicao,
                'anos_decorridos' => 0,
            ];
        }
    }
}

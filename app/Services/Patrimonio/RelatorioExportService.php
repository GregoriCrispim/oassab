<?php

namespace App\Services\Patrimonio;

use App\Models\Orcamento;
use App\Models\Patrimonio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatorioExportService
{
    public function __construct(private readonly DepreciacaoService $depreciacao)
    {
    }

    public function patrimoniosCsv(Collection $patrimonios): StreamedResponse
    {
        return response()->streamDownload(function () use ($patrimonios) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Código', 'Quantidade', 'Nome', 'Categoria', 'Valor Unit. Aquisição', 'Valor Unit. Atual',
                'Depreciação %', 'Data Aquisição', 'Localização', 'Responsável', 'Ativo',
            ], ';');

            foreach ($patrimonios as $p) {
                $dep = $this->depreciacao->calcular(
                    (float) $p->valor_aquisicao,
                    (float) $p->indice_depreciacao,
                    $p->data_aquisicao->format('Y-m-d'),
                );

                fputcsv($handle, [
                    $p->codigoResumo(),
                    $p->unidades(),
                    $p->nome,
                    $p->categoria?->nome ?? '',
                    number_format((float) $p->valor_aquisicao, 2, ',', '.'),
                    number_format($dep['valor_atual'], 2, ',', '.'),
                    number_format((float) $p->indice_depreciacao, 2, ',', '.'),
                    $p->data_aquisicao->format('d/m/Y'),
                    $p->localizacao,
                    $p->responsavel,
                    $p->ativo ? 'Sim' : 'Não',
                ], ';');
            }

            fclose($handle);
        }, 'patrimonios_'.date('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function patrimoniosPdf(Collection $patrimonios)
    {
        $rows = $patrimonios->map(function (Patrimonio $p) {
            $dep = $this->depreciacao->calcular(
                (float) $p->valor_aquisicao,
                (float) $p->indice_depreciacao,
                $p->data_aquisicao->format('Y-m-d'),
            );

            return [
                'codigo' => $p->codigoResumo(),
                'quantidade' => $p->unidades(),
                'nome' => $p->nome,
                'categoria' => $p->categoria?->nome ?? '-',
                'valor_aquisicao' => $p->valor_aquisicao,
                'valor_atual' => $dep['valor_atual'],
                'data_aquisicao' => $p->data_aquisicao->format('d/m/Y'),
            ];
        });

        return Pdf::loadView('patrimonios.relatorios.patrimonios-pdf', [
            'rows' => $rows,
            'geradoEm' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');
    }

    public function orcamentosCsv(Collection $orcamentos): StreamedResponse
    {
        return response()->streamDownload(function () use ($orcamentos) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Item', 'Categoria', 'Quantidade', 'Prioridade', 'Status',
                'Data Necessidade', 'Solicitante', 'Propostas',
            ], ';');

            foreach ($orcamentos as $o) {
                fputcsv($handle, [
                    $o->nome_item,
                    $o->categoria?->nome ?? '',
                    $o->quantidade,
                    $o->prioridade,
                    $o->status,
                    $o->data_necessidade?->format('d/m/Y') ?? '',
                    $o->usuario_solicitante,
                    $o->propostas->count(),
                ], ';');
            }

            fclose($handle);
        }, 'orcamentos_'.date('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function orcamentosPdf(Collection $orcamentos)
    {
        return Pdf::loadView('patrimonios.relatorios.orcamentos-pdf', [
            'orcamentos' => $orcamentos,
            'geradoEm' => now()->format('d/m/Y H:i'),
        ]);
    }
}

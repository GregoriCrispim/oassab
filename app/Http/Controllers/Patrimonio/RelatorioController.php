<?php

namespace App\Http\Controllers\Patrimonio;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use App\Models\Patrimonio;
use App\Services\Patrimonio\RelatorioExportService;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    public function __construct(private readonly RelatorioExportService $export)
    {
    }

    public function patrimoniosCsv(Request $request)
    {
        $patrimonios = Patrimonio::query()->with('categoria')->ativos()->orderBy('codigo')->get();

        return $this->export->patrimoniosCsv($patrimonios);
    }

    public function patrimoniosPdf(Request $request)
    {
        $patrimonios = Patrimonio::query()->with('categoria')->ativos()->orderBy('codigo')->get();

        return $this->export->patrimoniosPdf($patrimonios)->download('patrimonios_'.date('Y-m-d').'.pdf');
    }

    public function orcamentosCsv(Request $request)
    {
        $orcamentos = Orcamento::query()->with(['categoria', 'propostas'])->orderByDesc('created_at')->get();

        return $this->export->orcamentosCsv($orcamentos);
    }

    public function orcamentosPdf(Request $request)
    {
        $orcamentos = Orcamento::query()->with(['categoria', 'propostas'])->orderByDesc('created_at')->get();

        return $this->export->orcamentosPdf($orcamentos)->download('orcamentos_'.date('Y-m-d').'.pdf');
    }
}

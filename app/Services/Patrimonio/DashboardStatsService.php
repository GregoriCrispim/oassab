<?php

namespace App\Services\Patrimonio;

use App\Models\Manutencao;
use App\Models\Patrimonio;
use App\Models\PatrimonioCategoria;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardStatsService
{
    public function __construct(private readonly DepreciacaoService $depreciacao)
    {
    }

    public function stats(): array
    {
        $patrimonios = Patrimonio::query()->ativos()->get();

        $valorAquisicao = 0.0;
        $valorTotal = 0.0;
        $valorDepreciado = 0.0;

        foreach ($patrimonios as $p) {
            $unidades = $p->unidades();
            $valorAquisicao += (float) $p->valor_aquisicao * $unidades;
            $dep = $this->depreciacao->calcular(
                (float) $p->valor_aquisicao,
                (float) $p->indice_depreciacao,
                $p->data_aquisicao->format('Y-m-d'),
            );
            $valorTotal += $dep['valor_atual'] * $unidades;
            $valorDepreciado += $dep['valor_depreciado'] * $unidades;
        }

        $valorDepreciado = round($valorDepreciado, 2);

        $porCategoria = PatrimonioCategoria::query()
            ->ativas()
            ->orderByDesc(
                Patrimonio::query()
                    ->selectRaw('COALESCE(SUM(quantidade), 0)')
                    ->whereColumn('patrimonio_categoria_id', 'patrimonio_categorias.id')
                    ->where('ativo', true)
            )
            ->limit(10)
            ->get()
            ->map(function (PatrimonioCategoria $cat) {
                $valorCat = 0.0;
                $quantidade = 0;
                $pats = Patrimonio::query()
                    ->ativos()
                    ->where('patrimonio_categoria_id', $cat->id)
                    ->get();

                foreach ($pats as $p) {
                    $unidades = $p->unidades();
                    $quantidade += $unidades;
                    $dep = $this->depreciacao->calcular(
                        (float) $p->valor_aquisicao,
                        (float) $p->indice_depreciacao,
                        $p->data_aquisicao->format('Y-m-d'),
                    );
                    $valorCat += $dep['valor_atual'] * $unidades;
                }

                return [
                    'nome' => $cat->nome,
                    'quantidade' => $quantidade,
                    'valor_total' => $valorCat,
                ];
            });

        $maisValiosos = $this->patrimoniosComValorAtual($patrimonios)
            ->sortByDesc('valor_atual')
            ->take(5)
            ->values();

        $recentes = Patrimonio::query()
            ->ativos()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function (Patrimonio $p) {
                $dep = $this->depreciacao->calcular(
                    (float) $p->valor_aquisicao,
                    (float) $p->indice_depreciacao,
                    $p->data_aquisicao->format('Y-m-d'),
                );

                return [
                    'id' => $p->id,
                    'codigo' => $p->codigo,
                    'nome' => $p->nome,
                    'valor_atual' => $dep['valor_atual'],
                    'created_at' => $p->created_at,
                ];
            });

        $proximasManutencoes = Manutencao::query()
            ->proximas(30)
            ->with('patrimonio')
            ->orderBy('proxima_manutencao')
            ->limit(5)
            ->get();

        return [
            'total_patrimonios' => (int) $patrimonios->sum(fn (Patrimonio $p) => $p->unidades()),
            'valor_total' => round($valorTotal, 2),
            'valor_aquisicao' => round($valorAquisicao, 2),
            'valor_depreciado' => round($valorDepreciado, 2),
            'percentual_depreciacao' => $valorAquisicao > 0 ? round(($valorDepreciado / $valorAquisicao) * 100, 2) : 0,
            'total_categorias' => PatrimonioCategoria::query()->ativas()->count(),
            'total_usuarios' => User::query()->whereNotNull('patrimonio_role')->orWhere('is_admin', true)->count(),
            'por_categoria' => $porCategoria,
            'mais_valiosos' => $maisValiosos,
            'recentes' => $recentes,
            'proximas_manutencoes' => $proximasManutencoes,
        ];
    }

    public function depreciacaoChart(): array
    {
        $categorias = PatrimonioCategoria::query()->ativas()->get();
        $labels = [];
        $valores = [];
        $depreciados = [];

        foreach ($categorias as $cat) {
            $pats = Patrimonio::query()->ativos()->where('patrimonio_categoria_id', $cat->id)->get();
            if ($pats->isEmpty()) {
                continue;
            }

            $atual = 0.0;
            $dep = 0.0;
            foreach ($pats as $p) {
                $unidades = $p->unidades();
                $calc = $this->depreciacao->calcular(
                    (float) $p->valor_aquisicao,
                    (float) $p->indice_depreciacao,
                    $p->data_aquisicao->format('Y-m-d'),
                );
                $atual += $calc['valor_atual'] * $unidades;
                $dep += $calc['valor_depreciado'] * $unidades;
            }

            $labels[] = $cat->nome;
            $valores[] = round($atual, 2);
            $depreciados[] = round($dep, 2);
        }

        return compact('labels', 'valores', 'depreciados');
    }

    private function patrimoniosComValorAtual(Collection $patrimonios): Collection
    {
        return $patrimonios->map(function (Patrimonio $p) {
            $dep = $this->depreciacao->calcular(
                (float) $p->valor_aquisicao,
                (float) $p->indice_depreciacao,
                $p->data_aquisicao->format('Y-m-d'),
            );

            return [
                'id' => $p->id,
                'codigo' => $p->codigo,
                'nome' => $p->nome,
                'valor_atual' => $dep['valor_atual'],
            ];
        });
    }
}

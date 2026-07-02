<?php

namespace Database\Seeders;

use App\Models\Patrimonio;
use App\Models\PatrimonioCategoria;
use App\Services\Patrimonio\DepreciacaoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PatrimonioPlanilhaSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/patrimonio_planilha.json');

        if (! file_exists($path)) {
            $this->command?->error("Arquivo não encontrado: {$path}");

            return;
        }

        $items = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $depreciacao = app(DepreciacaoService::class);

        $categorias = PatrimonioCategoria::query()
            ->pluck('id', 'nome')
            ->all();

        $importados = 0;
        $ignorados = 0;

        DB::transaction(function () use ($items, $depreciacao, $categorias, &$importados, &$ignorados) {
            foreach ($items as $item) {
                $sequencia = $item['sequencia'] ?? $item['numero_inventario'] ?? null;
                $codigo = 'INV-'.str_pad((string) $sequencia, 3, '0', STR_PAD_LEFT);

                if (Patrimonio::query()->where('codigo', $codigo)->exists()) {
                    $ignorados++;

                    continue;
                }

                $categoriaId = $categorias[$item['categoria']] ?? null;
                $categoria = $categoriaId ? PatrimonioCategoria::find($categoriaId) : null;
                $indiceDepreciacao = $categoria?->indice_depreciacao_padrao ?? 10.00;

                $dep = $depreciacao->calcular(
                    (float) $item['valor_aquisicao'],
                    (float) $indiceDepreciacao,
                    $item['data_aquisicao'],
                );

                $numeroPlanilha = $item['numero_inventario_planilha'] ?? $item['numero_inventario'] ?? null;
                $obsNumero = $numeroPlanilha
                    ? "Nº inventário na planilha: {$numeroPlanilha}. "
                    : 'Nº inventário na planilha: não informado. ';

                Patrimonio::create([
                    'codigo' => $codigo,
                    'quantidade' => 1,
                    'nome' => $item['nome'],
                    'descricao' => $item['descricao'],
                    'patrimonio_categoria_id' => $categoriaId,
                    'valor_aquisicao' => $item['valor_aquisicao'],
                    'indice_depreciacao' => $indiceDepreciacao,
                    'valor_depreciado' => $dep['valor_depreciado'],
                    'valor_atual' => $dep['valor_atual'],
                    'data_aquisicao' => $item['data_aquisicao'],
                    'localizacao' => $item['localizacao'],
                    'responsavel' => $item['responsavel'],
                    'observacoes' => $obsNumero
                        .'Importado da planilha patrimonial OASSAB (prestação de contas 01/05/2013 a 30/07/2013). '
                        .'Valor de aquisição estimado com base em pesquisa de preços médios de mercado (2025/2026).',
                    'ativo' => true,
                ]);

                $importados++;
            }
        });

        $this->command?->info("Patrimônios importados: {$importados}");
        if ($ignorados > 0) {
            $this->command?->warn("Patrimônios ignorados (código já existente): {$ignorados}");
        }
    }
}

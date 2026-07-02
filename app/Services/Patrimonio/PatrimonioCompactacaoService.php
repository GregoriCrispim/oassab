<?php

namespace App\Services\Patrimonio;

use App\Models\Manutencao;
use App\Models\Patrimonio;
use App\Models\PatrimonioArquivo;
use App\Models\PatrimonioCampoValor;
use App\Models\PatrimonioUnidade;
use Illuminate\Support\Facades\DB;

class PatrimonioCompactacaoService
{
    /**
     * @return array{grupos: int, removidos: int, registros: int}
     */
    public function compactar(): array
    {
        $grupos = 0;
        $removidos = 0;

        DB::transaction(function () use (&$grupos, &$removidos) {
            $registros = Patrimonio::query()->orderBy('codigo')->get();

            foreach ($registros->groupBy(fn (Patrimonio $p) => $this->chaveGrupo($p)) as $itens) {
                /** @var \Illuminate\Support\Collection<int, Patrimonio> $itens */
                if ($itens->count() === 1) {
                    $itens->first()->update([
                        'quantidade' => max(1, (int) $itens->first()->quantidade),
                    ]);

                    continue;
                }

                $grupos++;
                $principal = $itens->first();
                $codigos = $itens->pluck('codigo')->sort()->values()->all();

                foreach ($itens->where('id', '!=', $principal->id) as $duplicado) {
                    Manutencao::query()
                        ->where('patrimonio_id', $duplicado->id)
                        ->update(['patrimonio_id' => $principal->id]);

                    PatrimonioArquivo::query()
                        ->where('patrimonio_id', $duplicado->id)
                        ->update(['patrimonio_id' => $principal->id]);

                    PatrimonioCampoValor::query()
                        ->where('patrimonio_id', $duplicado->id)
                        ->delete();

                    $duplicado->delete();
                    $removidos++;
                }

                $principal->update([
                    'codigo' => $codigos[0],
                    'quantidade' => count($codigos),
                    'codigos_inventario' => array_values(array_slice($codigos, 1)),
                ]);

                PatrimonioUnidade::query()->where('patrimonio_id', $principal->id)->delete();

                foreach ($codigos as $ordem => $codigo) {
                    PatrimonioUnidade::create([
                        'patrimonio_id' => $principal->id,
                        'codigo' => $codigo,
                        'ordem' => $ordem,
                    ]);
                }
            }
        });

        return [
            'grupos' => $grupos,
            'removidos' => $removidos,
            'registros' => Patrimonio::query()->count(),
        ];
    }

    public function chaveGrupo(Patrimonio $patrimonio): string
    {
        return implode('|', [
            mb_strtolower(trim($patrimonio->nome)),
            mb_strtolower(trim($patrimonio->localizacao ?? '')),
            (string) ($patrimonio->patrimonio_categoria_id ?? 0),
            number_format((float) $patrimonio->valor_aquisicao, 2, '.', ''),
            number_format((float) $patrimonio->indice_depreciacao, 2, '.', ''),
            $patrimonio->ativo ? '1' : '0',
            mb_strtolower(trim($patrimonio->responsavel ?? '')),
            $patrimonio->data_aquisicao->format('Y-m-d'),
        ]);
    }
}

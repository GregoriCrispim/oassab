<?php

namespace App\Http\Controllers\Patrimonio;

use App\Http\Controllers\Controller;
use App\Models\Patrimonio;
use App\Models\PatrimonioUnidade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrScannerController extends Controller
{
    public function index(): View
    {
        return view('patrimonios.qr-scanner');
    }

    public function buscar(Request $request): JsonResponse
    {
        $dados = $request->input('dados', $request->input('codigo', ''));

        if (is_array($dados)) {
            $dados = json_encode($dados);
        }

        $dados = trim((string) $dados);

        if ($dados === '') {
            return response()->json(['sucesso' => false, 'mensagem' => 'Dados vazios'], 400);
        }

        $patrimonio = null;
        $json = json_decode($dados, true);
        $jsonValido = json_last_error() === JSON_ERROR_NONE;

        if ($jsonValido && isset($json['id'])) {
            $patrimonio = Patrimonio::with(['categoria', 'itensInventario'])->find($json['id']);
        }

        if (! $patrimonio && $jsonValido && ! empty($json['codigo'])) {
            $patrimonio = $this->buscarPorCodigo((string) $json['codigo']);
        }

        if (! $patrimonio) {
            $patrimonio = $this->buscarPorCodigo($dados);
        }

        if (! $patrimonio && preg_match('/(?:PAT|INV)-\d+/i', $dados, $m)) {
            $patrimonio = $this->buscarPorCodigo(strtoupper($m[0]));
        }

        if (! $patrimonio) {
            return response()->json(['sucesso' => false, 'mensagem' => 'Patrimônio não encontrado']);
        }

        $patrimonio->loadMissing('itensInventario');

        $codigoExibicao = $this->resolverCodigoExibicao($dados, $jsonValido ? $json : null, $patrimonio);

        return response()->json([
            'sucesso' => true,
            'patrimonio' => $this->montarResposta($patrimonio, $codigoExibicao),
        ]);
    }

    private function resolverCodigoExibicao(string $dados, ?array $json, Patrimonio $patrimonio): string
    {
        if ($json !== null && ! empty($json['codigo'])) {
            return (string) $json['codigo'];
        }

        if (preg_match('/(?:PAT|INV)-\d+/i', $dados, $m)) {
            return strtoupper($m[0]);
        }

        if (in_array($dados, $patrimonio->todosCodigosInventario(), true)) {
            return $dados;
        }

        return $patrimonio->codigo;
    }

    private function montarResposta(Patrimonio $patrimonio, string $codigoExibicao): array
    {
        $multiplasUnidades = $patrimonio->unidades() > 1;
        $dadosUnidade = $patrimonio->dadosUnidadeParaCodigo($codigoExibicao);

        return [
            'id' => $patrimonio->id,
            'codigo' => $codigoExibicao,
            'nome' => $patrimonio->nome,
            'descricao' => $dadosUnidade['descricao'],
            'imagem' => $dadosUnidade['imagem'],
            'categoria' => $patrimonio->categoria?->nome,
            'localizacao' => $patrimonio->localizacao,
            'responsavel' => $patrimonio->responsavel,
            'valor_atual' => $patrimonio->valor_atual,
            'url' => $patrimonio->urlParaCodigo($codigoExibicao),
            'multiplas_unidades' => $multiplasUnidades,
            'grupo' => [
                'codigo' => $patrimonio->codigo,
                'nome' => $patrimonio->nome,
                'descricao' => $patrimonio->descricao,
                'imagem' => $patrimonio->imagemUrl(),
                'total_unidades' => $patrimonio->unidades(),
            ],
            'unidade' => $multiplasUnidades ? [
                ...$dadosUnidade,
                'url' => $patrimonio->urlParaCodigo($codigoExibicao),
            ] : null,
        ];
    }

    private function buscarPorCodigo(string $codigo): ?Patrimonio
    {
        $unidade = PatrimonioUnidade::query()->where('codigo', $codigo)->first();

        if ($unidade) {
            return Patrimonio::with(['categoria', 'itensInventario'])->find($unidade->patrimonio_id);
        }

        return Patrimonio::with(['categoria', 'itensInventario'])->where('codigo', $codigo)->first()
            ?? Patrimonio::with(['categoria', 'itensInventario'])->whereJsonContains('codigos_inventario', $codigo)->first();
    }
}

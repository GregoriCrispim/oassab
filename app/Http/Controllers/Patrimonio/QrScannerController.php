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
        if (json_last_error() === JSON_ERROR_NONE && isset($json['id'])) {
            $patrimonio = Patrimonio::with('categoria')->find($json['id']);
        }

        if (! $patrimonio && json_last_error() === JSON_ERROR_NONE && ! empty($json['codigo'])) {
            $codigo = (string) $json['codigo'];
            $patrimonio = $this->buscarPorCodigo($codigo);
        }

        if (! $patrimonio) {
            $patrimonio = $this->buscarPorCodigo($dados);
        }

        if (! $patrimonio && preg_match('/(?:PAT|INV)-\d+/i', $dados, $m)) {
            $codigo = strtoupper($m[0]);
            $patrimonio = $this->buscarPorCodigo($codigo);
        }

        if (! $patrimonio) {
            return response()->json(['sucesso' => false, 'mensagem' => 'Patrimônio não encontrado']);
        }

        $codigoExibicao = $patrimonio->codigo;
        if (json_last_error() === JSON_ERROR_NONE && ! empty($json['codigo'])) {
            $codigoExibicao = (string) $json['codigo'];
        } elseif (preg_match('/(?:PAT|INV)-\d+/i', $dados, $m)) {
            $codigoExibicao = strtoupper($m[0]);
        } elseif (in_array($dados, $patrimonio->todosCodigosInventario(), true)) {
            $codigoExibicao = $dados;
        }

        return response()->json([
            'sucesso' => true,
            'patrimonio' => [
                'id' => $patrimonio->id,
                'codigo' => $codigoExibicao,
                'nome' => $patrimonio->nome,
                'descricao' => $patrimonio->descricaoParaCodigo($codigoExibicao),
                'imagem' => $patrimonio->imagemParaCodigo($codigoExibicao),
                'categoria' => $patrimonio->categoria?->nome,
                'localizacao' => $patrimonio->localizacao,
                'responsavel' => $patrimonio->responsavel,
                'valor_atual' => $patrimonio->valor_atual,
                'url' => route('patrimonios.patrimonios.show', $patrimonio),
            ],
        ]);
    }

    private function buscarPorCodigo(string $codigo): ?Patrimonio
    {
        $unidade = PatrimonioUnidade::query()->where('codigo', $codigo)->first();

        if ($unidade) {
            return Patrimonio::with('categoria')->find($unidade->patrimonio_id);
        }

        return Patrimonio::with('categoria')->where('codigo', $codigo)->first()
            ?? Patrimonio::with('categoria')->whereJsonContains('codigos_inventario', $codigo)->first();
    }
}

<?php

namespace App\Services\Patrimonio;

use App\Models\Patrimonio;
use App\Models\PatrimonioUnidade;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class PatrimonioUnidadeService
{
    public function __construct(
        private readonly CodigoPatrimonioService $codigoService,
        private readonly PatrimonioFileStorage $fileStorage,
        private readonly QrCodeService $qrCodeService,
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $dadosUnidades
     */
    public function criarUnidades(Patrimonio $patrimonio, int $quantidade, array $dadosUnidades = []): void
    {
        if ($quantidade <= 1) {
            return;
        }

        $codigos = $patrimonio->todosCodigosInventario();

        foreach ($codigos as $ordem => $codigo) {
            $dados = $dadosUnidades[$ordem] ?? $dadosUnidades[(string) $ordem] ?? [];

            PatrimonioUnidade::create([
                'patrimonio_id' => $patrimonio->id,
                'codigo' => $codigo,
                'descricao' => $this->normalizarDescricao($dados['descricao'] ?? null),
                'ordem' => $ordem,
            ]);
        }

        $this->syncCodigosPatrimonio($patrimonio->fresh(['itensInventario']));
    }

    public function sincronizar(Patrimonio $patrimonio, Request $request, int $quantidadeAlvo): void
    {
        $modoImagem = $request->string('modo_imagem', 'unica')->toString();
        $dadosUnidades = $request->input('unidades', []);

        if (! is_array($dadosUnidades)) {
            $dadosUnidades = [];
        }

        $patrimonio->load('itensInventario');
        $itensAtuais = $patrimonio->itensInventario->keyBy('id');

        if ($patrimonio->unidades() <= 1 && $quantidadeAlvo <= 1) {
            return;
        }

        foreach ($dadosUnidades as $dados) {
            if (! is_array($dados)) {
                continue;
            }

            $id = isset($dados['id']) ? (int) $dados['id'] : null;
            $excluir = filter_var($dados['excluir'] ?? false, FILTER_VALIDATE_BOOL);

            if (! $id || ! $itensAtuais->has($id)) {
                continue;
            }

            /** @var PatrimonioUnidade $unidade */
            $unidade = $itensAtuais->get($id);

            if ($excluir) {
                if ($itensAtuais->count() <= 1) {
                    continue;
                }

                $this->excluirUnidade($unidade);
                $itensAtuais->forget($id);

                continue;
            }

            $descricao = $this->normalizarDescricao($dados['descricao'] ?? null);
            $updates = ['descricao' => $descricao];

            if ($request->hasFile("unidades.{$id}.imagem")) {
                $this->removerImagemUnidade($unidade);
                $updates['imagem'] = $this->fileStorage->storeUnidadeImagem(
                    $request->file("unidades.{$id}.imagem"),
                    $patrimonio,
                    $unidade->codigo,
                );
            } elseif ($modoImagem === 'unica' && filter_var($dados['remover_imagem'] ?? false, FILTER_VALIDATE_BOOL)) {
                $this->removerImagemUnidade($unidade);
                $updates['imagem'] = null;
            }

            $unidade->update($updates);
        }

        $patrimonio->refresh()->load('itensInventario');
        $quantidadeAtual = $patrimonio->itensInventario->count();

        if ($quantidadeAtual === 0) {
            return;
        }

        if ($quantidadeAlvo > $quantidadeAtual) {
            $this->adicionarUnidades($patrimonio, $quantidadeAlvo - $quantidadeAtual, $request, $modoImagem);
        }

        $this->syncCodigosPatrimonio($patrimonio->fresh(['itensInventario']));
    }

    /**
     * @param  array<int, array<string, mixed>>  $dadosUnidades
     */
    public function processarImagensCriacao(Patrimonio $patrimonio, Request $request, string $modoImagem, array $dadosUnidades = []): void
    {
        if ($patrimonio->unidades() <= 1) {
            return;
        }

        $patrimonio->load('itensInventario');

        if ($modoImagem === 'individual') {
            foreach ($patrimonio->itensInventario as $unidade) {
                $indice = $unidade->ordem;
                $dados = $dadosUnidades[$indice] ?? $dadosUnidades[(string) $indice] ?? [];
                $id = $unidade->id;

                $file = $request->file("unidades.{$indice}.imagem")
                    ?? $request->file("unidades.{$id}.imagem")
                    ?? (isset($dados['imagem']) && $dados['imagem'] instanceof UploadedFile ? $dados['imagem'] : null);

                if ($file) {
                    $unidade->update([
                        'imagem' => $this->fileStorage->storeUnidadeImagem($file, $patrimonio, $unidade->codigo),
                    ]);
                }
            }

            return;
        }

        if ($request->hasFile('imagem')) {
            return;
        }

        foreach ($patrimonio->itensInventario as $unidade) {
            $indice = $unidade->ordem;
            $file = $request->file("unidades.{$indice}.imagem")
                ?? $request->file("unidades.{$unidade->id}.imagem");

            if ($file) {
                $unidade->update([
                    'imagem' => $this->fileStorage->storeUnidadeImagem($file, $patrimonio, $unidade->codigo),
                ]);
            }
        }
    }

    public function excluirUnidade(PatrimonioUnidade $unidade): void
    {
        $this->removerImagemUnidade($unidade);
        $this->qrCodeService->deleteForCodigo($unidade->codigo);
        $this->fileStorage->deleteLegacyGroupedQrCode($unidade->patrimonio, $unidade->codigo);
        $unidade->delete();
    }

    public function syncCodigosPatrimonio(Patrimonio $patrimonio): void
    {
        $itens = $patrimonio->itensInventario()->orderBy('ordem')->get();
        $codigos = $itens->pluck('codigo')->all();

        if ($codigos === []) {
            $patrimonio->update([
                'quantidade' => 1,
                'codigos_inventario' => null,
            ]);

            return;
        }

        if (count($codigos) === 1) {
            $patrimonio->update([
                'codigo' => $codigos[0],
                'quantidade' => 1,
                'codigos_inventario' => null,
            ]);
            $itens->first()?->delete();

            return;
        }

        $patrimonio->update([
            'codigo' => $codigos[0],
            'quantidade' => count($codigos),
            'codigos_inventario' => array_values(array_slice($codigos, 1)),
        ]);
    }

    private function adicionarUnidades(Patrimonio $patrimonio, int $quantidade, Request $request, string $modoImagem): void
    {
        $ordemInicial = (int) $patrimonio->itensInventario()->max('ordem') + 1;
        $novosCodigos = $this->codigoService->gerarUnicos($quantidade);
        $dadosNovas = $request->input('unidades_novas', []);

        if (! is_array($dadosNovas)) {
            $dadosNovas = [];
        }

        foreach ($novosCodigos as $i => $codigo) {
            $ordem = $ordemInicial + $i;
            $dados = $dadosNovas[$i] ?? $dadosNovas[(string) $i] ?? [];

            $unidade = PatrimonioUnidade::create([
                'patrimonio_id' => $patrimonio->id,
                'codigo' => $codigo,
                'descricao' => $this->normalizarDescricao($dados['descricao'] ?? null),
                'ordem' => $ordem,
            ]);

            $file = $request->file("unidades_novas.{$i}.imagem");

            if ($file) {
                $unidade->update([
                    'imagem' => $this->fileStorage->storeUnidadeImagem($file, $patrimonio, $unidade->codigo),
                ]);
            } elseif ($modoImagem === 'individual' && $request->hasFile("unidades_novas.{$i}.imagem")) {
                $unidade->update([
                    'imagem' => $this->fileStorage->storeUnidadeImagem(
                        $request->file("unidades_novas.{$i}.imagem"),
                        $patrimonio,
                        $unidade->codigo,
                    ),
                ]);
            }
        }
    }

    private function removerImagemUnidade(PatrimonioUnidade $unidade): void
    {
        if ($unidade->imagem) {
            $this->fileStorage->deletePath($unidade->imagem);
        }
    }

    private function normalizarDescricao(mixed $descricao): ?string
    {
        if ($descricao === null) {
            return null;
        }

        $texto = trim((string) $descricao);

        return $texto === '' ? null : $texto;
    }
}

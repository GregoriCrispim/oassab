<?php

namespace App\Http\Controllers\Patrimonio;

use App\Http\Controllers\Concerns\RespondsWithFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patrimonio\StorePatrimonioRequest;
use App\Models\Patrimonio;
use App\Models\PatrimonioCampoValor;
use App\Models\PatrimonioCategoria;
use App\Services\Patrimonio\CodigoPatrimonioService;
use App\Services\Patrimonio\DepreciacaoService;
use App\Services\Patrimonio\PatrimonioFileStorage;
use App\Services\Patrimonio\PatrimonioLogService;
use App\Services\Patrimonio\PatrimonioUnidadeService;
use App\Services\Patrimonio\QrCodeService;
use App\Support\PaginationPerPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PatrimonioController extends Controller
{
    use RespondsWithFormModal;
    public function __construct(
        private readonly DepreciacaoService $depreciacao,
        private readonly CodigoPatrimonioService $codigoService,
        private readonly PatrimonioFileStorage $fileStorage,
        private readonly PatrimonioLogService $logService,
        private readonly PatrimonioUnidadeService $unidadeService,
        private readonly QrCodeService $qrCodeService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Patrimonio::class);

        $query = Patrimonio::query()->with('categoria');

        if ($request->filled('q')) {
            $query->busca($request->string('q')->toString());
        }

        if ($request->filled('categoria_id')) {
            $query->where('patrimonio_categoria_id', $request->integer('categoria_id'));
        }

        if ($request->has('ativo') && $request->string('ativo')->toString() !== '') {
            $query->where('ativo', $request->boolean('ativo'));
        }

        $ordenacao = $request->string('ordenacao', 'nome')->toString();
        match ($ordenacao) {
            'codigo' => $query->orderBy('codigo'),
            'valor' => $query->orderByDesc('valor_aquisicao'),
            'data' => $query->orderByDesc('data_aquisicao'),
            default => $query->orderBy('nome'),
        };

        $patrimonios = $query->paginate(PaginationPerPage::resolve($request, 10))->withQueryString();
        $categorias = PatrimonioCategoria::query()->ativas()->orderBy('nome')->get();

        if ($request->header('X-Patrimonio-List') === '1') {
            return view('patrimonios.patrimonios._table', compact('patrimonios'));
        }

        return view('patrimonios.patrimonios.index', compact('patrimonios', 'categorias'));
    }

    public function create(Request $request): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Patrimonio::class);

        $patrimonio = new Patrimonio([
            'data_aquisicao' => now()->toDateString(),
            'indice_depreciacao' => 10,
            'ativo' => true,
        ]);

        $categorias = PatrimonioCategoria::query()->ativas()->with('camposAtivos')->orderBy('nome')->get();

        $data = [
            'patrimonio' => $patrimonio,
            'categorias' => $categorias,
            'campoValores' => [],
            'unidadesInventario' => [],
            'modoImagem' => 'unica',
        ];

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Novo Patrimônio', 'patrimonios.patrimonios._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.patrimonios.index'),
            route('patrimonios.patrimonios.create'),
        );
    }

    public function store(StorePatrimonioRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Patrimonio::class);

        $data = $request->validated();
        $quantidade = min(999, max(1, (int) ($data['quantidade'] ?? 1)));
        $camposCustomizados = $data['campos_customizados'] ?? [];
        unset($data['quantidade'], $data['campos_customizados']);

        if (! empty($data['patrimonio_categoria_id'])) {
            $cat = PatrimonioCategoria::find($data['patrimonio_categoria_id']);
            if ($cat && empty($data['indice_depreciacao'])) {
                $data['indice_depreciacao'] = $cat->indice_depreciacao_padrao;
            }
        }

        $dep = $this->depreciacao->calcular(
            (float) $data['valor_aquisicao'],
            (float) $data['indice_depreciacao'],
            $data['data_aquisicao'],
        );

        $codigos = $this->codigoService->gerarUnicos($quantidade);
        $codigoPrincipal = array_shift($codigos);

        $data['codigo'] = $codigoPrincipal;
        $data['quantidade'] = $quantidade;
        $data['codigos_inventario'] = $codigos ?: null;
        $data['valor_depreciado'] = $dep['valor_depreciado'];
        $data['valor_atual'] = $dep['valor_atual'];
        $data['created_by'] = auth()->id();
        $data['ativo'] = $request->boolean('ativo');

        DB::transaction(function () use ($data, $camposCustomizados, $request, $quantidade) {
            $patrimonio = Patrimonio::create($data);

            if ($quantidade > 1) {
                $this->unidadeService->criarUnidades(
                    $patrimonio,
                    $quantidade,
                    $request->input('unidades', []),
                );
            }

            $this->syncCamposCustomizados($patrimonio, $camposCustomizados);
            $this->handleUploads($request, $patrimonio);

            if ($quantidade > 1) {
                $modoImagem = $request->string('modo_imagem', 'unica')->toString();
                $this->unidadeService->processarImagensCriacao(
                    $patrimonio,
                    $request,
                    $modoImagem,
                    $request->input('unidades', []),
                );
            }

            $this->logService->logModel(
                'INSERT',
                $patrimonio,
                "Patrimônio criado: {$patrimonio->nome} ({$patrimonio->codigoResumo()}, qtd. {$patrimonio->fresh()->unidades()})",
            );

            $this->qrCodeService->syncForPatrimonio($patrimonio->fresh());
        });

        $msg = $quantidade > 1
            ? "Patrimônio cadastrado com {$quantidade} unidades (código inicial {$codigoPrincipal})."
            : 'Patrimônio cadastrado com sucesso.';

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(route('patrimonios.patrimonios.index'), $msg);
        }

        return redirect()->route('patrimonios.patrimonios.index')->with('status', $msg);
    }

    public function show(Request $request, Patrimonio $patrimonio): View
    {
        $this->authorize('view', $patrimonio);

        $patrimonio->load(['categoria.camposAtivos', 'campoValores.campo', 'arquivos', 'manutencoes' => fn ($q) => $q->latest()->limit(5), 'itensInventario']);

        $codigoUnidade = $request->string('unidade')->toString();
        $unidadeAtiva = null;
        $dadosUnidadeAtiva = null;

        if ($codigoUnidade !== '' && in_array($codigoUnidade, $patrimonio->todosCodigosInventario(), true)) {
            $unidadeAtiva = $patrimonio->unidadePorCodigo($codigoUnidade);
            $dadosUnidadeAtiva = $patrimonio->dadosUnidadeParaCodigo($codigoUnidade);
        }

        return view('patrimonios.patrimonios.show', compact('patrimonio', 'unidadeAtiva', 'dadosUnidadeAtiva', 'codigoUnidade'));
    }

    public function edit(Request $request, Patrimonio $patrimonio): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $patrimonio);

        $patrimonio->load(['categoria.camposAtivos', 'campoValores', 'arquivos', 'itensInventario']);
        $categorias = PatrimonioCategoria::query()->ativas()->with('camposAtivos')->orderBy('nome')->get();
        $campoValores = $patrimonio->campoValores->pluck('valor', 'patrimonio_categoria_campo_id')->toArray();

        $data = [
            'patrimonio' => $patrimonio,
            'categorias' => $categorias,
            'campoValores' => $campoValores,
            'unidadesInventario' => $patrimonio->itensInventario->map(fn ($u) => [
                'id' => $u->id,
                'codigo' => $u->codigo,
                'descricao' => $u->descricao,
                'imagem' => $u->imagemUrl(),
                'ordem' => $u->ordem,
            ])->values()->all(),
            'modoImagem' => $patrimonio->usaImagensIndividuais() ? 'individual' : 'unica',
        ];

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Editar Patrimônio', 'patrimonios.patrimonios._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.patrimonios.index'),
            route('patrimonios.patrimonios.edit', $patrimonio),
        );
    }

    public function update(StorePatrimonioRequest $request, Patrimonio $patrimonio): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $patrimonio);

        $data = $request->validated();
        $camposCustomizados = $data['campos_customizados'] ?? [];
        unset($data['campos_customizados'], $data['codigo']);
        $quantidadeAlvo = min(999, max(1, (int) ($data['quantidade'] ?? $patrimonio->unidades())));
        unset($data['quantidade']);

        $dep = $this->depreciacao->calcular(
            (float) $data['valor_aquisicao'],
            (float) $data['indice_depreciacao'],
            $data['data_aquisicao'],
        );

        $data['valor_depreciado'] = $dep['valor_depreciado'];
        $data['valor_atual'] = $dep['valor_atual'];
        $data['ativo'] = $request->boolean('ativo');

        DB::transaction(function () use ($patrimonio, $data, $camposCustomizados, $request, $quantidadeAlvo) {
            $patrimonio->update($data);
            $this->syncCamposCustomizados($patrimonio, $camposCustomizados);
            $this->handleUploads($request, $patrimonio);

            if ($patrimonio->itensInventario()->exists() || $quantidadeAlvo > 1) {
                $this->unidadeService->sincronizar($patrimonio, $request, $quantidadeAlvo);
            } elseif ($quantidadeAlvo > 1) {
                $codigos = $this->codigoService->gerarUnicos($quantidadeAlvo);
                $codigoPrincipal = array_shift($codigos);
                $patrimonio->update([
                    'codigo' => $codigoPrincipal,
                    'quantidade' => $quantidadeAlvo,
                    'codigos_inventario' => $codigos ?: null,
                ]);
                $this->unidadeService->criarUnidades($patrimonio->fresh(), $quantidadeAlvo);
            }

            $patrimonio->refresh();
            $this->logService->logModel('UPDATE', $patrimonio, "Patrimônio atualizado: {$patrimonio->nome} (qtd. {$patrimonio->unidades()})");
            $this->qrCodeService->syncForPatrimonio($patrimonio);
        });

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(route('patrimonios.patrimonios.index'), 'Patrimônio atualizado com sucesso.');
        }

        return redirect()->route('patrimonios.patrimonios.index')->with('status', 'Patrimônio atualizado com sucesso.');
    }

    public function destroy(Patrimonio $patrimonio): RedirectResponse
    {
        $this->authorize('delete', $patrimonio);

        $nome = $patrimonio->nome;
        $this->fileStorage->purgeAll($patrimonio);
        $patrimonio->delete();
        $this->logService->registrar('DELETE', 'patrimonios', null, "Patrimônio excluído: {$nome}");

        return redirect()->route('patrimonios.patrimonios.index')->with('status', 'Patrimônio excluído.');
    }

    public function qrcodesData(Patrimonio $patrimonio): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $patrimonio);

        $this->qrCodeService->syncForPatrimonio($patrimonio);

        return response()->json([
            'nome' => $patrimonio->nome,
            'unidades' => $patrimonio->unidades(),
            'codigos' => $patrimonio->todosCodigosInventario(),
            'qrcodes' => $this->qrCodeService->pathsForPatrimonio($patrimonio),
            'qrcode_base' => route('patrimonios.patrimonios.qrcode', $patrimonio),
            'regenerar_url' => route('patrimonios.patrimonios.qrcodes.regenerar', $patrimonio),
            'pode_regenerar' => \Illuminate\Support\Facades\Gate::allows('update', $patrimonio),
        ]);
    }

    public function regenerarQrcodes(Patrimonio $patrimonio, Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $patrimonio);

        $todos = $patrimonio->todosCodigosInventario();
        $solicitados = $request->input('codigos', []);

        if (! is_array($solicitados) || $solicitados === []) {
            $codigos = $todos;
        } else {
            $solicitados = array_map('strval', $solicitados);
            $codigos = array_values(array_filter($todos, fn (string $c) => in_array($c, $solicitados, true)));
        }

        $resultado = $this->qrCodeService->regenerate($patrimonio, $codigos);

        $version = now()->timestamp;
        $urls = [];

        foreach ($codigos as $codigo) {
            $urls[$codigo] = $this->qrCodeService->dynamicUrl($patrimonio, $codigo).'&v='.$version;
        }

        return response()->json([
            'sucesso' => true,
            'regerados' => $resultado['regerados'],
            'falhas' => $resultado['falhas'],
            'qrcodes' => $urls,
            'version' => $version,
        ]);
    }

    public function qrcode(Patrimonio $patrimonio, Request $request): Response
    {
        $this->authorize('view', $patrimonio);

        $codigo = $request->string('codigo')->toString() ?: $patrimonio->codigo;

        if (! in_array($codigo, $patrimonio->todosCodigosInventario(), true)) {
            abort(404);
        }

        $size = min(400, max(100, $request->integer('size', QrCodeService::DEFAULT_SIZE)));

        if ($size === QrCodeService::DEFAULT_SIZE) {
            $content = $this->qrCodeService->storedContent($codigo);

            if ($content === null && $this->qrCodeService->storeSafely($patrimonio, $codigo)) {
                $content = $this->qrCodeService->storedContent($codigo);
            }

            if ($content !== null) {
                return response($content, 200, [
                    'Content-Type' => 'image/svg+xml',
                    'Content-Disposition' => 'inline; filename="qrcode-'.$codigo.'.svg"',
                ]);
            }
        }

        try {
            $image = $this->qrCodeService->generate($patrimonio, $codigo, $size);
            $svg = $image['content'];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Falha ao gerar QR code.', [
                'patrimonio_id' => $patrimonio->id,
                'codigo' => $codigo,
                'erro' => $e->getMessage(),
            ]);
            $svg = $this->qrCodePlaceholderSvg($codigo, $size);
        }

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qrcode-'.$codigo.'.svg"',
        ]);
    }

    private function qrCodePlaceholderSvg(string $codigo, int $size): string
    {
        $codigo = htmlspecialchars($codigo, ENT_QUOTES);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
            <rect width="100%" height="100%" fill="#ffffff" stroke="#e2b4b4"/>
            <text x="50%" y="46%" font-family="monospace" font-size="14" font-weight="bold" text-anchor="middle" fill="#1f2754">{$codigo}</text>
            <text x="50%" y="58%" font-family="sans-serif" font-size="10" text-anchor="middle" fill="#c0392b">QR indisponível</text>
        </svg>
        SVG;
    }

    private function syncCamposCustomizados(Patrimonio $patrimonio, array $campos): void
    {
        PatrimonioCampoValor::query()->where('patrimonio_id', $patrimonio->id)->delete();

        foreach ($campos as $campoId => $valor) {
            if ($valor === null || $valor === '') {
                continue;
            }

            PatrimonioCampoValor::create([
                'patrimonio_id' => $patrimonio->id,
                'patrimonio_categoria_campo_id' => $campoId,
                'valor' => $valor,
            ]);
        }
    }

    private function handleUploads(Request $request, Patrimonio $patrimonio): void
    {
        if ($request->hasFile('imagem')) {
            if ($patrimonio->imagem) {
                $this->fileStorage->deletePath($patrimonio->imagem);
            }

            $patrimonio->arquivos()->where('categoria_arquivo', 'imagem')->each(function ($arquivo) {
                $this->fileStorage->deleteAttachment($arquivo);
                $arquivo->delete();
            });

            $meta = $this->fileStorage->store($request->file('imagem'), $patrimonio, 'imagem');
            $patrimonio->update(['imagem' => $meta['nome_arquivo']]);
            $patrimonio->arquivos()->create($meta);
        }

        if ($request->hasFile('arquivos')) {
            foreach ($request->file('arquivos') as $file) {
                if (! $file) {
                    continue;
                }

                $patrimonio->arquivos()->create(
                    $this->fileStorage->store($file, $patrimonio, 'documento'),
                );
            }
        }
    }
}

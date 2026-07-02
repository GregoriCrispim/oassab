<?php

namespace App\Http\Controllers\Patrimonio;

use App\Http\Controllers\Concerns\RespondsWithFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patrimonio\StoreCategoriaRequest;
use App\Models\PatrimonioCategoria;
use App\Models\PatrimonioCategoriaCampo;
use App\Services\Patrimonio\PatrimonioLogService;
use App\Support\PaginationPerPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    use RespondsWithFormModal;
    public function __construct(private readonly PatrimonioLogService $logService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PatrimonioCategoria::class);

        $categorias = PatrimonioCategoria::query()
            ->withCount('patrimonios')
            ->orderBy('nome')
            ->paginate(PaginationPerPage::resolve($request, 10))
            ->withQueryString();

        return view('patrimonios.categorias.index', compact('categorias'));
    }

    public function create(Request $request): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', PatrimonioCategoria::class);

        $categoria = new PatrimonioCategoria([
            'indice_depreciacao_padrao' => 10,
            'icone' => 'bi-tag',
            'cor' => '#6366f1',
            'ativo' => true,
        ]);

        $data = [
            'categoria' => $categoria,
            'campos' => [],
        ];

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Nova Categoria', 'patrimonios.categorias._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.categorias.index'),
            route('patrimonios.categorias.create'),
        );
    }

    public function store(StoreCategoriaRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', PatrimonioCategoria::class);

        DB::transaction(function () use ($request) {
            $categoria = PatrimonioCategoria::create($request->categoriaData());
            $this->syncCampos($categoria, $request->input('campos', []));
            $this->logService->logModel('INSERT', $categoria, "Categoria criada: {$categoria->nome}");
        });

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(route('patrimonios.categorias.index'), 'Categoria criada com sucesso.');
        }

        return redirect()->route('patrimonios.categorias.index')->with('status', 'Categoria criada com sucesso.');
    }

    public function edit(Request $request, PatrimonioCategoria $categoria): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $categoria);

        $categoria->load('campos');

        $data = [
            'categoria' => $categoria,
            'campos' => $categoria->campos,
        ];

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Editar Categoria', 'patrimonios.categorias._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.categorias.index'),
            route('patrimonios.categorias.edit', $categoria),
        );
    }

    public function update(StoreCategoriaRequest $request, PatrimonioCategoria $categoria): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $categoria);

        DB::transaction(function () use ($request, $categoria) {
            $categoria->update($request->categoriaData());
            $this->syncCampos($categoria, $request->input('campos', []));
            $this->logService->logModel('UPDATE', $categoria, "Categoria atualizada: {$categoria->nome}");
        });

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(route('patrimonios.categorias.index'), 'Categoria atualizada com sucesso.');
        }

        return redirect()->route('patrimonios.categorias.index')->with('status', 'Categoria atualizada com sucesso.');
    }

    public function destroy(PatrimonioCategoria $categoria): RedirectResponse
    {
        $this->authorize('delete', $categoria);

        if ($categoria->patrimonios()->exists()) {
            return back()->withErrors(['categoria' => 'Não é possível excluir categoria com patrimônios vinculados.']);
        }

        $nome = $categoria->nome;
        $categoria->delete();
        $this->logService->registrar('DELETE', 'patrimonio_categorias', null, "Categoria excluída: {$nome}");

        return redirect()->route('patrimonios.categorias.index')->with('status', 'Categoria excluída.');
    }

    private function syncCampos(PatrimonioCategoria $categoria, array $campos): void
    {
        $idsMantidos = [];

        foreach ($campos as $ordem => $campo) {
            if (empty($campo['nome_campo']) || empty($campo['label'])) {
                continue;
            }

            $data = [
                'nome_campo' => $campo['nome_campo'],
                'label' => $campo['label'],
                'tipo_campo' => $campo['tipo_campo'] ?? 'texto',
                'opcoes_select' => $campo['opcoes_select'] ?? null,
                'obrigatorio' => ! empty($campo['obrigatorio']),
                'ordem' => $ordem,
                'ativo' => true,
            ];

            if (! empty($campo['id'])) {
                $model = PatrimonioCategoriaCampo::where('patrimonio_categoria_id', $categoria->id)
                    ->where('id', $campo['id'])
                    ->first();
                if ($model) {
                    $model->update($data);
                    $idsMantidos[] = $model->id;

                    continue;
                }
            }

            $novo = $categoria->campos()->create($data);
            $idsMantidos[] = $novo->id;
        }

        $categoria->campos()->whereNotIn('id', $idsMantidos)->delete();
    }
}

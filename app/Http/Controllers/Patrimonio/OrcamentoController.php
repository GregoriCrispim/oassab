<?php

namespace App\Http\Controllers\Patrimonio;

use App\Http\Controllers\Concerns\RespondsWithFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patrimonio\StoreOrcamentoRequest;
use App\Http\Requests\Patrimonio\StorePropostaRequest;
use App\Models\Orcamento;
use App\Models\OrcamentoProposta;
use App\Models\PatrimonioCategoria;
use App\Services\Patrimonio\PatrimonioLogService;
use App\Support\PaginationPerPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrcamentoController extends Controller
{
    use RespondsWithFormModal;
    public function __construct(private readonly PatrimonioLogService $logService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Orcamento::class);

        $query = Orcamento::query()->with(['categoria', 'propostas']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $orcamentos = $query->orderByDesc('created_at')->paginate(PaginationPerPage::resolve($request, 10))->withQueryString();

        return view('patrimonios.orcamentos.index', compact('orcamentos'));
    }

    public function create(Request $request): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Orcamento::class);

        $orcamento = new Orcamento([
            'quantidade' => 1,
            'prioridade' => 'media',
            'status' => 'aberto',
            'usuario_solicitante' => auth()->user()->name,
        ]);

        $categorias = PatrimonioCategoria::query()->ativas()->orderBy('nome')->get();

        $data = [
            'orcamento' => $orcamento,
            'categorias' => $categorias,
            'propostas' => collect(),
        ];

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Novo Orçamento', 'patrimonios.orcamentos._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.orcamentos.index'),
            route('patrimonios.orcamentos.create'),
        );
    }

    public function store(StoreOrcamentoRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Orcamento::class);

        $orcamento = Orcamento::create(array_merge($request->validated(), [
            'created_by' => auth()->id(),
        ]));

        $this->logService->logModel('INSERT', $orcamento, "Orçamento criado: {$orcamento->nome_item}");

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(
                route('patrimonios.orcamentos.index', ['modal' => route('patrimonios.orcamentos.edit', $orcamento)]),
                'Orçamento criado com sucesso.',
            );
        }

        return redirect()->route('patrimonios.orcamentos.edit', $orcamento)->with('status', 'Orçamento criado com sucesso.');
    }

    public function edit(Request $request, Orcamento $orcamento): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $orcamento);

        $orcamento->load('propostas');
        $categorias = PatrimonioCategoria::query()->ativas()->orderBy('nome')->get();

        $data = [
            'orcamento' => $orcamento,
            'categorias' => $categorias,
            'propostas' => $orcamento->propostas,
        ];

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Gerenciar Orçamento', 'patrimonios.orcamentos._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.orcamentos.index'),
            route('patrimonios.orcamentos.edit', $orcamento),
        );
    }

    public function update(StoreOrcamentoRequest $request, Orcamento $orcamento): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $orcamento);

        $orcamento->update($request->validated());
        $this->logService->logModel('UPDATE', $orcamento, "Orçamento atualizado: {$orcamento->nome_item}");

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(route('patrimonios.orcamentos.index'), 'Orçamento atualizado com sucesso.');
        }

        return redirect()->route('patrimonios.orcamentos.index')->with('status', 'Orçamento atualizado com sucesso.');
    }

    public function destroy(Orcamento $orcamento): RedirectResponse
    {
        $this->authorize('delete', $orcamento);

        $nome = $orcamento->nome_item;
        $orcamento->delete();
        $this->logService->registrar('DELETE', 'orcamentos', null, "Orçamento excluído: {$nome}");

        return redirect()->route('patrimonios.orcamentos.index')->with('status', 'Orçamento excluído.');
    }

    public function storeProposta(StorePropostaRequest $request, Orcamento $orcamento): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $orcamento);

        DB::transaction(function () use ($request, $orcamento) {
            if ($request->boolean('selecionada')) {
                $orcamento->propostas()->update(['selecionada' => false]);
            }

            $data = $request->validated();
            $data['valor_total'] = ($data['valor_unitario'] * $data['quantidade']) + ($data['custo_frete'] ?? 0) + ($data['custo_instalacao'] ?? 0);

            if (! empty($data['proposta_id'])) {
                $proposta = OrcamentoProposta::where('orcamento_id', $orcamento->id)->findOrFail($data['proposta_id']);
                unset($data['proposta_id']);
                $proposta->update($data);
            } else {
                unset($data['proposta_id']);
                $orcamento->propostas()->create($data);
            }
        });

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(
                route('patrimonios.orcamentos.index', ['modal' => route('patrimonios.orcamentos.edit', $orcamento)]),
                'Proposta salva com sucesso.',
            );
        }

        return back()->with('status', 'Proposta salva com sucesso.');
    }

    public function destroyProposta(Orcamento $orcamento, OrcamentoProposta $proposta): RedirectResponse
    {
        $this->authorize('update', $orcamento);

        abort_unless($proposta->orcamento_id === $orcamento->id, 404);
        $proposta->delete();

        return back()->with('status', 'Proposta excluída.');
    }
}

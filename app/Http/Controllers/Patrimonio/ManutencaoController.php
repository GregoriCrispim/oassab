<?php

namespace App\Http\Controllers\Patrimonio;

use App\Http\Controllers\Concerns\RespondsWithFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patrimonio\StoreManutencaoRequest;
use App\Models\Manutencao;
use App\Models\Patrimonio;
use App\Services\Patrimonio\PatrimonioLogService;
use App\Support\PaginationPerPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManutencaoController extends Controller
{
    use RespondsWithFormModal;
    public function __construct(private readonly PatrimonioLogService $logService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Manutencao::class);

        $query = Manutencao::query()->with('patrimonio');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo'));
        }

        $manutencoes = $query->orderByDesc('data_manutencao')->paginate(PaginationPerPage::resolve($request, 10))->withQueryString();

        return view('patrimonios.manutencoes.index', compact('manutencoes'));
    }

    public function create(Request $request): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Manutencao::class);

        $manutencao = new Manutencao([
            'data_manutencao' => now()->toDateString(),
            'tipo' => 'corretiva',
            'status' => 'concluida',
            'patrimonio_id' => $request->integer('patrimonio_id') ?: null,
        ]);

        $patrimonios = Patrimonio::query()->ativos()->orderBy('nome')->get(['id', 'codigo', 'nome']);
        $data = compact('manutencao', 'patrimonios');

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Nova Manutenção', 'patrimonios.manutencoes._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.manutencoes.index'),
            route('patrimonios.manutencoes.create', $request->only('patrimonio_id')),
        );
    }

    public function store(StoreManutencaoRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Manutencao::class);

        $manutencao = Manutencao::create(array_merge($request->validated(), [
            'created_by' => auth()->id(),
        ]));

        $this->logService->logModel('INSERT', $manutencao, "Manutenção registrada para patrimônio #{$manutencao->patrimonio_id}");

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(route('patrimonios.manutencoes.index'), 'Manutenção registrada com sucesso.');
        }

        return redirect()->route('patrimonios.manutencoes.index')->with('status', 'Manutenção registrada com sucesso.');
    }

    public function edit(Request $request, Manutencao $manutencao): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $manutencao);

        $patrimonios = Patrimonio::query()->ativos()->orderBy('nome')->get(['id', 'codigo', 'nome']);
        $data = compact('manutencao', 'patrimonios');

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Editar Manutenção', 'patrimonios.manutencoes._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.manutencoes.index'),
            route('patrimonios.manutencoes.edit', $manutencao),
        );
    }

    public function update(StoreManutencaoRequest $request, Manutencao $manutencao): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $manutencao);

        $manutencao->update($request->validated());
        $this->logService->logModel('UPDATE', $manutencao, "Manutenção atualizada #{$manutencao->id}");

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(route('patrimonios.manutencoes.index'), 'Manutenção atualizada com sucesso.');
        }

        return redirect()->route('patrimonios.manutencoes.index')->with('status', 'Manutenção atualizada com sucesso.');
    }

    public function destroy(Manutencao $manutencao): RedirectResponse
    {
        $this->authorize('delete', $manutencao);

        $id = $manutencao->id;
        $manutencao->delete();
        $this->logService->registrar('DELETE', 'manutencoes', $id, "Manutenção excluída #{$id}");

        return redirect()->route('patrimonios.manutencoes.index')->with('status', 'Manutenção excluída.');
    }
}

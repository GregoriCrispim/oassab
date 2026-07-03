<?php

namespace App\Http\Controllers\Patrimonio;

use App\Http\Controllers\Controller;
use App\Models\PatrimonioLog;
use App\Services\Patrimonio\PatrimonioLogService;
use App\Support\PaginationPerPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->isPatrimonioAdmin(), 403);

        $query = PatrimonioLog::query()->with('user')->orderByDesc('created_at');

        if ($request->filled('acao')) {
            $query->where('acao', $request->string('acao'));
        }

        $logs = $query->paginate(PaginationPerPage::resolve($request, 10))->withQueryString();

        return view('patrimonios.logs.index', compact('logs'));
    }

    public function clear(PatrimonioLogService $logService): RedirectResponse
    {
        abort_unless(auth()->user()->isPatrimonioAdmin(), 403);

        PatrimonioLog::query()->delete();
        $logService->registrar('DELETE', 'patrimonio_logs', null, 'Logs de auditoria limpos');

        return back()->with('status', 'Logs limpos com sucesso.');
    }
}

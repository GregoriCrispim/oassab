<?php

namespace App\Http\Controllers\Patrimonio;

use App\Http\Controllers\Controller;
use App\Services\Patrimonio\DashboardStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardStatsService $stats)
    {
    }

    public function index(): View
    {
        $stats = $this->stats->stats();
        $chart = $this->stats->depreciacaoChart();

        return view('patrimonios.dashboard', compact('stats', 'chart'));
    }
}

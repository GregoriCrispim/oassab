@extends('patrimonios.layouts.app')

@section('title', 'Dashboard')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
@endpush

@section('content')
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-oassab-border bg-white p-5 shadow-sm">
            <p class="text-sm text-oassab-gray">Total de Patrimônios</p>
            <p class="mt-2 text-3xl font-bold text-oassab-blue">{{ $stats['total_patrimonios'] }}</p>
        </div>
        <div class="rounded-xl border border-oassab-border bg-white p-5 shadow-sm">
            <p class="text-sm text-oassab-gray">Valor de Aquisição</p>
            <p class="mt-2 text-2xl font-bold text-oassab-blue">R$ {{ number_format($stats['valor_aquisicao'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-oassab-border bg-white p-5 shadow-sm">
            <p class="text-sm text-oassab-gray">Valor Atual</p>
            <p class="mt-2 text-2xl font-bold text-green-600">R$ {{ number_format($stats['valor_total'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-oassab-border bg-white p-5 shadow-sm">
            <p class="text-sm text-oassab-gray">Depreciação</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['percentual_depreciacao'] }}%</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-oassab-border bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-heading text-lg font-bold text-oassab-blue">Depreciação por Categoria</h2>
            <canvas id="chartDepreciacao" height="200"></canvas>
        </div>
        <div class="rounded-xl border border-oassab-border bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-heading text-lg font-bold text-oassab-blue">Patrimônios por Categoria</h2>
            <div class="space-y-3">
                @forelse ($stats['por_categoria'] as $cat)
                    <div class="flex items-center justify-between rounded-lg bg-oassab-cream px-4 py-2">
                        <span class="font-medium text-oassab-blue">{{ $cat['nome'] }}</span>
                        <span class="text-sm text-oassab-gray">{{ $cat['quantidade'] }} itens — R$ {{ number_format($cat['valor_total'], 2, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-oassab-gray">Nenhuma categoria com patrimônios.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-oassab-border bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-heading text-lg font-bold text-oassab-blue">Mais Valiosos</h2>
            <div class="divide-y divide-oassab-border">
                @foreach ($stats['mais_valiosos'] as $item)
                    <a href="{{ route('patrimonios.patrimonios.show', $item['id']) }}" class="flex items-center justify-between py-3 hover:bg-oassab-cream">
                        <div>
                            <p class="font-medium text-oassab-blue">{{ $item['nome'] }}</p>
                            <p class="text-xs text-oassab-gray">{{ $item['codigo'] }}</p>
                        </div>
                        <span class="font-semibold text-green-600">R$ {{ number_format($item['valor_atual'], 2, ',', '.') }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="rounded-xl border border-oassab-border bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-heading text-lg font-bold text-oassab-blue">Próximas Manutenções</h2>
            <div class="divide-y divide-oassab-border">
                @forelse ($stats['proximas_manutencoes'] as $man)
                    <div class="py-3">
                        <p class="font-medium text-oassab-blue">{{ $man->patrimonio?->nome }}</p>
                        <p class="text-xs text-oassab-gray">{{ $man->proxima_manutencao?->format('d/m/Y') }} — {{ $man->tipo }}</p>
                    </div>
                @empty
                    <p class="text-sm text-oassab-gray">Nenhuma manutenção agendada nos próximos 30 dias.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('chartDepreciacao');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chart['labels']),
            datasets: [
                {
                    label: 'Valor Atual',
                    data: @json($chart['valores']),
                    backgroundColor: '#0052CC',
                },
                {
                    label: 'Depreciado',
                    data: @json($chart['depreciados']),
                    backgroundColor: '#f97316',
                }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
});
</script>
@endpush

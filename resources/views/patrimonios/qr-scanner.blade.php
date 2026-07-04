@extends('patrimonios.layouts.app')

@section('title', 'QR Scanner')

@push('head')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" defer></script>
@endpush

@section('content')
    <div class="mx-auto max-w-2xl">
        <div id="reader" class="overflow-hidden rounded-xl border border-oassab-border bg-black"></div>
        <div id="resultado" class="mt-6 hidden rounded-xl border border-oassab-border bg-white p-6 shadow-sm"></div>
        <div id="erro" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"></div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const token = document.querySelector('meta[name="csrf-token"]').content;

    const formatMoney = (value) => Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2 });

    const renderResultado = (p) => {
        const unidadeHtml = p.multiplas_unidades && p.unidade ? `
            <section class="rounded-xl border-2 border-oassab-orange bg-oassab-orange/5 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-oassab-orange">Unidade identificada</p>
                <p class="mt-1 font-mono text-lg font-bold text-oassab-blue">${p.unidade.codigo}</p>
                ${p.unidade.imagem ? `
                    <img src="${p.unidade.imagem}" alt="${p.unidade.codigo}" class="mx-auto mt-3 h-36 w-36 rounded-xl border border-oassab-border bg-white object-contain p-2">
                ` : ''}
                ${p.unidade.descricao ? `
                    <p class="mt-3 text-sm text-oassab-gray">${p.unidade.descricao}</p>
                ` : `
                    <p class="mt-3 text-sm italic text-oassab-gray">Sem descrição específica para esta unidade.</p>
                `}
            </section>
        ` : '';

        const grupoHtml = p.multiplas_unidades ? `
            <section class="mt-4 rounded-xl border border-oassab-border bg-oassab-cream/30 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-oassab-gray">Conjunto</p>
                <h3 class="font-heading text-lg font-bold text-oassab-blue">${p.grupo.nome}</h3>
                <p class="text-sm text-oassab-gray">${p.grupo.total_unidades} unidades · ref. ${p.grupo.codigo}</p>
                ${p.grupo.descricao ? `<p class="mt-2 text-sm text-oassab-gray">${p.grupo.descricao}</p>` : ''}
            </section>
        ` : `
            <h3 class="font-heading text-lg font-bold text-oassab-blue">${p.nome}</h3>
            <p class="text-sm text-oassab-gray">${p.codigo}</p>
            ${p.descricao ? `<p class="mt-2 text-sm text-oassab-gray">${p.descricao}</p>` : ''}
            ${p.imagem ? `<img src="${p.imagem}" alt="${p.nome}" class="mx-auto mt-4 h-36 w-36 rounded-xl border border-oassab-border bg-white object-contain p-2">` : ''}
        `;

        document.getElementById('resultado').innerHTML = `
            ${unidadeHtml}
            ${grupoHtml}
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-oassab-gray">Categoria</dt><dd>${p.categoria || '—'}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-oassab-gray">Localização</dt><dd>${p.localizacao || '—'}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-oassab-gray">Valor unitário</dt><dd>R$ ${formatMoney(p.valor_atual)}</dd></div>
            </dl>
            <a href="${p.url}" class="btn-orange mt-4 inline-flex">Ver detalhes completos</a>
        `;
    };

    function onScan(decodedText) {
        fetch('{{ route('patrimonios.qr-scanner.buscar') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify({ dados: decodedText })
        })
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                document.getElementById('resultado').classList.remove('hidden');
                renderResultado(data.patrimonio);
                document.getElementById('erro').classList.add('hidden');
            } else {
                document.getElementById('erro').classList.remove('hidden');
                document.getElementById('erro').textContent = data.mensagem || 'Patrimônio não encontrado';
            }
        })
        .catch(() => {
            document.getElementById('erro').classList.remove('hidden');
            document.getElementById('erro').textContent = 'Erro ao buscar patrimônio. Verifique sua conexão.';
        });
    }

    if (typeof Html5Qrcode !== 'undefined') {
        const scanner = new Html5Qrcode('reader');
        const qrboxSize = Math.min(280, Math.floor(window.innerWidth * 0.75));
        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: qrboxSize, height: qrboxSize } },
            onScan
        ).catch(() => {
            document.getElementById('erro').classList.remove('hidden');
            document.getElementById('erro').textContent = 'Não foi possível acessar a câmera.';
        });
    }
});
</script>
@endpush

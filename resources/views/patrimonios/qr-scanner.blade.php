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

    function onScan(decodedText) {
        fetch('{{ route('patrimonios.qr-scanner.buscar') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify({ dados: decodedText })
        })
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                const p = data.patrimonio;
                document.getElementById('resultado').classList.remove('hidden');
                document.getElementById('resultado').innerHTML = `
                    <h3 class="font-heading text-lg font-bold text-oassab-blue">${p.nome}</h3>
                    <p class="text-sm text-oassab-gray">${p.codigo}</p>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt>Categoria</dt><dd>${p.categoria || '—'}</dd></div>
                        <div class="flex justify-between"><dt>Localização</dt><dd>${p.localizacao || '—'}</dd></div>
                        <div class="flex justify-between"><dt>Valor Atual</dt><dd>R$ ${Number(p.valor_atual).toLocaleString('pt-BR', {minimumFractionDigits:2})}</dd></div>
                    </dl>
                    <a href="${p.url}" class="btn-orange mt-4">Ver detalhes</a>
                `;
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

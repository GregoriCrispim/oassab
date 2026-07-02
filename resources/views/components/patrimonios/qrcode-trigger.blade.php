@props(['patrimonio'])

<button
    type="button"
    {{ $attributes->merge(['class' => 'js-open-qrcode-modal btn-icon btn-icon--blue']) }}
    data-url="{{ route('patrimonios.patrimonios.qrcodes.data', $patrimonio) }}"
    title="QR Code{{ $patrimonio->unidades() > 1 ? 's ('.$patrimonio->unidades().' unidades)' : '' }}"
    aria-label="QR Code{{ $patrimonio->unidades() > 1 ? 's ('.$patrimonio->unidades().' unidades)' : '' }}"
>
    <i class="bi bi-qr-code"></i>
</button>

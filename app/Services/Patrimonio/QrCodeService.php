<?php

namespace App\Services\Patrimonio;

use App\Models\Patrimonio;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    public function dataForPatrimonio(Patrimonio $patrimonio, ?string $codigoInventario = null): string
    {
        $codigo = $codigoInventario ?? $patrimonio->codigo;

        return json_encode([
            'id' => $patrimonio->id,
            'codigo' => $codigo,
            'nome' => $patrimonio->nome,
            'url' => route('patrimonios.patrimonios.show', $patrimonio),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function generate(Patrimonio $patrimonio, ?string $codigoInventario = null, int $size = 300): array
    {
        $builder = new Builder(
            writer: new SvgWriter,
            data: $this->dataForPatrimonio($patrimonio, $codigoInventario),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $size,
            margin: 10,
        );

        return [
            'content' => $builder->build()->getString(),
            'mime' => 'image/svg+xml',
        ];
    }
}

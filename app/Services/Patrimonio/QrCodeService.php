<?php

namespace App\Services\Patrimonio;

use App\Models\Patrimonio;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class QrCodeService
{
    public const DEFAULT_SIZE = 300;

    public function __construct(
        private readonly PatrimonioFileStorage $fileStorage,
    ) {}

    public function dataForPatrimonio(Patrimonio $patrimonio, ?string $codigoInventario = null): string
    {
        $codigo = $codigoInventario ?? $patrimonio->codigo;

        return json_encode([
            'id' => $patrimonio->id,
            'codigo' => $codigo,
            'nome' => $patrimonio->nome,
            'url' => $patrimonio->urlParaCodigo($codigo),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function generate(Patrimonio $patrimonio, ?string $codigoInventario = null, int $size = self::DEFAULT_SIZE): array
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

    public function store(Patrimonio $patrimonio, string $codigoInventario, int $size = self::DEFAULT_SIZE): string
    {
        $image = $this->generate($patrimonio, $codigoInventario, $size);

        return $this->fileStorage->storeQrCode($image['content'], $codigoInventario);
    }

    /**
     * Grava o QR code em disco sem lançar exceção. Em hospedagem compartilhada
     * (ex.: HostGator) a escrita pode falhar por permissão; nesse caso o QR
     * continua sendo servido dinamicamente pela rota `patrimonios.patrimonios.qrcode`.
     */
    public function storeSafely(Patrimonio $patrimonio, string $codigoInventario, int $size = self::DEFAULT_SIZE): bool
    {
        try {
            $this->store($patrimonio, $codigoInventario, $size);

            return true;
        } catch (Throwable $e) {
            Log::warning('Falha ao gravar QR code em disco; usando geração dinâmica.', [
                'patrimonio_id' => $patrimonio->id,
                'codigo' => $codigoInventario,
                'erro' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function publicPath(string $codigoInventario): string
    {
        return '/storage/'.$this->fileStorage->qrCodeRelativePath($codigoInventario);
    }

    public function dynamicUrl(Patrimonio $patrimonio, string $codigoInventario): string
    {
        return route('patrimonios.patrimonios.qrcode', [$patrimonio, 'codigo' => $codigoInventario]);
    }

    /**
     * URLs para exibição dos QR codes. Sempre usa a rota de geração
     * (`patrimonios.patrimonios.qrcode`), que serve o arquivo em cache quando
     * existe ou gera o SVG em memória caso contrário. Assim a imagem nunca
     * quebra, mesmo quando o espelho public/storage está fora de sincronia.
     *
     * @return array<string, string>
     */
    public function pathsForPatrimonio(Patrimonio $patrimonio): array
    {
        $paths = [];

        foreach ($patrimonio->todosCodigosInventario() as $codigo) {
            $paths[$codigo] = $this->dynamicUrl($patrimonio, $codigo);
        }

        return $paths;
    }

    /**
     * Regera (sobrescreve) os arquivos de QR code informados. Best-effort:
     * códigos que não puderam ser gravados em disco continuam disponíveis
     * pela geração dinâmica.
     *
     * @param  array<int, string>  $codigos
     * @return array{regerados: array<int, string>, falhas: array<int, string>}
     */
    public function regenerate(Patrimonio $patrimonio, array $codigos): array
    {
        $regerados = [];
        $falhas = [];

        foreach ($codigos as $codigo) {
            if ($this->storeSafely($patrimonio, $codigo)) {
                $regerados[] = $codigo;
            } else {
                $falhas[] = $codigo;
            }
        }

        return ['regerados' => $regerados, 'falhas' => $falhas];
    }

    public function syncForPatrimonio(Patrimonio $patrimonio): void
    {
        try {
            $patrimonio->refresh();
            $codigos = $patrimonio->todosCodigosInventario();

            foreach ($codigos as $codigo) {
                $this->storeSafely($patrimonio, $codigo);
                $this->fileStorage->deleteLegacyGroupedQrCode($patrimonio, $codigo);
            }

            $this->cleanupLegacyGroupedFolder($patrimonio, $codigos);
        } catch (Throwable $e) {
            Log::warning('Falha ao sincronizar QR codes do patrimônio; seguindo com geração dinâmica.', [
                'patrimonio_id' => $patrimonio->id,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    public function syncAll(): int
    {
        $count = 0;

        Patrimonio::query()->orderBy('id')->chunkById(50, function ($patrimonios) use (&$count) {
            foreach ($patrimonios as $patrimonio) {
                $this->syncForPatrimonio($patrimonio);
                $count++;
            }
        });

        return $count;
    }

    public function storedContent(string $codigoInventario): ?string
    {
        try {
            $relative = $this->fileStorage->qrCodeRelativePath($codigoInventario);

            if (! Storage::disk('public')->exists($relative)) {
                return null;
            }

            return Storage::disk('public')->get($relative);
        } catch (Throwable) {
            return null;
        }
    }

    public function deleteForCodigo(string $codigoInventario): void
    {
        $this->fileStorage->deleteQrCodeForCodigo($codigoInventario);
    }

    /** @param  array<int, string>  $codigosAtivos */
    private function cleanupLegacyGroupedFolder(Patrimonio $patrimonio, array $codigosAtivos): void
    {
        if ($patrimonio->unidades() <= 1) {
            return;
        }

        $legacyDir = storage_path('app/public/patrimonios/'.$patrimonio->codigo.'/qrcodes');

        if (! is_dir($legacyDir)) {
            return;
        }

        foreach (glob($legacyDir.'/*.svg') ?: [] as $file) {
            $codigo = basename($file, '.svg');

            if (! in_array($codigo, $codigosAtivos, true)) {
                $this->fileStorage->deleteLegacyGroupedQrCode($patrimonio, $codigo);
            }
        }

        $remaining = glob($legacyDir.'/*.svg') ?: [];

        if ($remaining === []) {
            Storage::disk('public')->deleteDirectory('patrimonios/'.$patrimonio->codigo.'/qrcodes');

            $publicLegacyDir = public_path('storage/patrimonios/'.$patrimonio->codigo.'/qrcodes');

            if (is_dir($publicLegacyDir)) {
                File::deleteDirectory($publicLegacyDir);
            }
        }
    }
}

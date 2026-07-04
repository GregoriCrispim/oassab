<?php

namespace App\Console\Commands;

use App\Services\Patrimonio\QrCodeService;
use Illuminate\Console\Command;

class SyncPatrimonioQrCodesCommand extends Command
{
    protected $signature = 'patrimonios:sync-qrcodes';

    protected $description = 'Gera e publica QR codes de patrimônios em storage/app/public e public/storage';

    public function handle(QrCodeService $qrCodes): int
    {
        $this->info('Gerando QR codes dos patrimônios...');

        $count = $qrCodes->syncAll();

        $this->info("{$count} patrimônio(s) processado(s).");
        $this->info('QR codes disponíveis em /storage/patrimonios/{codigo}/qrcodes/{codigo}.svg');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\Patrimonio\PatrimonioCompactacaoService;
use Illuminate\Console\Command;

class CompactarPatrimoniosCommand extends Command
{
    protected $signature = 'patrimonios:compactar';

    protected $description = 'Compacta patrimônios iguais em um único registro com quantidade';

    public function handle(PatrimonioCompactacaoService $service): int
    {
        $this->info('Compactando patrimônios duplicados...');

        $resultado = $service->compactar();

        $this->info("Grupos compactados: {$resultado['grupos']}");
        $this->info("Registros removidos: {$resultado['removidos']}");
        $this->info("Total de registros: {$resultado['registros']}");

        return self::SUCCESS;
    }
}

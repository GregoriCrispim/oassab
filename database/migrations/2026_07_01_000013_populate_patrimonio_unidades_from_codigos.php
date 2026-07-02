<?php

use App\Models\Patrimonio;
use App\Models\PatrimonioUnidade;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Patrimonio::query()
            ->where('quantidade', '>', 1)
            ->orderBy('id')
            ->each(function (Patrimonio $patrimonio) {
                if ($patrimonio->itensInventario()->exists()) {
                    return;
                }

                $codigos = array_values(array_filter([
                    $patrimonio->codigo,
                    ...($patrimonio->codigos_inventario ?? []),
                ]));

                foreach ($codigos as $ordem => $codigo) {
                    PatrimonioUnidade::create([
                        'patrimonio_id' => $patrimonio->id,
                        'codigo' => $codigo,
                        'ordem' => $ordem,
                    ]);
                }
            });
    }

    public function down(): void
    {
        PatrimonioUnidade::query()->delete();
    }
};

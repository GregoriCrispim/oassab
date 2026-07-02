<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonio_campo_valores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrimonio_id')->constrained('patrimonios')->cascadeOnDelete();
            $table->foreignId('patrimonio_categoria_campo_id')->constrained('patrimonio_categoria_campos')->cascadeOnDelete();
            $table->text('valor')->nullable();
            $table->timestamps();

            $table->unique(['patrimonio_id', 'patrimonio_categoria_campo_id'], 'patrimonio_campo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonio_campo_valores');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonio_unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrimonio_id')->constrained('patrimonios')->cascadeOnDelete();
            $table->string('codigo', 50)->unique();
            $table->text('descricao')->nullable();
            $table->string('imagem')->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();

            $table->index(['patrimonio_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonio_unidades');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonio_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->text('descricao')->nullable();
            $table->decimal('indice_depreciacao_padrao', 5, 2)->default(10.00);
            $table->string('icone', 50)->default('bi-tag');
            $table->string('cor', 20)->default('#6366f1');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('nome');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonio_categorias');
    }
};

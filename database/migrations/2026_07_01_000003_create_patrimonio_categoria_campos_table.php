<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonio_categoria_campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrimonio_categoria_id')->constrained('patrimonio_categorias')->cascadeOnDelete();
            $table->string('nome_campo', 100);
            $table->string('label', 100);
            $table->enum('tipo_campo', ['texto', 'numero', 'data', 'select', 'textarea'])->default('texto');
            $table->text('opcoes_select')->nullable();
            $table->boolean('obrigatorio')->default(false);
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['patrimonio_categoria_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonio_categoria_campos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonio_arquivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrimonio_id')->constrained('patrimonios')->cascadeOnDelete();
            $table->string('nome_original');
            $table->string('nome_arquivo');
            $table->string('tipo', 50)->nullable();
            $table->unsignedInteger('tamanho')->nullable();
            $table->enum('categoria_arquivo', ['nota_fiscal', 'imagem', 'documento', 'outro'])->default('outro');
            $table->timestamp('data_upload')->useCurrent();

            $table->index('patrimonio_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonio_arquivos');
    }
};

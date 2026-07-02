<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_item');
            $table->text('descricao')->nullable();
            $table->foreignId('patrimonio_categoria_id')->nullable()->constrained('patrimonio_categorias')->nullOnDelete();
            $table->unsignedInteger('quantidade')->default(1);
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('status', ['aberto', 'em_cotacao', 'aprovado', 'cancelado', 'finalizado'])->default('aberto');
            $table->text('justificativa')->nullable();
            $table->date('data_necessidade')->nullable();
            $table->string('usuario_solicitante', 100)->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('prioridade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};

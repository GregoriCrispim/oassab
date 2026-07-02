<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamento_propostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->string('fornecedor');
            $table->string('contato_fornecedor')->nullable();
            $table->decimal('valor_unitario', 15, 2)->default(0);
            $table->unsignedInteger('quantidade')->default(1);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->decimal('custo_frete', 15, 2)->default(0);
            $table->decimal('custo_instalacao', 15, 2)->default(0);
            $table->string('prazo_entrega', 100)->nullable();
            $table->date('data_instalacao')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->string('garantia')->nullable();
            $table->date('data_validade')->nullable();
            $table->string('link_proposta', 500)->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('selecionada')->default(false);
            $table->timestamps();

            $table->index('orcamento_id');
            $table->index('selecionada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamento_propostas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrimonio_id')->constrained('patrimonios')->cascadeOnDelete();
            $table->enum('tipo', ['preventiva', 'corretiva', 'preditiva'])->default('corretiva');
            $table->text('descricao');
            $table->date('data_manutencao');
            $table->decimal('custo', 15, 2)->default(0);
            $table->string('responsavel', 100)->nullable();
            $table->string('fornecedor', 200)->nullable();
            $table->string('nota_fiscal', 100)->nullable();
            $table->enum('status', ['agendada', 'em_andamento', 'concluida', 'cancelada'])->default('concluida');
            $table->date('proxima_manutencao')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('patrimonio_id');
            $table->index('tipo');
            $table->index('status');
            $table->index('data_manutencao');
            $table->index('proxima_manutencao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manutencoes');
    }
};

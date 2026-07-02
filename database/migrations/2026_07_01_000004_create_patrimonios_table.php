<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nome', 200);
            $table->text('descricao')->nullable();
            $table->foreignId('patrimonio_categoria_id')->nullable()->constrained('patrimonio_categorias')->nullOnDelete();
            $table->decimal('valor_aquisicao', 15, 2);
            $table->decimal('indice_depreciacao', 5, 2)->default(10.00);
            $table->decimal('valor_depreciado', 15, 2)->default(0);
            $table->decimal('valor_atual', 15, 2);
            $table->date('data_aquisicao');
            $table->string('localizacao', 200)->nullable();
            $table->string('responsavel', 100)->nullable();
            $table->string('nota_fiscal', 100)->nullable();
            $table->string('imagem')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('codigo');
            $table->index('nome');
            $table->index('data_aquisicao');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonios');
    }
};

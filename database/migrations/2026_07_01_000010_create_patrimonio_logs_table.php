<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonio_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('acao', 50);
            $table->string('tabela', 50)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->text('descricao')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('acao');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonio_logs');
    }
};

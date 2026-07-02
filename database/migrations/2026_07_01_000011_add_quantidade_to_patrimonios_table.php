<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patrimonios', function (Blueprint $table) {
            $table->unsignedInteger('quantidade')->default(1)->after('codigo');
            $table->json('codigos_inventario')->nullable()->after('quantidade');
        });
    }

    public function down(): void
    {
        Schema::table('patrimonios', function (Blueprint $table) {
            $table->dropColumn(['quantidade', 'codigos_inventario']);
        });
    }
};

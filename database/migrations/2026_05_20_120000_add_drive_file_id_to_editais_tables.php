<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editais', function (Blueprint $table) {
            $table->string('drive_file_id')->nullable()->after('file_path');
        });

        Schema::table('edital_attachments', function (Blueprint $table) {
            $table->string('drive_file_id')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('edital_attachments', function (Blueprint $table) {
            $table->dropColumn('drive_file_id');
        });

        Schema::table('editais', function (Blueprint $table) {
            $table->dropColumn('drive_file_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('attachment_file_path')->nullable()->after('image_meta');
            $table->string('attachment_drive_file_id')->nullable()->after('attachment_file_path');
            $table->string('attachment_original_filename')->nullable()->after('attachment_drive_file_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'attachment_file_path',
                'attachment_drive_file_id',
                'attachment_original_filename',
            ]);
        });
    }
};

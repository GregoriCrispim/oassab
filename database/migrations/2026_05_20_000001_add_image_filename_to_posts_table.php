<?php

use App\Models\Post;
use App\Services\PostImageStorage;
use App\Services\PublicStoragePublisher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('image_filename', 255)->nullable()->after('image');
        });

        PublicStoragePublisher::publish();

        Post::query()->orderBy('id')->each(function (Post $post) {
            PostImageStorage::relocateLegacyFiles($post->slug);

            [$url, $meta] = PostImageStorage::recordFromDisk($post->slug);

            if ($url !== null) {
                $post->image = $url;
                $post->image_meta = $meta;
                $post->image_filename = $meta['filename'] ?? basename($url);
                $post->saveQuietly();

                return;
            }

            if ($post->image && str_starts_with($post->image, '/images/')) {
                $post->image_filename = basename($post->image);
                $post->image_meta = null;
                $post->saveQuietly();

                return;
            }

            if ($post->image || $post->image_meta || $post->image_filename) {
                $post->image = null;
                $post->image_meta = null;
                $post->image_filename = null;
                $post->saveQuietly();
            }
        });

        PublicStoragePublisher::publish();
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('image_filename');
        });
    }
};

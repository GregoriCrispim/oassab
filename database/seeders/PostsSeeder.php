<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PostsSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::pluck('id', 'slug');

        $dataFile = base_path('app/Data/posts.php');

        if (! file_exists($dataFile)) {
            $this->command?->warn("[PostsSeeder] Arquivo {$dataFile} não encontrado. Pulei.");

            return;
        }

        $posts = require $dataFile;

        foreach ($posts as $entry) {
            $post = Post::firstOrCreate(
                ['slug' => $entry['slug']],
                [
                    'title' => $entry['title'],
                    'date' => $entry['date'],
                    'image' => $entry['image'] ?? null,
                    'excerpt' => $entry['excerpt'] ?? null,
                    'body' => $entry['body'] ?? null,
                    'is_published' => true,
                ],
            );

            $categorySlug = $entry['category'] ?? Category::NOTICIAS;
            $categoryId = $categoryIds->get($categorySlug);

            if ($categoryId !== null) {
                $post->categories()->syncWithoutDetaching([$categoryId]);
            }
        }

        $this->command?->info('[PostsSeeder] '.count($posts).' posts importados.');
    }
}

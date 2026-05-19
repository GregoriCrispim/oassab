<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => Category::NOTICIAS, 'name' => 'Notícias'],
            ['slug' => Category::PROJETOS, 'name' => 'Projetos'],
            ['slug' => Category::TRANSPARENCIA, 'name' => 'Portal Transparência'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], ['name' => $cat['name']]);
        }
    }
}

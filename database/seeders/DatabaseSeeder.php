<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategoriesSeeder::class,
            PostsSeeder::class,
            EditalInternoOassab2026Seeder::class,
            EditalInternoOassab2Seeder::class,
            ComunicadoEditalInternoOassab2Seeder::class,
            AdminUserSeeder::class,
        ]);
    }
}

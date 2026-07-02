<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public const DEFAULT_EMAIL = 'admin@oassab.org.br';

    public const DEFAULT_PASSWORD = 'OASSAB@2026';

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => self::DEFAULT_EMAIL],
            [
                'name' => 'Administrador OASSAB',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'is_admin' => true,
                'patrimonio_role' => 'admin',
                'email_verified_at' => now(),
            ],
        );

        if (! $user->is_admin) {
            $user->is_admin = true;
            $user->save();
        }

        $this->command?->warn('[AdminUserSeeder] Usuário admin garantido: '.self::DEFAULT_EMAIL);
        $this->command?->warn('[AdminUserSeeder] Senha padrão: '.self::DEFAULT_PASSWORD.' — TROQUE após o primeiro login em /admin/profile.');
    }
}

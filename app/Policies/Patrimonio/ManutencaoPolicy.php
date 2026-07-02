<?php

namespace App\Policies\Patrimonio;

use App\Models\Manutencao;
use App\Models\User;

class ManutencaoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessPatrimonio();
    }

    public function view(User $user, Manutencao $manutencao): bool
    {
        return $user->canAccessPatrimonio();
    }

    public function create(User $user): bool
    {
        return $user->canWritePatrimonio();
    }

    public function update(User $user, Manutencao $manutencao): bool
    {
        return $user->canWritePatrimonio();
    }

    public function delete(User $user, Manutencao $manutencao): bool
    {
        return $user->canWritePatrimonio();
    }
}

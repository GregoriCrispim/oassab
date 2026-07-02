<?php

namespace App\Policies\Patrimonio;

use App\Models\Orcamento;
use App\Models\User;

class OrcamentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessPatrimonio();
    }

    public function view(User $user, Orcamento $orcamento): bool
    {
        return $user->canAccessPatrimonio();
    }

    public function create(User $user): bool
    {
        return $user->canWritePatrimonio();
    }

    public function update(User $user, Orcamento $orcamento): bool
    {
        return $user->canWritePatrimonio();
    }

    public function delete(User $user, Orcamento $orcamento): bool
    {
        return $user->canWritePatrimonio();
    }
}

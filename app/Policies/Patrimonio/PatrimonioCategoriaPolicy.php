<?php

namespace App\Policies\Patrimonio;

use App\Models\PatrimonioCategoria;
use App\Models\User;

class PatrimonioCategoriaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessPatrimonio();
    }

    public function view(User $user, PatrimonioCategoria $categoria): bool
    {
        return $user->canAccessPatrimonio();
    }

    public function create(User $user): bool
    {
        return $user->isPatrimonioAdmin();
    }

    public function update(User $user, PatrimonioCategoria $categoria): bool
    {
        return $user->isPatrimonioAdmin();
    }

    public function delete(User $user, PatrimonioCategoria $categoria): bool
    {
        return $user->isPatrimonioAdmin();
    }
}

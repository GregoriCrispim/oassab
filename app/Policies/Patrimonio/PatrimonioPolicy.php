<?php

namespace App\Policies\Patrimonio;

use App\Models\Patrimonio;
use App\Models\User;

class PatrimonioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessPatrimonio();
    }

    public function view(User $user, Patrimonio $patrimonio): bool
    {
        return $user->canAccessPatrimonio();
    }

    public function create(User $user): bool
    {
        return $user->canWritePatrimonio();
    }

    public function update(User $user, Patrimonio $patrimonio): bool
    {
        return $user->canWritePatrimonio();
    }

    public function delete(User $user, Patrimonio $patrimonio): bool
    {
        return $user->canWritePatrimonio();
    }
}

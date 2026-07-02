<?php

namespace App\Policies\Patrimonio;

use App\Models\User;

class PatrimonioUserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPatrimonioAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isPatrimonioAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isPatrimonioAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isPatrimonioAdmin() && $user->id !== $model->id;
    }
}

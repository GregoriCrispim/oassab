<?php

namespace App\Models;

use App\Enums\PatrimonioRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'patrimonio_role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'patrimonio_role' => PatrimonioRole::class,
        ];
    }

    public function canAccessPatrimonio(): bool
    {
        return $this->is_admin || $this->patrimonio_role !== null;
    }

    public function isPatrimonioAdmin(): bool
    {
        return $this->is_admin || $this->patrimonio_role === PatrimonioRole::Admin;
    }

    public function isPatrimonioGerente(): bool
    {
        return $this->isPatrimonioAdmin() || $this->patrimonio_role === PatrimonioRole::Gerente;
    }

    public function canWritePatrimonio(): bool
    {
        return $this->isPatrimonioGerente();
    }

    public function patrimonioRoleLabel(): string
    {
        if ($this->is_admin) {
            return 'Administrador CMS';
        }

        return $this->patrimonio_role?->label() ?? 'Sem acesso';
    }
}

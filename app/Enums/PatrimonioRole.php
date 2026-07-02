<?php

namespace App\Enums;

enum PatrimonioRole: string
{
    case Admin = 'admin';
    case Gerente = 'gerente';
    case Leitor = 'leitor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Gerente => 'Gerente',
            self::Leitor => 'Leitor',
        };
    }
}

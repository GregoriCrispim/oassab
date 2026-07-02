<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatrimonioCategoria extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'indice_depreciacao_padrao',
        'icone',
        'cor',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'indice_depreciacao_padrao' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    public function campos(): HasMany
    {
        return $this->hasMany(PatrimonioCategoriaCampo::class)->orderBy('ordem');
    }

    public function camposAtivos(): HasMany
    {
        return $this->campos()->where('ativo', true);
    }

    public function patrimonios(): HasMany
    {
        return $this->hasMany(Patrimonio::class);
    }

    public function orcamentos(): HasMany
    {
        return $this->hasMany(Orcamento::class);
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }
}

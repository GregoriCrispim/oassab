<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatrimonioCategoriaCampo extends Model
{
    protected $fillable = [
        'patrimonio_categoria_id',
        'nome_campo',
        'label',
        'tipo_campo',
        'opcoes_select',
        'obrigatorio',
        'ordem',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'obrigatorio' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(PatrimonioCategoria::class, 'patrimonio_categoria_id');
    }

    public function valores(): HasMany
    {
        return $this->hasMany(PatrimonioCampoValor::class);
    }

    public function opcoesSelectArray(): array
    {
        if (empty($this->opcoes_select)) {
            return [];
        }

        return array_map('trim', explode(',', $this->opcoes_select));
    }
}

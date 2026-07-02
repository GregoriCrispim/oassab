<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orcamento extends Model
{
    protected $fillable = [
        'nome_item',
        'descricao',
        'patrimonio_categoria_id',
        'quantidade',
        'prioridade',
        'status',
        'justificativa',
        'data_necessidade',
        'usuario_solicitante',
        'observacoes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'data_necessidade' => 'date',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(PatrimonioCategoria::class, 'patrimonio_categoria_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function propostas(): HasMany
    {
        return $this->hasMany(OrcamentoProposta::class);
    }

    public function propostaSelecionada(): ?OrcamentoProposta
    {
        return $this->propostas()->where('selecionada', true)->first();
    }
}

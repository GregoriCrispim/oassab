<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrcamentoProposta extends Model
{
    protected $fillable = [
        'orcamento_id',
        'fornecedor',
        'contato_fornecedor',
        'valor_unitario',
        'quantidade',
        'valor_total',
        'custo_frete',
        'custo_instalacao',
        'prazo_entrega',
        'data_instalacao',
        'forma_pagamento',
        'garantia',
        'data_validade',
        'link_proposta',
        'observacoes',
        'selecionada',
    ];

    protected function casts(): array
    {
        return [
            'valor_unitario' => 'decimal:2',
            'valor_total' => 'decimal:2',
            'custo_frete' => 'decimal:2',
            'custo_instalacao' => 'decimal:2',
            'data_instalacao' => 'date',
            'data_validade' => 'date',
            'selecionada' => 'boolean',
        ];
    }

    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Manutencao extends Model
{
    protected $table = 'manutencoes';

    protected $fillable = [
        'patrimonio_id',
        'tipo',
        'descricao',
        'data_manutencao',
        'custo',
        'responsavel',
        'fornecedor',
        'nota_fiscal',
        'status',
        'proxima_manutencao',
        'observacoes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'data_manutencao' => 'date',
            'proxima_manutencao' => 'date',
            'custo' => 'decimal:2',
        ];
    }

    public function patrimonio(): BelongsTo
    {
        return $this->belongsTo(Patrimonio::class);
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeProximas($query, int $dias = 30)
    {
        return $query->whereNotNull('proxima_manutencao')
            ->whereBetween('proxima_manutencao', [now()->toDateString(), now()->addDays($dias)->toDateString()]);
    }
}

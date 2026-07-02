<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatrimonioUnidade extends Model
{
    protected $fillable = [
        'patrimonio_id',
        'codigo',
        'descricao',
        'imagem',
        'ordem',
    ];

    public function patrimonio(): BelongsTo
    {
        return $this->belongsTo(Patrimonio::class);
    }

    public function imagemUrl(): ?string
    {
        if (! $this->imagem) {
            return null;
        }

        if (str_starts_with($this->imagem, '/storage/')) {
            return $this->imagem;
        }

        return '/storage/'.ltrim($this->imagem, '/');
    }

    public function imagemEfetivaUrl(): ?string
    {
        return $this->imagemUrl() ?? $this->patrimonio?->imagemUrl();
    }

    public function descricaoEfetiva(): ?string
    {
        return $this->descricao ?: $this->patrimonio?->descricao;
    }
}

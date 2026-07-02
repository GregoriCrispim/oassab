<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatrimonioArquivo extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'patrimonio_id',
        'nome_original',
        'nome_arquivo',
        'tipo',
        'tamanho',
        'categoria_arquivo',
        'data_upload',
    ];

    protected function casts(): array
    {
        return [
            'data_upload' => 'datetime',
        ];
    }

    public function patrimonio(): BelongsTo
    {
        return $this->belongsTo(Patrimonio::class);
    }

    public function fileUrl(): ?string
    {
        if (! $this->nome_arquivo) {
            return null;
        }

        if (str_starts_with($this->nome_arquivo, '/storage/')) {
            return $this->nome_arquivo;
        }

        return '/storage/'.ltrim($this->nome_arquivo, '/');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatrimonioCampoValor extends Model
{
    protected $table = 'patrimonio_campo_valores';

    protected $fillable = [
        'patrimonio_id',
        'patrimonio_categoria_campo_id',
        'valor',
    ];

    public function patrimonio(): BelongsTo
    {
        return $this->belongsTo(Patrimonio::class);
    }

    public function campo(): BelongsTo
    {
        return $this->belongsTo(PatrimonioCategoriaCampo::class, 'patrimonio_categoria_campo_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditalAttachment extends Model
{
    protected $fillable = [
        'edital_id',
        'title',
        'file_path',
        'original_filename',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function edital(): BelongsTo
    {
        return $this->belongsTo(Edital::class);
    }
}

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
        'drive_file_id',
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

    public function hasFile(): bool
    {
        return filled($this->drive_file_id) || filled($this->file_path);
    }

    public function downloadUrl(): ?string
    {
        if ($this->drive_file_id) {
            return route('editais.files.attachment', [
                'edital' => $this->edital,
                'attachment' => $this,
            ]);
        }

        return $this->file_path ?: null;
    }
}

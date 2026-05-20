<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edital extends Model
{
    protected $table = 'editais';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'date',
        'file_path',
        'drive_file_id',
        'original_filename',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EditalAttachment::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('date')->orderByDesc('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function formattedDate(): string
    {
        return Post::formatDate($this->date?->toDateString() ?? '');
    }

    public function hasMainFile(): bool
    {
        return filled($this->drive_file_id) || filled($this->file_path);
    }

    public function mainFileUrl(): ?string
    {
        if ($this->drive_file_id) {
            return route('editais.files.main', $this);
        }

        return $this->file_path ?: null;
    }

    public function previous(): ?self
    {
        return self::published()
            ->where(function (Builder $q) {
                $q->where('date', '<', $this->date)
                    ->orWhere(function (Builder $qq) {
                        $qq->where('date', $this->date)->where('id', '<', $this->id);
                    });
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();
    }

    public function next(): ?self
    {
        return self::published()
            ->where(function (Builder $q) {
                $q->where('date', '>', $this->date)
                    ->orWhere(function (Builder $qq) {
                        $qq->where('date', $this->date)->where('id', '>', $this->id);
                    });
            })
            ->orderBy('date')
            ->orderBy('id')
            ->first();
    }
}

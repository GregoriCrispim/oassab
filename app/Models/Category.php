<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    public const NOTICIAS = 'noticias';
    public const PROJETOS = 'projetos';
    public const TRANSPARENCIA = 'transparencia';

    protected $fillable = ['name', 'slug'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    /** Categorias disponíveis no formulário de posts (exclui Portal Transparência). */
    public function scopeAssignableToPosts(Builder $query): Builder
    {
        return $query->where('slug', '!=', self::TRANSPARENCIA);
    }
}

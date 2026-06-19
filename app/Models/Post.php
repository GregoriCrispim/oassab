<?php

namespace App\Models;

use App\Services\PostImageStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'body',
        'date',
        'edital_id',
        'image',
        'image_meta',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_published' => 'boolean',
            'image_meta' => 'array',
        ];
    }

    /**
     * Retorna um array pronto para o componente <x-responsive-image>.
     *
     * - Se houver image_meta (gerado pelo ImageOptimizer), monta srcset
     *   com WebP + JPG nas larguras geradas.
     * - Caso contrário, faz fallback para o caminho legado em $image.
     *
     * @return array{src:string, webp_srcset:?string, jpg_srcset:?string, width:?int, height:?int, base:?string}
     */
    public function coverImageUrl(): ?string
    {
        return PostImageStorage::resolveDisplayUrl(
            $this->slug,
            $this->image,
            $this->image_meta
        );
    }

    public function imageSet(): array
    {
        return PostImageStorage::imageSet(
            $this->slug,
            $this->image,
            $this->image_meta
        );
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function edital(): BelongsTo
    {
        return $this->belongsTo(Edital::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeForCategory(Builder $query, string $slug): Builder
    {
        return $query->whereHas('categories', fn (Builder $q) => $q->where('slug', $slug));
    }

    public function scopeOrderedRecent(Builder $query): Builder
    {
        return $query->orderByDesc('date')->orderByDesc('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Slug primário usado no badge / link "voltar".
     * Prioriza categorias na ordem: noticias, projetos, transparencia.
     */
    public function primaryCategorySlug(): string
    {
        $cats = $this->categories->pluck('slug');

        foreach ([Category::NOTICIAS, Category::PROJETOS, Category::TRANSPARENCIA] as $slug) {
            if ($cats->contains($slug)) {
                return $slug;
            }
        }

        return $cats->first() ?? Category::NOTICIAS;
    }

    public function previousFor(string $categorySlug): ?Post
    {
        return self::published()
            ->forCategory($categorySlug)
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

    public function nextFor(string $categorySlug): ?Post
    {
        return self::published()
            ->forCategory($categorySlug)
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

    public function formattedDate(): string
    {
        return self::formatDate($this->date?->toDateString() ?? '');
    }

    public static function formatDate(string $date): string
    {
        if ($date === '') {
            return '';
        }

        $months = [
            '01' => 'janeiro', '02' => 'fevereiro', '03' => 'março',
            '04' => 'abril', '05' => 'maio', '06' => 'junho',
            '07' => 'julho', '08' => 'agosto', '09' => 'setembro',
            '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro',
        ];

        [$y, $m, $d] = explode('-', $date);

        return ltrim($d, '0').' de '.($months[$m] ?? $m).' de '.$y;
    }
}

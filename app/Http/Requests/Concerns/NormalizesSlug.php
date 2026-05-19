<?php

namespace App\Http\Requests\Concerns;

use App\Support\SlugHelper;

trait NormalizesSlug
{
    /** Campo usado para gerar o slug quando o slug vier vazio. */
    protected function slugSourceField(): string
    {
        return 'title';
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('slug')) {
            return;
        }

        $this->merge([
            'slug' => SlugHelper::normalize(
                $this->input('slug'),
                $this->input($this->slugSourceField())
            ),
        ]);
    }

    protected function resolveUniqueSlug(callable $existsCheck): string
    {
        $slug = (string) ($this->input('slug') ?? '');

        if ($slug === '') {
            $slug = SlugHelper::normalize(null, $this->input($this->slugSourceField())) ?? '';
        }

        return SlugHelper::ensureUnique($slug, $existsCheck);
    }
}

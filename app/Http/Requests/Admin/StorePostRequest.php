<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\NormalizesSlug;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    use NormalizesSlug;

    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        $postId = $this->route('post')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')->ignore($postId),
            ],
            'date' => ['required', 'date'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'edital_id' => ['nullable', 'integer', Rule::exists('editais', 'id')],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => [
                'integer',
                Rule::exists('categories', 'id')->where('slug', '!=', Category::TRANSPARENCIA),
            ],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'categories.required' => 'Selecione pelo menos uma categoria para o post.',
            'image.max' => 'A imagem deve ter no máximo 4 MB.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $postId = $this->route('post')?->id;
        $slug = $this->resolveUniqueSlug(
            fn (string $candidate) => Post::query()
                ->where('slug', $candidate)
                ->where('id', '!=', $postId)
                ->exists()
        );

        return [
            'title' => (string) $this->input('title'),
            'slug' => $slug,
            'date' => $this->input('date'),
            'excerpt' => $this->input('excerpt'),
            'body' => $this->input('body'),
            'edital_id' => $this->input('edital_id') ?: null,
            'is_published' => $this->boolean('is_published'),
        ];
    }
}

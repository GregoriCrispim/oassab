<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
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
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i',
                Rule::unique('posts', 'slug')->ignore($postId),
            ],
            'date' => ['required', 'date'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'categories.required' => 'Selecione pelo menos uma categoria para o post.',
            'slug.regex' => 'O slug pode conter apenas letras minúsculas, números e hífens.',
            'image.max' => 'A imagem deve ter no máximo 4 MB.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $title = (string) $this->input('title');
        $slug = $this->input('slug') ?: Str::slug($title);

        // Garante unicidade caso o usuário deixe vazio e exista colisão.
        $base = $slug;
        $i = 2;
        $postId = $this->route('post')?->id;
        while (Post::query()->where('slug', $slug)->where('id', '!=', $postId)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'date' => $this->input('date'),
            'excerpt' => $this->input('excerpt'),
            'body' => $this->input('body'),
            'is_published' => $this->boolean('is_published'),
        ];
    }
}

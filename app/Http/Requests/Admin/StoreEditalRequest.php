<?php

namespace App\Http\Requests\Admin;

use App\Models\Edital;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreEditalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        $editalId = $this->route('edital')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i',
                Rule::unique('editais', 'slug')->ignore($editalId),
            ],
            'date' => ['required', 'date'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'remove_file' => ['nullable', 'boolean'],
            'attachment_titles' => ['nullable', 'array', 'max:50'],
            'attachment_titles.*' => ['nullable', 'string', 'max:255'],
            'attachment_files' => ['nullable', 'array', 'max:50'],
            'attachment_files.*' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'remove_attachments' => ['nullable', 'array'],
            'remove_attachments.*' => ['integer', Rule::exists('edital_attachments', 'id')],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'O slug pode conter apenas letras minúsculas, números e hífens.',
            'file.mimes' => 'O edital principal deve ser um PDF.',
            'file.max' => 'O PDF principal deve ter no máximo 20 MB.',
            'attachment_files.*.mimes' => 'Cada anexo deve ser um PDF.',
            'attachment_files.*.max' => 'Cada anexo deve ter no máximo 20 MB.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $title = (string) $this->input('title');
        $slug = $this->input('slug') ?: Str::slug($title);

        $base = $slug;
        $i = 2;
        $editalId = $this->route('edital')?->id;
        while (Edital::query()->where('slug', $slug)->where('id', '!=', $editalId)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'date' => $this->input('date'),
            'excerpt' => $this->input('excerpt'),
            'body' => $this->input('body'),
            'sort_order' => (int) ($this->input('sort_order') ?? 0),
            'is_published' => $this->boolean('is_published'),
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\TransparencyDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreTransparencyDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        $documentId = $this->route('transparency_document')?->id;
        $isUpdate = $documentId !== null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i',
                Rule::unique('transparency_documents', 'slug')->ignore($documentId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'processo' => ['nullable', 'string', 'max:255'],
            'valor_global' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'file' => [
                $isUpdate ? 'nullable' : 'required',
                'file',
                'mimes:pdf',
                'max:20480',
            ],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'O slug pode conter apenas letras minúsculas, números e hífens.',
            'file.required' => 'Envie o arquivo PDF do documento.',
            'file.mimes' => 'O arquivo deve ser um PDF.',
            'file.max' => 'O PDF deve ter no máximo 20 MB.',
            'year.regex' => 'O ano deve ter 4 dígitos (ex.: 2024).',
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
        $documentId = $this->route('transparency_document')?->id;
        while (TransparencyDocument::query()->where('slug', $slug)->where('id', '!=', $documentId)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'description' => $this->input('description'),
            'processo' => $this->input('processo'),
            'valor_global' => $this->input('valor_global'),
            'year' => $this->input('year'),
            'sort_order' => (int) ($this->input('sort_order') ?? 0),
            'is_published' => $this->boolean('is_published'),
        ];
    }
}

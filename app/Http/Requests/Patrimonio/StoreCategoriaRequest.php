<?php

namespace App\Http\Requests\Patrimonio;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'descricao' => ['nullable', 'string'],
            'indice_depreciacao_padrao' => ['required', 'numeric', 'min:0', 'max:100'],
            'icone' => ['nullable', 'string', 'max:50'],
            'cor' => ['nullable', 'string', 'max:20'],
            'ativo' => ['nullable', 'boolean'],
            'campos' => ['nullable', 'array'],
            'campos.*.id' => ['nullable', 'integer'],
            'campos.*.nome_campo' => ['nullable', 'string', 'max:100'],
            'campos.*.label' => ['nullable', 'string', 'max:100'],
            'campos.*.tipo_campo' => ['nullable', 'in:texto,numero,data,select,textarea'],
            'campos.*.opcoes_select' => ['nullable', 'string'],
            'campos.*.obrigatorio' => ['nullable', 'boolean'],
        ];
    }

    public function categoriaData(): array
    {
        return [
            'nome' => $this->input('nome'),
            'descricao' => $this->input('descricao'),
            'indice_depreciacao_padrao' => $this->input('indice_depreciacao_padrao'),
            'icone' => $this->input('icone', 'bi-tag'),
            'cor' => $this->input('cor', '#6366f1'),
            'ativo' => $this->boolean('ativo', true),
        ];
    }
}

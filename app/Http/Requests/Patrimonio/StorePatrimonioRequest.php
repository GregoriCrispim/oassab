<?php

namespace App\Http\Requests\Patrimonio;

use Illuminate\Foundation\Http\FormRequest;

class StorePatrimonioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:50'],
            'nome' => ['required', 'string', 'max:200'],
            'descricao' => ['nullable', 'string'],
            'patrimonio_categoria_id' => ['nullable', 'exists:patrimonio_categorias,id'],
            'valor_aquisicao' => ['required', 'numeric', 'min:0.01'],
            'indice_depreciacao' => ['required', 'numeric', 'min:0', 'max:100'],
            'data_aquisicao' => ['required', 'date'],
            'localizacao' => ['nullable', 'string', 'max:200'],
            'responsavel' => ['nullable', 'string', 'max:100'],
            'nota_fiscal' => ['nullable', 'string', 'max:100'],
            'observacoes' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
            'quantidade' => ['nullable', 'integer', 'min:1', 'max:999'],
            'modo_imagem' => ['nullable', 'in:unica,individual'],
            'unidades' => ['nullable', 'array'],
            'unidades.*.id' => ['nullable', 'integer', 'exists:patrimonio_unidades,id'],
            'unidades.*.descricao' => ['nullable', 'string'],
            'unidades.*.imagem' => ['nullable', 'image', 'max:5120'],
            'unidades.*.excluir' => ['nullable', 'boolean'],
            'unidades.*.remover_imagem' => ['nullable', 'boolean'],
            'unidades_novas' => ['nullable', 'array'],
            'unidades_novas.*.descricao' => ['nullable', 'string'],
            'unidades_novas.*.imagem' => ['nullable', 'image', 'max:5120'],
            'campos_customizados' => ['nullable', 'array'],
            'campos_customizados.*' => ['nullable', 'string'],
            'imagem' => ['nullable', 'image', 'max:5120'],
            'arquivos' => ['nullable', 'array'],
            'arquivos.*' => ['file', 'max:10240'],
        ];
    }
}

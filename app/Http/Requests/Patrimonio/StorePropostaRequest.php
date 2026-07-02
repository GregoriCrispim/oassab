<?php

namespace App\Http\Requests\Patrimonio;

use Illuminate\Foundation\Http\FormRequest;

class StorePropostaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposta_id' => ['nullable', 'integer'],
            'fornecedor' => ['required', 'string', 'max:255'],
            'contato_fornecedor' => ['nullable', 'string', 'max:255'],
            'valor_unitario' => ['required', 'numeric', 'min:0'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'custo_frete' => ['nullable', 'numeric', 'min:0'],
            'custo_instalacao' => ['nullable', 'numeric', 'min:0'],
            'prazo_entrega' => ['nullable', 'string', 'max:100'],
            'data_instalacao' => ['nullable', 'date'],
            'forma_pagamento' => ['nullable', 'string', 'max:255'],
            'garantia' => ['nullable', 'string', 'max:255'],
            'data_validade' => ['nullable', 'date'],
            'link_proposta' => ['nullable', 'url', 'max:500'],
            'observacoes' => ['nullable', 'string'],
            'selecionada' => ['nullable', 'boolean'],
        ];
    }
}

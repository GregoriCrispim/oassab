<?php

namespace App\Http\Requests\Patrimonio;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrcamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome_item' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'patrimonio_categoria_id' => ['nullable', 'exists:patrimonio_categorias,id'],
            'quantidade' => ['nullable', 'integer', 'min:1'],
            'prioridade' => ['required', 'in:baixa,media,alta,urgente'],
            'status' => ['required', 'in:aberto,em_cotacao,aprovado,cancelado,finalizado'],
            'justificativa' => ['nullable', 'string'],
            'data_necessidade' => ['nullable', 'date'],
            'usuario_solicitante' => ['nullable', 'string', 'max:100'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}

<?php

namespace App\Http\Requests\Patrimonio;

use Illuminate\Foundation\Http\FormRequest;

class StoreManutencaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patrimonio_id' => ['required', 'exists:patrimonios,id'],
            'tipo' => ['required', 'in:preventiva,corretiva,preditiva'],
            'descricao' => ['required', 'string'],
            'data_manutencao' => ['required', 'date'],
            'custo' => ['nullable', 'numeric', 'min:0'],
            'responsavel' => ['nullable', 'string', 'max:100'],
            'fornecedor' => ['nullable', 'string', 'max:200'],
            'nota_fiscal' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:agendada,em_andamento,concluida,cancelada'],
            'proxima_manutencao' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}

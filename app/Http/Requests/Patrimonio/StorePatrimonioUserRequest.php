<?php

namespace App\Http\Requests\Patrimonio;

use App\Enums\PatrimonioRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePatrimonioUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('usuario')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'patrimonio_role' => ['required', new Enum(PatrimonioRole::class)],
        ];
    }
}

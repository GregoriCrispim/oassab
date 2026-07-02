<?php

namespace App\Services\Patrimonio;

use App\Models\PatrimonioLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class PatrimonioLogService
{
    public function registrar(string $acao, ?string $tabela = null, ?int $registroId = null, ?string $descricao = null): void
    {
        PatrimonioLog::create([
            'user_id' => Auth::id(),
            'acao' => $acao,
            'tabela' => $tabela,
            'registro_id' => $registroId,
            'descricao' => $descricao,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }

    public function logModel(string $acao, Model $model, ?string $descricao = null): void
    {
        $this->registrar(
            $acao,
            $model->getTable(),
            $model->getKey(),
            $descricao ?? class_basename($model).' #'.$model->getKey(),
        );
    }
}

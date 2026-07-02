<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patrimonio extends Model
{
    protected $fillable = [
        'codigo',
        'quantidade',
        'codigos_inventario',
        'nome',
        'descricao',
        'patrimonio_categoria_id',
        'valor_aquisicao',
        'indice_depreciacao',
        'valor_depreciado',
        'valor_atual',
        'data_aquisicao',
        'localizacao',
        'responsavel',
        'nota_fiscal',
        'imagem',
        'observacoes',
        'ativo',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'codigos_inventario' => 'array',
            'valor_aquisicao' => 'decimal:2',
            'indice_depreciacao' => 'decimal:2',
            'valor_depreciado' => 'decimal:2',
            'valor_atual' => 'decimal:2',
            'data_aquisicao' => 'date',
            'ativo' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(PatrimonioCategoria::class, 'patrimonio_categoria_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campoValores(): HasMany
    {
        return $this->hasMany(PatrimonioCampoValor::class);
    }

    public function arquivos(): HasMany
    {
        return $this->hasMany(PatrimonioArquivo::class);
    }

    public function manutencoes(): HasMany
    {
        return $this->hasMany(Manutencao::class);
    }

    public function itensInventario(): HasMany
    {
        return $this->hasMany(PatrimonioUnidade::class)->orderBy('ordem');
    }

    public function unidades(): int
    {
        if ($this->relationLoaded('itensInventario')) {
            $count = $this->itensInventario->count();

            return $count > 0 ? $count : max(1, (int) $this->quantidade);
        }

        if ($this->quantidade > 1 && $this->itensInventario()->exists()) {
            return $this->itensInventario()->count();
        }

        return max(1, (int) $this->quantidade);
    }

    /** @return array<int, string> */
    public function todosCodigosInventario(): array
    {
        if ($this->relationLoaded('itensInventario') && $this->itensInventario->isNotEmpty()) {
            return $this->itensInventario->pluck('codigo')->all();
        }

        if ($this->quantidade > 1) {
            $codigos = $this->itensInventario()->orderBy('ordem')->pluck('codigo')->all();

            if ($codigos !== []) {
                return $codigos;
            }
        }

        return array_values(array_filter([
            $this->codigo,
            ...($this->codigos_inventario ?? []),
        ]));
    }

    public function unidadePorCodigo(string $codigo): ?PatrimonioUnidade
    {
        if ($this->relationLoaded('itensInventario')) {
            return $this->itensInventario->firstWhere('codigo', $codigo);
        }

        return $this->itensInventario()->where('codigo', $codigo)->first();
    }

    public function imagemParaCodigo(?string $codigo = null): ?string
    {
        if ($codigo && $this->unidades() > 1) {
            $unidade = $this->unidadePorCodigo($codigo);

            if ($unidade?->imagemUrl()) {
                return $unidade->imagemUrl();
            }
        }

        return $this->imagemUrl();
    }

    public function descricaoParaCodigo(?string $codigo = null): ?string
    {
        if ($codigo && $this->unidades() > 1) {
            $unidade = $this->unidadePorCodigo($codigo);

            if ($unidade?->descricao) {
                return $unidade->descricao;
            }
        }

        return $this->descricao;
    }

    public function usaImagensIndividuais(): bool
    {
        if ($this->unidades() <= 1) {
            return false;
        }

        if ($this->relationLoaded('itensInventario')) {
            return $this->itensInventario->contains(fn (PatrimonioUnidade $u) => (bool) $u->imagem);
        }

        return $this->itensInventario()->whereNotNull('imagem')->exists();
    }

    public function codigoResumo(): string
    {
        if ($this->unidades() <= 1) {
            return $this->codigo;
        }

        $codigos = $this->todosCodigosInventario();
        $ultimo = count($codigos) > 1 ? $codigos[array_key_last($codigos)] : null;

        return $ultimo && $ultimo !== $this->codigo
            ? "{$this->codigo} – {$ultimo}"
            : $this->codigo;
    }

    public function valorAquisicaoTotal(): float
    {
        return (float) $this->valor_aquisicao * $this->unidades();
    }

    public function valorAtualTotal(): float
    {
        return (float) $this->valor_atual * $this->unidades();
    }

    public function imagemUrl(): ?string
    {
        if (! $this->imagem) {
            return null;
        }

        if (str_starts_with($this->imagem, '/storage/')) {
            return $this->imagem;
        }

        return '/storage/'.ltrim($this->imagem, '/');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeBusca($query, ?string $termo)
    {
        if (empty($termo)) {
            return $query;
        }

        return $query->where(function ($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
                ->orWhere('codigo', 'like', "%{$termo}%")
                ->orWhere('localizacao', 'like', "%{$termo}%")
                ->orWhere('responsavel', 'like', "%{$termo}%");
        });
    }
}

<form method="POST"
      action="{{ $manutencao->exists ? route('patrimonios.manutencoes.update', $manutencao) : route('patrimonios.manutencoes.store') }}"
      data-form-modal
      class="space-y-6">
    @csrf
    @if ($manutencao->exists) @method('PUT') @endif

    <x-patrimonios.form-section
        title="Patrimônio e Classificação"
        icon="wrench"
        subtitle="Vincule o bem e defina tipo e status da manutenção."
    >
        <div class="space-y-5">
            <x-patrimonios.form-field label="Patrimônio" name="patrimonio_id" required>
                <select name="patrimonio_id" id="patrimonio_id" required class="form-input">
                    <option value="">Selecione...</option>
                    @foreach ($patrimonios as $p)
                        <option value="{{ $p->id }}" @selected(old('patrimonio_id', $manutencao->patrimonio_id) == $p->id)>{{ $p->codigo }} — {{ $p->nome }}</option>
                    @endforeach
                </select>
            </x-patrimonios.form-field>

            <div class="form-grid">
                <x-patrimonios.form-field label="Tipo" name="tipo" required>
                    <select name="tipo" id="tipo" class="form-input">
                        @foreach (['preventiva','corretiva','preditiva'] as $t)
                            <option value="{{ $t }}" @selected(old('tipo', $manutencao->tipo) === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </x-patrimonios.form-field>

                <x-patrimonios.form-field label="Status" name="status" required>
                    <select name="status" id="status" class="form-input">
                        @foreach (['agendada','em_andamento','concluida','cancelada'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $manutencao->status) === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </x-patrimonios.form-field>
            </div>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section title="Detalhes" icon="card-text" subtitle="Descrição do serviço ou intervenção realizada.">
        <x-patrimonios.form-field label="Descrição" name="descricao" required>
            <textarea name="descricao" id="descricao" rows="3" required class="form-input" placeholder="Descreva a manutenção realizada ou planejada">{{ old('descricao', $manutencao->descricao) }}</textarea>
        </x-patrimonios.form-field>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section title="Datas e Custos" icon="calendar-event" subtitle="Agendamento, próxima revisão e valores.">
        <div class="form-grid">
            <x-patrimonios.form-field label="Data da Manutenção" name="data_manutencao" required>
                <input type="date" name="data_manutencao" id="data_manutencao" value="{{ old('data_manutencao', $manutencao->data_manutencao?->format('Y-m-d')) }}" required class="form-input">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Próxima Manutenção" name="proxima_manutencao" hint="Opcional — para manutenções preventivas recorrentes.">
                <input type="date" name="proxima_manutencao" id="proxima_manutencao" value="{{ old('proxima_manutencao', $manutencao->proxima_manutencao?->format('Y-m-d')) }}" class="form-input">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Custo (R$)" name="custo">
                <input type="number" step="0.01" name="custo" id="custo" value="{{ old('custo', $manutencao->custo) }}" class="form-input" placeholder="0,00">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Fornecedor" name="fornecedor">
                <input type="text" name="fornecedor" id="fornecedor" value="{{ old('fornecedor', $manutencao->fornecedor) }}" class="form-input" placeholder="Empresa ou prestador">
            </x-patrimonios.form-field>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-actions />
</form>

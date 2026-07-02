<?php

namespace Database\Seeders;

use App\Models\PatrimonioCategoria;
use App\Models\PatrimonioCategoriaCampo;
use Illuminate\Database\Seeder;

class PatrimonioCategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nome' => 'Equipamentos de Informática',
                'descricao' => 'Computadores, notebooks, impressoras, etc.',
                'indice_depreciacao_padrao' => 20.00,
                'icone' => 'bi-laptop',
                'cor' => '#3b82f6',
                'campos' => [
                    ['nome_campo' => 'numero_serie', 'label' => 'Número de Série', 'tipo_campo' => 'texto', 'ordem' => 1],
                    ['nome_campo' => 'processador', 'label' => 'Processador', 'tipo_campo' => 'texto', 'ordem' => 2],
                    ['nome_campo' => 'memoria_ram', 'label' => 'Memória RAM (GB)', 'tipo_campo' => 'numero', 'ordem' => 3],
                    ['nome_campo' => 'armazenamento', 'label' => 'Armazenamento', 'tipo_campo' => 'texto', 'ordem' => 4],
                    ['nome_campo' => 'sistema_operacional', 'label' => 'Sistema Operacional', 'tipo_campo' => 'select', 'opcoes_select' => 'Windows 10,Windows 11,Linux,macOS,Outro', 'ordem' => 5],
                ],
            ],
            [
                'nome' => 'Móveis e Utensílios',
                'descricao' => 'Mesas, cadeiras, armários, etc.',
                'indice_depreciacao_padrao' => 10.00,
                'icone' => 'bi-archive',
                'cor' => '#8b5cf6',
                'campos' => [],
            ],
            [
                'nome' => 'Veículos',
                'descricao' => 'Carros, motos, caminhões, etc.',
                'indice_depreciacao_padrao' => 15.00,
                'icone' => 'bi-truck',
                'cor' => '#ef4444',
                'campos' => [
                    ['nome_campo' => 'placa', 'label' => 'Placa', 'tipo_campo' => 'texto', 'obrigatorio' => true, 'ordem' => 1],
                    ['nome_campo' => 'chassi', 'label' => 'Chassi', 'tipo_campo' => 'texto', 'ordem' => 2],
                    ['nome_campo' => 'marca', 'label' => 'Marca', 'tipo_campo' => 'texto', 'obrigatorio' => true, 'ordem' => 3],
                    ['nome_campo' => 'modelo', 'label' => 'Modelo', 'tipo_campo' => 'texto', 'obrigatorio' => true, 'ordem' => 4],
                    ['nome_campo' => 'combustivel', 'label' => 'Combustível', 'tipo_campo' => 'select', 'opcoes_select' => 'Gasolina,Etanol,Flex,Diesel,Elétrico,Híbrido', 'ordem' => 5],
                ],
            ],
            [
                'nome' => 'Máquinas e Equipamentos',
                'descricao' => 'Máquinas industriais e equipamentos diversos',
                'indice_depreciacao_padrao' => 12.00,
                'icone' => 'bi-gear',
                'cor' => '#f59e0b',
                'campos' => [],
            ],
            [
                'nome' => 'Imóveis',
                'descricao' => 'Terrenos, prédios, salas comerciais, etc.',
                'indice_depreciacao_padrao' => 4.00,
                'icone' => 'bi-building',
                'cor' => '#10b981',
                'campos' => [
                    ['nome_campo' => 'matricula', 'label' => 'Matrícula', 'tipo_campo' => 'texto', 'ordem' => 1],
                    ['nome_campo' => 'tipo_imovel', 'label' => 'Tipo de Imóvel', 'tipo_campo' => 'select', 'opcoes_select' => 'Terreno,Casa,Apartamento,Sala Comercial,Galpão,Prédio,Fazenda', 'obrigatorio' => true, 'ordem' => 2],
                    ['nome_campo' => 'endereco_completo', 'label' => 'Endereço Completo', 'tipo_campo' => 'textarea', 'obrigatorio' => true, 'ordem' => 3],
                ],
            ],
            [
                'nome' => 'Ferramentas',
                'descricao' => 'Ferramentas manuais e elétricas',
                'indice_depreciacao_padrao' => 10.00,
                'icone' => 'bi-tools',
                'cor' => '#6b7280',
                'campos' => [],
            ],
            [
                'nome' => 'Eletrônicos',
                'descricao' => 'TV, aparelhos de som, telefones, etc.',
                'indice_depreciacao_padrao' => 18.00,
                'icone' => 'bi-tv',
                'cor' => '#ec4899',
                'campos' => [],
            ],
        ];

        foreach ($categorias as $data) {
            $campos = $data['campos'];
            unset($data['campos']);

            $categoria = PatrimonioCategoria::firstOrCreate(
                ['nome' => $data['nome']],
                $data,
            );

            foreach ($campos as $campo) {
                PatrimonioCategoriaCampo::firstOrCreate(
                    [
                        'patrimonio_categoria_id' => $categoria->id,
                        'nome_campo' => $campo['nome_campo'],
                    ],
                    array_merge($campo, ['patrimonio_categoria_id' => $categoria->id]),
                );
            }
        }
    }
}

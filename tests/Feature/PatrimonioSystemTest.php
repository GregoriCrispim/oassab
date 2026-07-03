<?php

namespace Tests\Feature;

use App\Models\Patrimonio;
use App\Models\PatrimonioCategoria;
use App\Models\User;
use Database\Seeders\PatrimonioCategoriasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatrimonioSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_admin' => true,
            'patrimonio_role' => 'admin',
        ]);

        $this->seed(PatrimonioCategoriasSeeder::class);
    }

    public function test_guest_is_redirected_from_patrimonio_area(): void
    {
        $this->get(route('patrimonios.dashboard'))
            ->assertRedirect();
    }

    public function test_admin_can_access_all_main_pages(): void
    {
        $routes = [
            'patrimonios.dashboard',
            'patrimonios.patrimonios.index',
            'patrimonios.categorias.index',
            'patrimonios.manutencoes.index',
            'patrimonios.orcamentos.index',
            'patrimonios.qr-scanner',
            'patrimonios.logs.index',
            'patrimonios.usuarios.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($this->admin)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_admin_can_create_and_deactivate_patrimonio(): void
    {
        $categoria = PatrimonioCategoria::query()->first();

        $this->actingAs($this->admin)
            ->post(route('patrimonios.patrimonios.store'), [
                'nome' => 'Item Teste Apresentação',
                'data_aquisicao' => now()->toDateString(),
                'valor_aquisicao' => 1500,
                'indice_depreciacao' => 10,
                'patrimonio_categoria_id' => $categoria->id,
                'quantidade' => 1,
                'ativo' => 1,
            ])
            ->assertRedirect(route('patrimonios.patrimonios.index'));

        $patrimonio = Patrimonio::query()->where('nome', 'Item Teste Apresentação')->first();
        $this->assertNotNull($patrimonio);
        $this->assertTrue($patrimonio->ativo);

        $this->actingAs($this->admin)
            ->post(route('patrimonios.patrimonios.update', $patrimonio), [
                'nome' => 'Item Teste Apresentação',
                'data_aquisicao' => $patrimonio->data_aquisicao->toDateString(),
                'valor_aquisicao' => 1500,
                'indice_depreciacao' => 10,
                'patrimonio_categoria_id' => $categoria->id,
                'quantidade' => 1,
                'ativo' => 0,
            ])
            ->assertRedirect(route('patrimonios.patrimonios.index'));

        $this->assertFalse($patrimonio->fresh()->ativo);
    }

    public function test_qr_scanner_finds_patrimonio_by_code(): void
    {
        $patrimonio = Patrimonio::query()->create([
            'codigo' => 'PAT-999',
            'quantidade' => 1,
            'nome' => 'Scanner Test',
            'valor_aquisicao' => 100,
            'indice_depreciacao' => 10,
            'valor_depreciado' => 0,
            'valor_atual' => 100,
            'data_aquisicao' => now(),
            'ativo' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('patrimonios.qr-scanner.buscar'), ['codigo' => 'PAT-999'])
            ->assertOk()
            ->assertJsonPath('sucesso', true)
            ->assertJsonPath('patrimonio.id', $patrimonio->id);
    }

    public function test_reports_are_available_for_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('patrimonios.relatorios.patrimonios.csv'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('patrimonios.relatorios.patrimonios.pdf'))
            ->assertOk();
    }

    public function test_leitor_cannot_access_logs(): void
    {
        $leitor = User::factory()->create([
            'patrimonio_role' => 'leitor',
        ]);

        $this->actingAs($leitor)
            ->get(route('patrimonios.logs.index'))
            ->assertForbidden();
    }
}

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

    public function test_qr_codes_are_stored_on_patrimonio_create(): void
    {
        $categoria = PatrimonioCategoria::query()->first();

        $this->actingAs($this->admin)
            ->post(route('patrimonios.patrimonios.store'), [
                'nome' => 'Item com QR Code',
                'data_aquisicao' => now()->toDateString(),
                'valor_aquisicao' => 500,
                'indice_depreciacao' => 10,
                'patrimonio_categoria_id' => $categoria->id,
                'quantidade' => 1,
                'ativo' => 1,
            ])
            ->assertRedirect(route('patrimonios.patrimonios.index'));

        $patrimonio = Patrimonio::query()->where('nome', 'Item com QR Code')->first();
        $this->assertNotNull($patrimonio);

        $relativePath = 'patrimonios/'.$patrimonio->codigo.'/qrcodes/'.$patrimonio->codigo.'.svg';

        $this->assertFileExists(storage_path('app/public/'.$relativePath));
        $this->assertFileExists(public_path('storage/'.$relativePath));

        $this->actingAs($this->admin)
            ->getJson(route('patrimonios.patrimonios.qrcodes.data', $patrimonio))
            ->assertOk()
            ->assertJsonPath('qrcodes.'.$patrimonio->codigo, '/storage/'.$relativePath);
    }

    public function test_multi_unit_patrimonio_gets_one_qr_code_per_inventory_code(): void
    {
        $categoria = PatrimonioCategoria::query()->first();

        $this->actingAs($this->admin)
            ->post(route('patrimonios.patrimonios.store'), [
                'nome' => 'Cadeiras em lote',
                'data_aquisicao' => now()->toDateString(),
                'valor_aquisicao' => 200,
                'indice_depreciacao' => 10,
                'patrimonio_categoria_id' => $categoria->id,
                'quantidade' => 3,
                'ativo' => 1,
            ])
            ->assertRedirect(route('patrimonios.patrimonios.index'));

        $patrimonio = Patrimonio::query()->where('nome', 'Cadeiras em lote')->first();
        $this->assertNotNull($patrimonio);
        $this->assertCount(3, $patrimonio->fresh()->todosCodigosInventario());

        foreach ($patrimonio->todosCodigosInventario() as $codigo) {
            $relativePath = 'patrimonios/'.$codigo.'/qrcodes/'.$codigo.'.svg';
            $this->assertFileExists(storage_path('app/public/'.$relativePath));
            $this->assertFileExists(public_path('storage/'.$relativePath));

            if ($codigo !== $patrimonio->codigo) {
                $this->assertFileDoesNotExist(storage_path('app/public/patrimonios/'.$patrimonio->codigo.'/qrcodes/'.$codigo.'.svg'));
            }
        }

        $response = $this->actingAs($this->admin)
            ->getJson(route('patrimonios.patrimonios.qrcodes.data', $patrimonio))
            ->assertOk();

        $this->assertCount(3, $response->json('codigos'));
        $this->assertCount(3, $response->json('qrcodes'));

        $qrService = app(\App\Services\Patrimonio\QrCodeService::class);
        $conteudos = [];

        foreach ($patrimonio->todosCodigosInventario() as $codigo) {
            $conteudos[] = $qrService->dataForPatrimonio($patrimonio, $codigo);
        }

        $this->assertCount(3, array_unique($conteudos));

        $codigos = $patrimonio->todosCodigosInventario();
        $this->assertStringContainsString('unidade='.urlencode($codigos[1]), $conteudos[1]);
    }

    public function test_qr_scanner_returns_unit_and_group_for_multi_unit_patrimonio(): void
    {
        $categoria = PatrimonioCategoria::query()->first();

        $this->actingAs($this->admin)
            ->post(route('patrimonios.patrimonios.store'), [
                'nome' => 'Mesas do salão',
                'data_aquisicao' => now()->toDateString(),
                'valor_aquisicao' => 300,
                'indice_depreciacao' => 10,
                'patrimonio_categoria_id' => $categoria->id,
                'quantidade' => 2,
                'ativo' => 1,
            ]);

        $patrimonio = Patrimonio::query()->where('nome', 'Mesas do salão')->first();
        $codigoSecundario = $patrimonio->todosCodigosInventario()[1];

        $this->actingAs($this->admin)
            ->postJson(route('patrimonios.qr-scanner.buscar'), ['codigo' => $codigoSecundario])
            ->assertOk()
            ->assertJsonPath('sucesso', true)
            ->assertJsonPath('patrimonio.codigo', $codigoSecundario)
            ->assertJsonPath('patrimonio.multiplas_unidades', true)
            ->assertJsonPath('patrimonio.unidade.codigo', $codigoSecundario)
            ->assertJsonPath('patrimonio.grupo.nome', 'Mesas do salão')
            ->assertJsonPath('patrimonio.grupo.total_unidades', 2);
    }

    public function test_show_page_highlights_scanned_unit(): void
    {
        $categoria = PatrimonioCategoria::query()->first();

        $this->actingAs($this->admin)
            ->post(route('patrimonios.patrimonios.store'), [
                'nome' => 'Cadeira azul',
                'data_aquisicao' => now()->toDateString(),
                'valor_aquisicao' => 100,
                'indice_depreciacao' => 10,
                'patrimonio_categoria_id' => $categoria->id,
                'quantidade' => 2,
                'ativo' => 1,
            ]);

        $patrimonio = Patrimonio::query()->where('nome', 'Cadeira azul')->first();
        $codigoSecundario = $patrimonio->todosCodigosInventario()[1];

        $this->actingAs($this->admin)
            ->get(route('patrimonios.patrimonios.show', [$patrimonio, 'unidade' => $codigoSecundario]))
            ->assertOk()
            ->assertSee('Unidade escaneada')
            ->assertSee($codigoSecundario)
            ->assertSee('Conjunto');
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

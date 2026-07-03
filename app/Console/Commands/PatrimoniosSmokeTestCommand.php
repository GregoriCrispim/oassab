<?php

namespace App\Console\Commands;

use App\Models\Orcamento;
use App\Models\Patrimonio;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatrimoniosSmokeTestCommand extends Command
{
    protected $signature = 'patrimonios:smoke-test';

    protected $description = 'Verifica rotas e fluxos críticos do módulo de patrimônios';

    public function handle(): int
    {
        $user = User::query()
            ->where(function ($q) {
                $q->where('is_admin', true)->orWhere('patrimonio_role', 'admin');
            })
            ->first();

        if (! $user) {
            $this->error('Nenhum usuário admin de patrimônio encontrado.');

            return self::FAILURE;
        }

        $this->info('Usuário de teste: '.$user->email);

        $failures = 0;
        $passes = 0;

        $getRoutes = [
            'patrimonios.dashboard',
            'patrimonios.patrimonios.index',
            'patrimonios.patrimonios.create',
            'patrimonios.categorias.index',
            'patrimonios.manutencoes.index',
            'patrimonios.manutencoes.create',
            'patrimonios.orcamentos.index',
            'patrimonios.orcamentos.create',
            'patrimonios.qr-scanner',
            'patrimonios.logs.index',
            'patrimonios.usuarios.index',
            'patrimonios.relatorios.patrimonios.csv',
            'patrimonios.relatorios.patrimonios.pdf',
            'patrimonios.relatorios.orcamentos.csv',
            'patrimonios.relatorios.orcamentos.pdf',
        ];

        foreach ($getRoutes as $name) {
            $response = $this->requestAs($user, 'GET', route($name));
            $status = $response->getStatusCode();

            if ($status >= 200 && $status < 400) {
                $this->line("<fg=green>✓</> GET {$name} ({$status})");
                $passes++;
            } else {
                $this->line("<fg=red>✗</> GET {$name} ({$status})");
                $failures++;
            }
        }

        $patrimonio = Patrimonio::query()->first();
        if ($patrimonio) {
            foreach ([
                'patrimonios.patrimonios.show',
                'patrimonios.patrimonios.edit',
                'patrimonios.patrimonios.qrcodes.data',
                'patrimonios.patrimonios.qrcode',
            ] as $name) {
                $response = $this->requestAs($user, 'GET', route($name, $patrimonio));
                $status = $response->getStatusCode();

                if ($status >= 200 && $status < 400) {
                    $this->line("<fg=green>✓</> GET {$name} ({$status})");
                    $passes++;
                } else {
                    $this->line("<fg=red>✗</> GET {$name} ({$status})");
                    $failures++;
                }
            }

            $qrRequest = Request::create('/', 'POST', ['codigo' => $patrimonio->codigo]);
            $qrRequest->setUserResolver(fn () => $user);
            Auth::login($user);
            $qrResponse = app(\App\Http\Controllers\Patrimonio\QrScannerController::class)->buscar($qrRequest);
            $qrBody = json_decode($qrResponse->getContent(), true);
            if (($qrBody['sucesso'] ?? false) === true) {
                $this->line('<fg=green>✓</> POST qr-scanner.buscar');
                $passes++;
            } else {
                $this->line('<fg=red>✗</> POST qr-scanner.buscar');
                $failures++;
            }

            $modalResponse = $this->requestAs($user, 'GET', route('patrimonios.patrimonios.edit', $patrimonio), [], [
                'X-Form-Modal' => '1',
                'Accept' => 'application/json',
            ]);

            $modalBody = json_decode($modalResponse->getContent(), true);
            if ($modalResponse->getStatusCode() === 200 && ! empty($modalBody['html'])) {
                $this->line('<fg=green>✓</> Modal edit patrimônio (JSON)');
                $passes++;
            } else {
                $this->line('<fg=red>✗</> Modal edit patrimônio (JSON)');
                $failures++;
            }

            $listResponse = $this->requestAs($user, 'GET', route('patrimonios.patrimonios.index'), [], [
                'X-Patrimonio-List' => '1',
            ]);

            if ($listResponse->getStatusCode() === 200 && str_contains($listResponse->getContent(), 'patrimonio-table')) {
                $this->line('<fg=green>✓</> Live search list partial');
                $passes++;
            } else {
                $this->line('<fg=red>✗</> Live search list partial');
                $failures++;
            }
        } else {
            $this->warn('Nenhum patrimônio no banco — pulando testes de detalhe.');
        }

        $orcamento = Orcamento::query()->first();
        if ($orcamento) {
            $response = $this->requestAs($user, 'GET', route('patrimonios.orcamentos.edit', $orcamento), [], [
                'X-Form-Modal' => '1',
                'Accept' => 'application/json',
            ]);
            $body = json_decode($response->getContent(), true);

            if ($response->getStatusCode() === 200 && ! empty($body['html'])) {
                $this->line('<fg=green>✓</> Modal edit orçamento (JSON)');
                $passes++;
            } else {
                $this->line('<fg=red>✗</> Modal edit orçamento (JSON)');
                $failures++;
            }
        }

        $manifest = public_path('build/manifest.json');
        if (is_file($manifest)) {
            $this->line('<fg=green>✓</> Vite manifest presente');
            $passes++;

            $data = json_decode(file_get_contents($manifest), true) ?: [];
            foreach ($data as $entry) {
                $asset = public_path('build/'.$entry['file']);
                if (! is_file($asset)) {
                    $this->line("<fg=red>✗</> Asset ausente: {$entry['file']}");
                    $failures++;
                }
            }
        } else {
            $this->line('<fg=red>✗</> Vite manifest ausente — rode npm run build');
            $failures++;
        }

        $this->newLine();
        $this->info("Resultado: {$passes} ok, {$failures} falha(s)");

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function requestAs(User $user, string $method, string $uri, array $data = [], array $headers = []): \Symfony\Component\HttpFoundation\Response
    {
        Auth::login($user);

        $kernel = app(Kernel::class);
        $request = Request::create($uri, $method, $data);
        $request->headers->add($headers);
        $request->setUserResolver(fn () => $user);

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return $response;
    }
}

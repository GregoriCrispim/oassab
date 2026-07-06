<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\EditalController;
use App\Http\Controllers\Admin\TransparencyDocumentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EditalFileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Patrimonio\CategoriaController as PatrimonioCategoriaController;
use App\Http\Controllers\Patrimonio\DashboardController as PatrimonioDashboardController;
use App\Http\Controllers\Patrimonio\LogController as PatrimonioLogController;
use App\Http\Controllers\Patrimonio\ManutencaoController;
use App\Http\Controllers\Patrimonio\OrcamentoController;
use App\Http\Controllers\Patrimonio\PatrimonioController;
use App\Http\Controllers\Patrimonio\QrScannerController;
use App\Http\Controllers\Patrimonio\RelatorioController as PatrimonioRelatorioController;
use App\Http\Controllers\Patrimonio\UserController as PatrimonioUserController;
use App\Http\Controllers\PostFileController;
use Illuminate\Support\Facades\Route;

Route::bind('usuario', fn (string $value) => \App\Models\User::findOrFail($value));
Route::bind('categoria', fn (string $value) => \App\Models\PatrimonioCategoria::findOrFail($value));
Route::bind('manutencao', fn (string $value) => \App\Models\Manutencao::findOrFail($value));

// --------------------------------------------------------------------
// Site público (cache de página inteira via middleware page.cache)
// --------------------------------------------------------------------
Route::middleware('page.cache')->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/quem-somos', [PageController::class, 'quemSomos'])->name('quem-somos');
    Route::get('/projetos', [PageController::class, 'projetos'])->name('projetos');
    Route::get('/editais', [PageController::class, 'editais'])->name('editais');
    Route::get('/editais/{edital:slug}/download', [EditalFileController::class, 'main'])->name('editais.files.main');
    Route::get('/editais/{edital:slug}/anexos/{attachment}/download', [EditalFileController::class, 'attachment'])
        ->name('editais.files.attachment');
    Route::get('/editais/{edital:slug}', [PageController::class, 'edital'])->name('edital');
    Route::get('/transparencia', [PageController::class, 'transparencia'])->name('transparencia');
    Route::get('/contato', [PageController::class, 'contato'])->name('contato');
    Route::get('/noticias', [PageController::class, 'noticias'])->name('noticias');
    Route::get('/relatorios-de-atividades', [PageController::class, 'relatorios'])->name('relatorios');
    Route::get('/posts/{post:slug}/download', [PostFileController::class, 'attachment'])->name('posts.files.attachment');
    Route::get('/posts/{slug}', [PageController::class, 'post'])->name('post');
});

// --------------------------------------------------------------------
// Autenticação do painel
// --------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'login']);
});

Route::post('/admin/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

// --------------------------------------------------------------------
// Painel administrativo
// --------------------------------------------------------------------
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('posts', AdminPostController::class)->except(['show', 'update']);
        Route::post('posts/{post}', [AdminPostController::class, 'update'])->name('posts.update');

        Route::resource('transparency-documents', TransparencyDocumentController::class)->except(['show', 'update']);
        Route::post('transparency-documents/{transparency_document}', [TransparencyDocumentController::class, 'update'])
            ->name('transparency-documents.update');

        Route::get('editais', [EditalController::class, 'index'])->name('editais.index');
        Route::get('editais/create', [EditalController::class, 'create'])->name('editais.create');
        Route::post('editais', [EditalController::class, 'store'])->name('editais.store');
        Route::get('editais/{edital}/edit', [EditalController::class, 'edit'])->name('editais.edit');
        Route::post('editais/{edital}', [EditalController::class, 'update'])->name('editais.update');
        Route::delete('editais/{edital}', [EditalController::class, 'destroy'])->name('editais.destroy');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

// --------------------------------------------------------------------
// Módulo Patrimonial OASSAB
// --------------------------------------------------------------------
Route::middleware(['auth', 'patrimonio'])
    ->prefix('patrimonios')
    ->name('patrimonios.')
    ->group(function () {
        Route::redirect('/', '/patrimonios/dashboard');
        Route::get('/dashboard', [PatrimonioDashboardController::class, 'index'])->name('dashboard');

        Route::get('patrimonios/{patrimonio}/qrcodes/data', [PatrimonioController::class, 'qrcodesData'])->name('patrimonios.qrcodes.data');
        Route::post('patrimonios/{patrimonio}/qrcodes/regenerar', [PatrimonioController::class, 'regenerarQrcodes'])->name('patrimonios.qrcodes.regenerar');
        Route::get('patrimonios/{patrimonio}/qrcode', [PatrimonioController::class, 'qrcode'])->name('patrimonios.qrcode');
        Route::post('patrimonios/{patrimonio}', [PatrimonioController::class, 'update'])->name('patrimonios.update');
        Route::resource('patrimonios', PatrimonioController::class)->except(['update']);

        Route::resource('categorias', PatrimonioCategoriaController::class)->except(['show']);

        Route::resource('manutencoes', ManutencaoController::class)
            ->except(['show'])
            ->parameters(['manutencoes' => 'manutencao']);

        Route::resource('orcamentos', OrcamentoController::class)->except(['show']);
        Route::post('orcamentos/{orcamento}/propostas', [OrcamentoController::class, 'storeProposta'])->name('orcamentos.propostas.store');
        Route::delete('orcamentos/{orcamento}/propostas/{proposta}', [OrcamentoController::class, 'destroyProposta'])->name('orcamentos.propostas.destroy');

        Route::get('qr-scanner', [QrScannerController::class, 'index'])->name('qr-scanner');
        Route::post('qr-scanner/buscar', [QrScannerController::class, 'buscar'])->name('qr-scanner.buscar');

        Route::get('relatorios/patrimonios/csv', [PatrimonioRelatorioController::class, 'patrimoniosCsv'])->name('relatorios.patrimonios.csv');
        Route::get('relatorios/patrimonios/pdf', [PatrimonioRelatorioController::class, 'patrimoniosPdf'])->name('relatorios.patrimonios.pdf');
        Route::get('relatorios/orcamentos/csv', [PatrimonioRelatorioController::class, 'orcamentosCsv'])->name('relatorios.orcamentos.csv');
        Route::get('relatorios/orcamentos/pdf', [PatrimonioRelatorioController::class, 'orcamentosPdf'])->name('relatorios.orcamentos.pdf');

        Route::get('logs', [PatrimonioLogController::class, 'index'])->name('logs.index');
        Route::delete('logs', [PatrimonioLogController::class, 'clear'])->name('logs.clear');

        Route::resource('usuarios', PatrimonioUserController::class)->except(['show']);
    });

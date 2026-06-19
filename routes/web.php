<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\EditalController;
use App\Http\Controllers\Admin\TransparencyDocumentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EditalFileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostFileController;
use Illuminate\Support\Facades\Route;

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

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PDOException;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        try {
            $authenticated = Auth::attempt(
                array_merge($credentials, ['is_admin' => true]),
                $remember,
            );
        } catch (QueryException|PDOException $e) {
            Log::error('Falha de conexão/consulta ao autenticar admin', [
                'message' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Não foi possível conectar ao banco de dados. Verifique DB_HOST, DB_USERNAME e DB_PASSWORD no .env (e Remote MySQL no cPanel, se estiver em desenvolvimento local).',
            ]);
        }

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas ou usuário sem permissão de administrador.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

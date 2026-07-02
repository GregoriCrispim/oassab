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
            $authenticated = Auth::attempt($credentials, $remember);
        } catch (QueryException|PDOException $e) {
            Log::error('Falha de conexão/consulta ao autenticar', [
                'message' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Não foi possível conectar ao banco de dados. Verifique DB_HOST, DB_USERNAME e DB_PASSWORD no .env.',
            ]);
        }

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas.',
            ]);
        }

        $user = Auth::user();

        if (! $user->is_admin && ! $user->canAccessPatrimonio()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Usuário sem permissão de acesso ao sistema.',
            ]);
        }

        $request->session()->regenerate();

        $intended = $request->session()->pull('url.intended');

        if ($intended && str_contains($intended, '/patrimonios')) {
            return redirect()->intended(route('patrimonios.dashboard'));
        }

        if ($user->is_admin) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('patrimonios.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

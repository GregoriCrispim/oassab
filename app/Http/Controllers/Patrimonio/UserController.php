<?php

namespace App\Http\Controllers\Patrimonio;

use App\Enums\PatrimonioRole;
use App\Http\Controllers\Concerns\RespondsWithFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patrimonio\StorePatrimonioUserRequest;
use App\Models\User;
use App\Services\Patrimonio\PatrimonioLogService;
use App\Support\PaginationPerPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    use RespondsWithFormModal;
    public function __construct(private readonly PatrimonioLogService $logService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $usuarios = User::query()
            ->where(function ($q) {
                $q->whereNotNull('patrimonio_role')->orWhere('is_admin', true);
            })
            ->orderBy('name')
            ->paginate(PaginationPerPage::resolve($request, 10))
            ->withQueryString();

        return view('patrimonios.usuarios.index', compact('usuarios'));
    }

    public function create(Request $request): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', User::class);

        $usuario = new User;
        $roles = PatrimonioRole::cases();
        $data = compact('usuario', 'roles');

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Novo Usuário', 'patrimonios.usuarios._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.usuarios.index'),
            route('patrimonios.usuarios.create'),
        );
    }

    public function store(StorePatrimonioUserRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('create', User::class);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'patrimonio_role' => $request->input('patrimonio_role'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->logService->registrar('INSERT', 'users', $user->id, "Usuário patrimonial criado: {$user->email}");

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(route('patrimonios.usuarios.index'), 'Usuário criado com sucesso.');
        }

        return redirect()->route('patrimonios.usuarios.index')->with('status', 'Usuário criado com sucesso.');
    }

    public function edit(Request $request, User $usuario): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $usuario);

        $roles = PatrimonioRole::cases();
        $data = compact('usuario', 'roles');

        if ($this->wantsFormModal($request)) {
            return $this->formModalJson('Editar Usuário', 'patrimonios.usuarios._form', $data);
        }

        return $this->formModalRedirect(
            route('patrimonios.usuarios.index'),
            route('patrimonios.usuarios.edit', $usuario),
        );
    }

    public function update(StorePatrimonioUserRequest $request, User $usuario): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $usuario);

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'patrimonio_role' => $request->input('patrimonio_role'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        if (! $usuario->is_admin) {
            $usuario->update($data);
        } else {
            $usuario->update(array_merge($data, ['patrimonio_role' => PatrimonioRole::Admin]));
        }

        $this->logService->registrar('UPDATE', 'users', $usuario->id, "Usuário patrimonial atualizado: {$usuario->email}");

        if ($this->wantsFormModal($request)) {
            return $this->formModalSuccess(route('patrimonios.usuarios.index'), 'Usuário atualizado com sucesso.');
        }

        return redirect()->route('patrimonios.usuarios.index')->with('status', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $this->authorize('delete', $usuario);

        if ($usuario->is_admin) {
            return back()->withErrors(['usuario' => 'Não é possível excluir administradores do CMS.']);
        }

        $email = $usuario->email;
        $usuario->delete();
        $this->logService->registrar('DELETE', 'users', null, "Usuário patrimonial excluído: {$email}");

        return redirect()->route('patrimonios.usuarios.index')->with('status', 'Usuário excluído.');
    }
}

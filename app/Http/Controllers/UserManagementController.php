<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'status'    => ['nullable', 'boolean'],
            'roles'     => ['nullable', 'array'],
            'roles.*'   => ['exists:roles,name'], // Spatie roles por nome
        ]);

        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'], // cast hashed no model
                'status'   => $data['status'] ?? true,
            ]);

            if (!empty($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuário criado com sucesso!');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao criar usuário: ' . $e->getMessage()]);
        }
    }

    public function show(User $user)
    {
        $user->load('roles', 'permissions');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'status'    => ['nullable', 'boolean'],
            'roles'     => ['nullable', 'array'],
            'roles.*'   => ['exists:roles,name'],
        ]);

        try {
            $payload = [
                'name'   => $data['name'],
                'email'  => $data['email'],
                'status' => $data['status'] ?? $user->status,
            ];

            if (!empty($data['password'])) {
                $payload['password'] = $data['password']; // cast hashed no model
            }

            $user->update($payload);

            $user->syncRoles($data['roles'] ?? []);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
        }
    }

    public function destroy(User $user)
    {
        try {
            // Se quiser evitar excluir a si mesmo:
            // if (auth()->id() === $user->id) {
            //     return back()->withErrors(['error' => 'Você não pode excluir seu próprio usuário.']);
            // }

            $user->syncRoles([]);
            $user->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuário excluído com sucesso!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Erro ao excluir usuário: ' . $e->getMessage()]);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserManagementService;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    protected $userService;

    public function __construct(UserManagementService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = User::with(['userAccesses.role'])->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $entities = $this->userService->getAvailableEntities();
        
        return view('admin.users.create', compact('roles', 'entities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'boolean',
            'access' => 'array',
            'access.*.role_id' => 'required|exists:roles,id',
            'access.*.userable_type' => 'required|string',
            'access.*.group_id' => 'required|integer'
        ]);

        try {
            $user = $this->userService->createUser(
                $request->only(['name', 'email', 'password', 'status']),
                $request->input('access', [])
            );

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuário criado com sucesso!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao criar usuário: ' . $e->getMessage()]);
        }
    }

    public function show(User $user)
    {
        $user->load(['userAccesses.role', 'userAccesses.userable']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $user->load(['userAccesses.role']);
        $roles = Role::all();
        $entities = $this->userService->getAvailableEntities();
        
        return view('admin.users.edit', compact('user', 'roles', 'entities'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'boolean',
            'access' => 'array',
            'access.*.role_id' => 'required|exists:roles,id',
            'access.*.userable_type' => 'required|string',
            'access.*.group_id' => 'required|integer'
        ]);

        try {
            // Atualizar dados básicos do usuário
            $userData = $request->only(['name', 'email', 'status']);
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }
            
            $user->update($userData);

            // Atualizar acessos
            if ($request->has('access')) {
                $this->userService->updateUserAccess($user, $request->input('access'));
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuário atualizado com sucesso!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->userAccesses()->delete();
            $user->delete();
            
            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuário excluído com sucesso!');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao excluir usuário: ' . $e->getMessage()]);
        }
    }

    // Método para buscar entidades via AJAX
    public function getEntitiesByType(Request $request)
    {
        $type = $request->input('type');
        
        $entities = [];
        switch ($type) {
            case 'App\Models\Admin':
                $entities = \App\Models\Admin::select('id', 'name')->get();
                break;
            case 'App\Models\Seller':
                $entities = \App\Models\Seller::select('id', 'name')->get();
                break;
            case 'App\Models\Partner':
                $entities = \App\Models\Partner::select('id', 'name')->get();
                break;
        }

        return response()->json($entities);
    }
}
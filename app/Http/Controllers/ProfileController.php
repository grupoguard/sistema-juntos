<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile');
    }

    public function store(Request $request)
    {
        // Validação do nome e das senhas
        $attributes = $request->validate([
            'name' => ['required', 'max:50'],
            'password' => ['nullable', 'min:8', 'confirmed'], // Valida a confirmação
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->name = $attributes['name'];

        // Validação do e-mail apenas para administradores
        if ($user->role === 'admin') {
            $request->validate([
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            ]);
            $user->email = $request->email;
        }

        // Se a senha for fornecida, atualiza-a
        if ($request->filled('password')) {
            $user->password = Hash::make($attributes['password']);
        }

        $user->save();

        return redirect()->route('admin.profile')->with('success', 'Dados atualizados com sucesso');
    }
}

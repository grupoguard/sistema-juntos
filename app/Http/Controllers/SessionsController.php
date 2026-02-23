<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function create()
    {
        return view('session.login-session');
    }

    public function store()
    {
        $attributes = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($attributes)) {
            return back()
                ->withErrors(['email' => 'Email ou senha inválidos.'])
                ->onlyInput('email');
        }

        session()->regenerate();

        $user = Auth::user();

        // Se tiver qualquer role válida do sistema, entra no mesmo dashboard
        if ($user->hasAnyRole(['ADMIN', 'COOP', 'SELLER', 'PARTNER'])) {
            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Bem-vindo!');
        }

        Auth::logout();

        return back()->withErrors([
            'email' => 'Acesso negado. Usuário sem perfil de acesso.'
        ]);
    }

    public function destroy()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login')->with(['success' => 'Você foi deslogado.']);
    }
}
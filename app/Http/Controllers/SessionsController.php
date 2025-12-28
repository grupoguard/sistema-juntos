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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($attributes)) {
            session()->regenerate();
            $user = Auth::user();

            if ($user->hasRole('ADMIN')) {
                return redirect('admin/dashboard')
                    ->with(['success' => 'Bem-vindo, Administrador!']);
            }

            if ($user->hasRole('COOP')) {
                return redirect('coop/dashboard')
                    ->with(['success' => 'Bem-vindo! Gerencie a sua cooperativa aqui.']);
            }

            if ($user->hasRole('SELLER')) {
                return redirect('seller/dashboard')
                    ->with(['success' => 'Bem-vindo, vendedor!']);
            }

            if ($user->hasRole('PARTNER')) {
                return redirect('partner/dashboard')
                    ->with(['success' => 'Bem-vindo, Parceiro!']);
            }

            Auth::logout();
            return back()->withErrors(['email' => 'Acesso negado.']);
        }

        return back()->withErrors(['email' => 'Email ou senha inválidos.']);
    }
    
    public function destroy()
    {
        Auth::logout();

        return redirect('/login')->with(['success'=>'Você foi deslogado.']);
    }
}

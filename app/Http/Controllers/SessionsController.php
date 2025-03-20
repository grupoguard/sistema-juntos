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
            'email'=>'required|email',
            'password'=>'required' 
        ]);

        if(Auth::attempt($attributes))
        {
            session()->regenerate();
            $user = Auth::user();
            
            switch ($user->role_name) {
                case 'ADMIN':
                    return redirect('admin/dashboard')->with(['success' => 'Bem-vindo, Administrador!']);
                case 'COOP':
                    return redirect('coop/dashboard')->with(['success' => 'Bem-vindo! Gerencie a sua cooperativa aqui.']);
                case 'SELLER':
                    return redirect('seller/dashboard')->with(['success' => 'Bem-vindo, vendedor!']);
                case 'PARTNER':
                    return redirect('partner/dashboard')->with(['success' => 'Bem-vindo, Parceiro!']);
                default:
                    Auth::logout();
                    return back()->withErrors(['email' => 'Acesso negado.']);
            }
        }
    
        return back()->withErrors(['email' => 'Email ou senha inválidos.']);
    }
    
    public function destroy()
    {
        Auth::logout();

        return redirect('/login')->with(['success'=>'You\'ve been logged out.']);
    }
}

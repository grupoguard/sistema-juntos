<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Se o usuário está logado, verifica se é admin
                if (Auth::user()->role_name === 'ADMIN') {
                    return redirect()->route('admin.dashboard');
                } else {
                    // Se não é admin, faz logout
                    Auth::logout();
                    return redirect('/login')->with('error', 'Acesso restrito apenas para administradores.');
                }
            }
        }

        return $next($request);
    }
}

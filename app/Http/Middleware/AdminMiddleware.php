<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
public function handle(Request $request, Closure $next): Response
    {
        // Verifica se está autenticado
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Verifica se é admin
        if (Auth::user()->role_name !== 'ADMIN') {
            abort(403, 'Acesso não autorizado. Apenas administradores podem acessar esta área.');
        }

        return $next($request);
    }
    
}

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

        if (!Auth::user()->hasAnyRole(['ADMIN', 'COOP', 'SELLER', 'PARTNER'])) {
            abort(403);
        }

        return $next($request);
    }
    
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AsaasWebhookAuth
{
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) config('services.asaas.webhook_token');
        $received = (string) $request->header('asaas-access-token');

        // Se você ainda não configurou token no Asaas, $expected vai estar vazio:
        // Eu recomendo bloquear mesmo assim, pra não ficar exposto.
        if ($expected === '' || $received !== $expected) {
            return response()->json(['ok' => false], 401);
        }

        return $next($request);
    }
}
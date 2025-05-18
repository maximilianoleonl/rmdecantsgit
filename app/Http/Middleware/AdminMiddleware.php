<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica si el usuario es administrador
        // Deberías añadir un campo 'es_admin' o similar a tu tabla 'usuarios'
        if (!$request->user() || !$request->user()->es_admin) {
            return redirect()->route('home')->with('error', 'No tienes permiso para acceder a esta área.');
        }

        return $next($request);
    }
}

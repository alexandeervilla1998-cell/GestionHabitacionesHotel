<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user();

        if (!in_array($usuario->rol, $roles)) {
            return redirect()->route('home.home')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
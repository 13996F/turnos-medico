<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') !== 'admin') {
            return redirect()->to('/login/admin')->withErrors(['auth' => 'Debes iniciar sesión como Administrador.']);
        }
        return $next($request);
    }
}

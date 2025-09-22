<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDoctor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') !== 'doctor' || !session()->has('doctor_id')) {
            return redirect()->to('/login/medico')->withErrors(['auth' => 'Debes iniciar sesión como Médico.']);
        }
        return $next($request);
    }
}

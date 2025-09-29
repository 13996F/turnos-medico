<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatientAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('patient_id')) {
            return redirect()->route('patient.access')
                ->withErrors(['auth' => 'Necesitás iniciar sesión para solicitar un turno.']);
        }
        return $next($request);
    }
}

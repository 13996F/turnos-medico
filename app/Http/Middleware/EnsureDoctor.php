<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Doctor;

class EnsureDoctor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') !== 'doctor' || !session()->has('doctor_id')) {
            return redirect()->route('doctor.access')->withErrors(['auth' => 'Debes iniciar sesión como Médico.']);
        }
        $doctorId = session('doctor_id');
        if ($doctorId) {
            $doctor = Doctor::find($doctorId);
            if ($doctor && empty($doctor->email_verified_at)) {
                return redirect()->route('verification.doctor.notice')->withErrors(['verify' => 'Debes verificar tu email para acceder.']);
            }
        }
        return $next($request);
    }
}

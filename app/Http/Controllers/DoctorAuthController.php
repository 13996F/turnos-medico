<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class DoctorAuthController extends Controller
{
    public function access()
    {
        // Si hay sesión de paciente, redirigir a su flujo
        if (session()->has('patient_id')) {
            return redirect()->route('appointments.create');
        }
        // Si hay sesión de admin, redirigir al panel de admin
        if (session('role') === 'admin') {
            return redirect()->route('reception.index');
        }
        $specialties = Specialty::orderBy('name')->get(['id','name']);
        return view('doctor.access', compact('specialties'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'specialty_id' => ['required','exists:specialties,id'],
            'email' => ['required','email','max:150','unique:doctors,email'],
            'password' => ['required','string','min:6','confirmed'],
        ]);

        $doctor = Doctor::create([
            'name' => $data['name'],
            'specialty_id' => (int) $data['specialty_id'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'active' => true,
        ]);

        session([
            'role' => 'doctor',
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
        ]);

        return redirect()->route('doctor.index')->with('success', 'Cuenta de médico creada y sesión iniciada.');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $doctor = Doctor::where('email', $data['email'])->where('active', true)->first();
        if (!$doctor || !Hash::check($data['password'], $doctor->password)) {
            return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
        }

        session([
            'role' => 'doctor',
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
        ]);

        return redirect()->route('doctor.index')->with('success', 'Sesión iniciada como Médico.');
    }

    public function googleRedirect(): RedirectResponse
    {
        $redirect = env('DOCTOR_GOOGLE_REDIRECT_URI', route('doctor.google.callback'));
        return Socialite::driver('google')
            ->redirectUrl($redirect)
            ->redirect();
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        try {
            $redirect = env('DOCTOR_GOOGLE_REDIRECT_URI', route('doctor.google.callback'));
            $googleUser = Socialite::driver('google')
                ->redirectUrl($redirect)
                ->user();
            $raw = $googleUser->user ?? [];
            $fullName = $googleUser->getName() ?? '';
            $email = $googleUser->getEmail() ?? '';

            // Si existe el médico, iniciar sesión
            $doctor = Doctor::where('email', $email)->where('active', true)->first();
            if ($doctor) {
                session([
                    'role' => 'doctor',
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->name,
                ]);
                return redirect()->route('doctor.index')->with('success', 'Sesión iniciada con Google.');
            }

            // Si no existe, prellenar registro
            $firstName = $raw['given_name'] ?? '';
            $lastName = $raw['family_name'] ?? '';
            if (!$firstName || !$lastName) {
                $parts = preg_split('/\s+/', trim($fullName));
                $firstName = $firstName ?: ($parts[0] ?? '');
                $lastName = $lastName ?: (count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '');
            }
            session(['doctor_google' => [
                'name' => trim(($firstName.' '.$lastName)) ?: $fullName ?: 'Nuevo Médico',
                'email' => $email,
            ]]);
            return redirect()->route('doctor.access')->with('success', 'Completa tu registro con los datos prellenados.');
        } catch (\Throwable $e) {
            \Log::error('[doctor.google] callback error', ['error' => $e->getMessage()]);
            return redirect()->route('doctor.access')->withErrors(['google' => 'No se pudo autenticar con Google.']);
        }
    }
}

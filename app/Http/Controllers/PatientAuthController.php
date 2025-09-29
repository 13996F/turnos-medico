<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class PatientAuthController extends Controller
{
    public function access(): View
    {
        // Si hay sesión de Admin o Médico, redirigir a su panel correspondiente
        if (session('role') === 'admin') {
            return redirect()->route('reception.index');
        }
        if (session('role') === 'doctor') {
            return redirect()->route('doctor.index');
        }
        return view('patient.access');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $raw = $googleUser->user ?? [];
            $fullName = $googleUser->getName() ?? '';
            $email = $googleUser->getEmail() ?? '';
            $googleId = $googleUser->getId();

            // Intentar separar nombre y apellido de forma simple
            $firstName = $raw['given_name'] ?? '';
            $lastName = $raw['family_name'] ?? '';
            if (!$firstName || !$lastName) {
                $parts = preg_split('/\s+/', trim($fullName));
                $firstName = $firstName ?: ($parts[0] ?? '');
                $lastName = $lastName ?: (count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '');
            }

            session(['patient_google' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'google_id' => $googleId,
            ]]);

            // Crear o encontrar Patient y autenticarlo en sesión
            $patient = Patient::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();
            if (!$patient) {
                $patient = Patient::create([
                    'first_name' => $firstName ?: 'Paciente',
                    'last_name' => $lastName ?: 'Google',
                    'email' => $email ?: null,
                    'google_id' => $googleId,
                ]);
            } else if (!$patient->google_id) {
                $patient->update(['google_id' => $googleId]);
            }

            session(['patient_id' => $patient->id]);

            return redirect()->route('appointments.create')
                ->with('success', 'Datos de Google cargados correctamente.');
        } catch (\Throwable $e) {
            Log::error('[patient.google] callback error', ['error' => $e->getMessage()]);
            return redirect()->route('appointments.create')
                ->withErrors(['google' => 'No se pudo autenticar con Google. Intente nuevamente.']);
        }
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('patient_google');
        $request->session()->forget('patient_id');
        return redirect()->route('appointments.create')->with('success', 'Datos de Google eliminados.');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name'  => ['required','string','max:100'],
            'email'      => ['required','email','max:150','unique:patients,email'],
            'password'   => ['required','string','min:6','confirmed'],
        ]);
        $patient = Patient::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
        ]);
        session(['patient_id' => $patient->id, 'patient_google' => [
            'first_name' => $patient->first_name,
            'last_name'  => $patient->last_name,
            'email'      => $patient->email,
        ]]);
        return redirect()->route('appointments.create')->with('success', 'Registro exitoso.');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);
        $patient = Patient::where('email', $data['email'])->first();
        if (!$patient || !$patient->password || !Hash::check($data['password'], $patient->password)) {
            return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
        }
        session(['patient_id' => $patient->id, 'patient_google' => [
            'first_name' => $patient->first_name,
            'last_name'  => $patient->last_name,
            'email'      => $patient->email,
        ]]);
        return redirect()->route('appointments.create')->with('success', 'Sesión iniciada.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('patient_id');
        // Mantener patient_google opcionalmente para prefill; por ahora lo eliminamos
        $request->session()->forget('patient_google');
        return redirect()->route('patient.access')->with('success', 'Sesión cerrada.');
    }
}

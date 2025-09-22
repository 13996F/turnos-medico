<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(string $role): View
    {
        abort_unless(in_array($role, ['admin', 'medico']), 404);
        $doctors = collect();
        if ($role === 'medico') {
            $doctors = Doctor::where('active', true)->orderBy('name')->get(['id','name']);
        }
        return view('auth.login', compact('role', 'doctors'));
    }

    public function login(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, ['admin', 'medico']), 404);

        if ($role === 'admin') {
            $request->validate(['password' => ['required','string']]);
            $pass = $request->input('password');
            $expected = (string) env('ADMIN_PASS', 'admin123');
            if ($pass !== $expected) {
                return back()->withErrors(['password' => 'Clave incorrecta'])->withInput();
            }
            session(['role' => 'admin']);
            return redirect()->route('reception.index')->with('success', 'Sesión iniciada como Administrador.');
        }

        // Médico
        $request->validate([
            'password' => ['required','string'],
            'doctor_id' => ['required','exists:doctors,id'],
        ]);
        $pass = $request->input('password');
        $expected = (string) env('DOCTOR_PASS', 'doctor123');
        if ($pass !== $expected) {
            return back()->withErrors(['password' => 'Clave incorrecta'])->withInput();
        }
        session(['role' => 'doctor', 'doctor_id' => (int) $request->doctor_id]);
        return redirect()->route('doctor.index')->with('success', 'Sesión iniciada como Médico.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/paciente')->with('success', 'Sesión cerrada.');
    }
}

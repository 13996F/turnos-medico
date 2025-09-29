<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientPasswordController extends Controller
{
    public function showForgot(): View
    {
        return view('patient.forgot');
    }

    public function sendReset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required','email'],
        ]);

        $email = $data['email'];
        $patient = Patient::where('email', $email)->first();

        // Siempre responder éxito para no filtrar existencia
        if ($patient) {
            $token = Str::random(64);
            DB::table('patient_password_resets')->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($token), 'created_at' => Carbon::now()]
            );

            $resetUrl = route('patient.password.reset', ['token' => $token, 'email' => $email]);
            // En un entorno real se enviaría un email.
            // Por ahora, lo registramos en logs para desarrollo local.
            Log::info('[patient.password] Reset link generated', ['email' => $email, 'url' => $resetUrl]);
            if (config('app.env') === 'local') {
                // Mostrar en sesión para facilitar pruebas locales
                session()->flash('success', 'Enlace de restablecimiento (solo dev): '.$resetUrl);
            }
        }

        return back()->with('success', 'Si el email existe, te enviaremos un enlace para restablecer la contraseña.');
    }

    public function showReset(Request $request): View|RedirectResponse
    {
        $email = $request->query('email');
        $token = $request->route('token');
        if (!$email || !$token) {
            return redirect()->route('patient.forgot')->withErrors(['email' => 'Enlace inválido.']);
        }
        return view('patient.reset', compact('email', 'token'));
    }

    public function performReset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'token' => ['required','string'],
            'password' => ['required','string','min:6','confirmed'],
        ]);

        $record = DB::table('patient_password_resets')->where('email', $data['email'])->first();
        if (!$record || !Hash::check($data['token'], $record->token)) {
            return back()->withErrors(['email' => 'El token es inválido o expiró.'])->withInput();
        }

        // Opcional: validar expiración por tiempo (ej. 60 minutos)
        if ($record->created_at && Carbon::parse($record->created_at)->lt(Carbon::now()->subHours(2))) {
            return back()->withErrors(['email' => 'El enlace expiró. Volvé a solicitarlo.'])->withInput();
        }

        $patient = Patient::where('email', $data['email'])->first();
        if (!$patient) {
            return back()->withErrors(['email' => 'No existe un paciente con ese email.'])->withInput();
        }

        $patient->update(['password' => Hash::make($data['password'])]);
        DB::table('patient_password_resets')->where('email', $data['email'])->delete();

        return redirect()->route('patient.access')->with('success', 'Contraseña actualizada. Ahora podés iniciar sesión.');
    }
}

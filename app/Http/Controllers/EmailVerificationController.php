<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function adminNotice()
    {
        return view('admin.verify_notice');
    }

    public function doctorNotice()
    {
        return view('doctor.verify_notice');
    }

    public function sendAdmin(Request $request): RedirectResponse
    {
        $adminId = session('admin_id');
        if (!$adminId) {
            return redirect()->route('admin.access');
        }
        $admin = Admin::find($adminId);
        if (!$admin || empty($admin->email)) {
            return back()->withErrors(['email' => 'No hay un email válido para enviar verificación.']);
        }
        if ($admin->email_verified_at) {
            return back()->with('success', 'Tu email ya está verificado.');
        }

        $url = URL::temporarySignedRoute(
            'verification.admin.verify', now()->addMinutes(60),
            ['id' => $admin->id, 'hash' => sha1(Str::lower($admin->email))]
        );

        // Enviar email simple con el enlace
        Mail::raw("Verificá tu email ingresando al siguiente enlace:\n\n{$url}\n\nEste enlace expira en 60 minutos.", function ($message) use ($admin) {
            $message->to($admin->email)
                ->subject('Verificá tu email - Centro Médico del Milagro');
        });

        return back()->with('success', 'Te enviamos un email con el enlace de verificación.');
    }

    public function sendDoctor(Request $request): RedirectResponse
    {
        $doctorId = session('doctor_id');
        if (!$doctorId) {
            return redirect()->route('doctor.access');
        }
        $doctor = Doctor::find($doctorId);
        if (!$doctor || empty($doctor->email)) {
            return back()->withErrors(['email' => 'No hay un email válido para enviar verificación.']);
        }
        if ($doctor->email_verified_at) {
            return back()->with('success', 'Tu email ya está verificado.');
        }

        $url = URL::temporarySignedRoute(
            'verification.doctor.verify', now()->addMinutes(60),
            ['id' => $doctor->id, 'hash' => sha1(Str::lower($doctor->email))]
        );

        Mail::raw("Verificá tu email ingresando al siguiente enlace:\n\n{$url}\n\nEste enlace expira en 60 minutos.", function ($message) use ($doctor) {
            $message->to($doctor->email)
                ->subject('Verificá tu email - Centro Médico del Milagro');
        });

        return back()->with('success', 'Te enviamos un email con el enlace de verificación.');
    }

    public function verifyAdmin(Request $request, int $id, string $hash): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('admin.access')->withErrors(['verify' => 'El enlace no es válido o expiró.']);
        }
        $admin = Admin::find($id);
        if (!$admin) {
            return redirect()->route('admin.access')->withErrors(['verify' => 'Usuario no encontrado.']);
        }
        if (!hash_equals($hash, sha1(Str::lower((string) $admin->email)))) {
            return redirect()->route('admin.access')->withErrors(['verify' => 'Hash inválido.']);
        }
        if (!$admin->email_verified_at) {
            $admin->forceFill(['email_verified_at' => now()])->save();
        }
        return redirect()->route('reception.index')->with('success', 'Email verificado.');
    }

    public function verifyDoctor(Request $request, int $id, string $hash): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('doctor.access')->withErrors(['verify' => 'El enlace no es válido o expiró.']);
        }
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return redirect()->route('doctor.access')->withErrors(['verify' => 'Usuario no encontrado.']);
        }
        if (!hash_equals($hash, sha1(Str::lower((string) $doctor->email)))) {
            return redirect()->route('doctor.access')->withErrors(['verify' => 'Hash inválido.']);
        }
        if (!$doctor->email_verified_at) {
            $doctor->forceFill(['email_verified_at' => now()])->save();
        }
        return redirect()->route('doctor.index')->with('success', 'Email verificado.');
    }
}

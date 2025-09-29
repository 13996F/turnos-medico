<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AdminAuthController extends Controller
{
    public function access(): View
    {
        return view('admin.access');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name'  => ['required','string','max:100'],
            'username'   => ['nullable','string','max:100','unique:admins,username'],
            'email'      => ['required','email','max:150','unique:admins,email'],
            'password'   => ['required','string','min:6','confirmed'],
        ]);
        // Autogenerar username si no fue provisto
        $username = $data['username'] ?? null;
        if (!$username) {
            $base = $data['email'] ? strstr($data['email'], '@', true) : null;
            if (!$base || strlen($base) < 3) {
                $base = trim(($data['first_name'].' '.$data['last_name'])) ?: 'admin';
            }
            $base = Str::slug(Str::lower($base), '_');
            $candidate = $base;
            $i = 1;
            while (Admin::where('username', $candidate)->exists()) {
                $candidate = $base.'_'.(++$i);
            }
            $username = $candidate;
        }

        $admin = Admin::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'username'   => $username,
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
        ]);
        session(['role' => 'admin', 'admin_id' => $admin->id, 'admin' => [
            'first_name' => $admin->first_name,
            'last_name'  => $admin->last_name,
            'email'      => $admin->email,
            'username'   => $admin->username,
        ]]);
        // Enviar email de verificación
        $url = URL::temporarySignedRoute(
            'verification.admin.verify', now()->addMinutes(60),
            ['id' => $admin->id, 'hash' => sha1(Str::lower($admin->email))]
        );
        Mail::raw("Verificá tu email ingresando al siguiente enlace:\n\n{$url}\n\nEste enlace expira en 60 minutos.", function ($message) use ($admin) {
            $message->to($admin->email)->subject('Verificá tu email - Centro Médico del Milagro');
        });
        return redirect()->route('verification.admin.notice')->with('success', 'Te enviamos un email para verificar tu cuenta.');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login'    => ['required','string'], // username o email
            'password' => ['required','string'],
        ]);
        $login = $data['login'];
        $admin = Admin::where('email', $login)
            ->orWhere('username', $login)
            ->first();
        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return back()->withErrors(['login' => 'Credenciales inválidas'])->withInput();
        }
        session(['role' => 'admin', 'admin_id' => $admin->id, 'admin' => [
            'first_name' => $admin->first_name,
            'last_name'  => $admin->last_name,
            'email'      => $admin->email,
            'username'   => $admin->username,
        ]]);
        if (empty($admin->email_verified_at)) {
            return redirect()->route('verification.admin.notice')->withErrors(['verify' => 'Debes verificar tu email para acceder al panel.']);
        }
        return redirect()->route('reception.index')->with('success', 'Sesión iniciada como Administrador.');
    }

    public function googleRedirect(): RedirectResponse
    {
        $redirect = env('ADMIN_GOOGLE_REDIRECT_URI', route('admin.google.callback'));
        return Socialite::driver('google')
            ->redirectUrl($redirect)
            ->redirect();
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        try {
            $redirect = env('ADMIN_GOOGLE_REDIRECT_URI', route('admin.google.callback'));
            $googleUser = Socialite::driver('google')
                ->redirectUrl($redirect)
                ->user();
            $raw = $googleUser->user ?? [];
            $fullName = $googleUser->getName() ?? '';
            $email = $googleUser->getEmail() ?? '';

            // Separar nombre/apellido
            $firstName = $raw['given_name'] ?? '';
            $lastName = $raw['family_name'] ?? '';
            if (!$firstName || !$lastName) {
                $parts = preg_split('/\s+/', trim($fullName));
                $firstName = $firstName ?: ($parts[0] ?? '');
                $lastName = $lastName ?: (count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '');
            }

            // Validación opcional de dominio (seguridad extra)
            $allowedDomain = env('ADMIN_GOOGLE_DOMAIN');
            if ($allowedDomain && $email && !str_ends_with(Str::lower($email), '@'.Str::lower($allowedDomain))) {
                return redirect()->route('admin.access')->withErrors(['google' => 'Tu dominio de email no está autorizado.']);
            }

            $admin = Admin::where('email', $email)->first();
            if (!$admin) {
                // Generar username único basado en email o nombre
                $base = $email ? strstr($email, '@', true) : null;
                if (!$base || strlen($base) < 3) {
                    $base = trim(($firstName.' '.$lastName)) ?: 'admin';
                }
                $base = Str::slug(Str::lower($base), '_');
                $candidate = $base;
                $i = 1;
                while (Admin::where('username', $candidate)->exists()) {
                    $candidate = $base.'_'.(++$i);
                }
                $username = $candidate;

                $admin = Admin::create([
                    'first_name' => $firstName ?: 'Admin',
                    'last_name'  => $lastName ?: '',
                    'username'   => $username,
                    'email'      => $email,
                    // contraseña aleatoria, no usada en Google login
                    'password'   => Hash::make(Str::random(16)),
                ]);
            }

            session(['role' => 'admin', 'admin_id' => $admin->id, 'admin' => [
                'first_name' => $admin->first_name,
                'last_name'  => $admin->last_name,
                'email'      => $admin->email,
                'username'   => $admin->username,
            ]]);
            return redirect()->route('reception.index')->with('success', 'Sesión iniciada con Google.');
        } catch (\Throwable $e) {
            \Log::error('[admin.google] callback error', ['error' => $e->getMessage()]);
            return redirect()->route('admin.access')->withErrors(['google' => 'No se pudo autenticar con Google.']);
        }
    }
}

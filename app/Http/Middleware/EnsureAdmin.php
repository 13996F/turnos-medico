<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') !== 'admin') {
            return redirect()->to('/login/admin')->withErrors(['auth' => 'Debes iniciar sesión como Administrador.']);
        }
        $adminId = session('admin_id');
        if ($adminId) {
            $admin = Admin::find($adminId);
            if ($admin && empty($admin->email_verified_at)) {
                return redirect()->route('verification.admin.notice')->withErrors(['verify' => 'Debes verificar tu email para acceder.']);
            }
        }
        return $next($request);
    }
}

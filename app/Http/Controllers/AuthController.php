<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function logout(Request $request): RedirectResponse
    {
        $role = $request->session()->get('role');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($role === 'admin') {
            return redirect()->route('admin.access')->with('success', 'Sesión cerrada.');
        }
        if ($role === 'doctor') {
            return redirect()->route('doctor.access')->with('success', 'Sesión cerrada.');
        }
        return redirect('/')->with('success', 'Sesión cerrada.');
    }
}

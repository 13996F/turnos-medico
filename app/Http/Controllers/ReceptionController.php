<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceptionController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->query('date', now()->toDateString());

        $appointments = Appointment::with(['doctor.specialty'])
            ->whereDate('scheduled_at', $date)
            ->orderBy('scheduled_at')
            ->get();

        return view('reception.index', compact('appointments', 'date'));
    }

    public function markArrived(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'arrived']);
        return back()->with('success', 'Asistencia confirmada.');
    }

    public function markPaid(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'paid']);
        return back()->with('success', 'Pago confirmado.');
    }
}

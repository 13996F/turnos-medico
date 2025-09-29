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

    public function edit(Appointment $appointment): View
    {
        return view('reception.edit', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required','date'],
            'time' => ['required'],
        ]);

        $scheduledAt = date('Y-m-d H:i:s', strtotime($data['date'].' '.$data['time']));

        // Evitar solapamiento con otros turnos del mismo médico
        $exists = Appointment::where('doctor_id', $appointment->doctor_id)
            ->where('scheduled_at', $scheduledAt)
            ->where('id', '!=', $appointment->id)
            ->exists();
        if ($exists) {
            return back()
                ->withErrors(['time' => 'Ya existe un turno para ese médico en el horario seleccionado.'])
                ->withInput();
        }

        $appointment->update([
            'scheduled_at' => $scheduledAt,
        ]);

        return redirect()->route('reception.index', ['date' => date('Y-m-d', strtotime($scheduledAt))])
            ->with('success', 'Turno actualizado.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->delete();
        return back()->with('success', 'Turno eliminado.');
    }
}

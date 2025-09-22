<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function create(): View
    {
        $specialties = Specialty::orderBy('name')->get();
        return view('appointments.create', compact('specialties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_first_name' => ['required','string','max:100'],
            'patient_last_name'  => ['required','string','max:100'],
            'phone'              => ['required','string','max:50'],
            'dni'                => ['required','string','max:20'],
            'specialty_id'       => ['required','exists:specialties,id'],
            'doctor_id'          => ['required','exists:doctors,id'],
            'date'               => ['required','date'],
            'time'               => ['required'],
        ]);

        $scheduledAt = date('Y-m-d H:i:s', strtotime($data['date'].' '.$data['time']));

        // Validación: evitar turnos superpuestos por médico en la misma fecha y hora
        $exists = Appointment::where('doctor_id', $data['doctor_id'])
            ->where('scheduled_at', $scheduledAt)
            ->exists();
        if ($exists) {
            return back()
                ->withErrors(['time' => 'Ya existe un turno para ese médico en el horario seleccionado.'])
                ->withInput();
        }

        // Crear el turno y manejar posible conflicto por índice único a nivel BD
        try {
            Appointment::create([
                'patient_first_name' => $data['patient_first_name'],
                'patient_last_name'  => $data['patient_last_name'],
                'phone'              => $data['phone'],
                'dni'                => $data['dni'],
                'specialty_id'       => $data['specialty_id'],
                'doctor_id'          => $data['doctor_id'],
                'scheduled_at'       => $scheduledAt,
                'status'             => 'requested',
            ]);
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['time' => 'No se pudo crear el turno: el horario ya fue reservado.'])
                ->withInput();
        }

        return redirect()->route('appointments.create')
            ->with('success', 'Turno solicitado con éxito. Preséntate en recepción el día y horario elegido.');
    }

    public function doctorsBySpecialty(Specialty $specialty)
    {
        return response()->json(
            Doctor::where('specialty_id', $specialty->id)
                ->where('active', true)
                ->orderBy('name')
                ->get(['id','name'])
        );
    }
}

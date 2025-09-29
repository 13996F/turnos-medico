<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function create(): View
    {
        $specialties = Specialty::orderBy('name')->get();
        $patient = session('patient_google', null);
        $patientModel = null;
        if ($patient && (isset($patient['google_id']) || isset($patient['email']))) {
            $query = Patient::query();
            if (!empty($patient['google_id'])) {
                $query->orWhere('google_id', $patient['google_id']);
            }
            if (!empty($patient['email'])) {
                $query->orWhere('email', $patient['email']);
            }
            $patientModel = $query->first();
        }
        return view('appointments.create', compact('specialties', 'patient', 'patientModel'));
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
            'time'               => ['required','date_format:H:i'],
            'has_insurance'      => ['nullable','boolean'],
            'insurance_name'     => ['nullable','string','max:150'],
        ]);

        // Normalizar booleano de obra social y validar dependencia
        $hasInsurance = (bool) ($data['has_insurance'] ?? false);
        if ($hasInsurance && empty($data['insurance_name'])) {
            return back()
                ->withErrors(['insurance_name' => 'Debe indicar la obra social.'])
                ->withInput();
        }

        $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'])
            ->seconds(0);
        $doctorId = (int) $data['doctor_id'];

        // Pre-chequeo desactivado temporalmente para diagnosticar errores de colisión.
        Log::info('[appointments.store] scheduledAt (no precheck)', [
            'doctor_id' => $doctorId,
            'date' => $data['date'],
            'time' => $data['time'],
            'scheduled_at' => $scheduledAt->toDateTimeString(),
        ]);

        // Crear/actualizar paciente si viene de Google
        $patientId = null;
        $sessionPatient = session('patient_google');
        if ($sessionPatient && (isset($sessionPatient['google_id']) || isset($sessionPatient['email']))) {
            $query = Patient::query();
            if (!empty($sessionPatient['google_id'])) {
                $query->orWhere('google_id', $sessionPatient['google_id']);
            }
            if (!empty($sessionPatient['email'])) {
                $query->orWhere('email', $sessionPatient['email']);
            }
            $patient = $query->first();
            if (!$patient) {
                $patient = Patient::create([
                    'first_name' => $sessionPatient['first_name'] ?? $data['patient_first_name'],
                    'last_name'  => $sessionPatient['last_name'] ?? $data['patient_last_name'],
                    'email'      => $sessionPatient['email'] ?? null,
                    'google_id'  => $sessionPatient['google_id'] ?? null,
                    'phone'      => $data['phone'] ?? null,
                    'dni'        => $data['dni'] ?? null,
                ]);
            } else {
                // actualizar datos básicos si vienen completos
                $patient->update(array_filter([
                    'first_name' => $sessionPatient['first_name'] ?? null,
                    'last_name'  => $sessionPatient['last_name'] ?? null,
                    'email'      => $sessionPatient['email'] ?? null,
                    'google_id'  => $sessionPatient['google_id'] ?? null,
                    'phone'      => $data['phone'] ?? null,
                    'dni'        => $data['dni'] ?? null,
                ], fn($v) => !is_null($v)));
            }
            $patientId = $patient->id ?? null;
        }

        // Crear el turno y manejar posible conflicto por índice único a nivel BD
        try {
            Appointment::create([
                'patient_id'         => $patientId,
                'patient_first_name' => $data['patient_first_name'],
                'patient_last_name'  => $data['patient_last_name'],
                'phone'              => $data['phone'],
                'dni'                => $data['dni'],
                'specialty_id'       => $data['specialty_id'],
                'doctor_id'          => $doctorId,
                'scheduled_at'       => $scheduledAt,
                'status'             => 'requested',
                'has_insurance'      => $hasInsurance,
                'insurance_name'     => $hasInsurance ? $data['insurance_name'] : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('[appointments.store] error creating appointment', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            $message = $e->getMessage();
            $isUnique = ($e->getCode() == 23000) || str_contains($message, 'appointments_doctor_time_unique');
            return back()
                ->withErrors([
                    'time' => $isUnique
                        ? 'No se pudo crear el turno: el horario ya fue reservado.'
                        : 'No se pudo crear el turno. Por favor, intente nuevamente.'
                ])
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

    public function occupiedTimes(Doctor $doctor, Request $request)
    {
        $date = $request->query('date');
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json([], 200);
        }
        $occupied = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', $date)
            ->orderBy('scheduled_at')
            ->pluck('scheduled_at')
            ->map(fn($dt) => Carbon::parse($dt)->format('H:i'))
            ->unique()
            ->values();
        return response()->json($occupied);
    }
}

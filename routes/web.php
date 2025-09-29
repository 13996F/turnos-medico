<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientAuthController;
use App\Http\Controllers\PatientPasswordController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DoctorAuthController;

// Pantalla de inicio
Route::get('/', function () {
    return view('home');
});


// Paciente: acceso con login normal y Google (sin recuperación de contraseña)
Route::get('/acceso', [PatientAuthController::class, 'access'])->name('patient.access');
Route::post('/acceso/registro', [PatientAuthController::class, 'register'])->name('patient.register')->middleware('throttle:5,1');
Route::post('/acceso/login', [PatientAuthController::class, 'login'])->name('patient.login')->middleware('throttle:5,1');
Route::post('/acceso/logout', [PatientAuthController::class, 'logout'])->name('patient.logout');
// Google para Paciente
Route::get('/auth/google', [PatientAuthController::class, 'redirectToGoogle'])->name('patient.google.redirect');
Route::get('/auth/google/callback', [PatientAuthController::class, 'handleGoogleCallback'])->name('patient.google.callback');
Route::post('/auth/google/clear', [PatientAuthController::class, 'clear'])->name('patient.google.clear');
// Olvido deshabilitado
// Route::get('/acceso/olvido', [PatientPasswordController::class, 'showForgot'])->name('patient.forgot');
// Route::post('/acceso/olvido', [PatientPasswordController::class, 'sendReset'])->name('patient.forgot.send');
// Route::get('/acceso/restablecer/{token}', [PatientPasswordController::class, 'showReset'])->name('patient.password.reset');
// Route::post('/acceso/restablecer', [PatientPasswordController::class, 'performReset'])->name('patient.password.perform');

Route::middleware('patient')->group(function () {
    Route::get('/paciente', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/paciente/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
});
// Route::get('/auth/google', [PatientAuthController::class, 'redirectToGoogle'])->name('patient.google.redirect');
// Route::get('/auth/google/callback', [PatientAuthController::class, 'handleGoogleCallback'])->name('patient.google.callback');
// Route::post('/auth/google/clear', [PatientAuthController::class, 'clear'])->name('patient.google.clear');

// Small JSON endpoint for dependent doctor dropdown
Route::get('/api/specialties/{specialty}/doctors', [AppointmentController::class, 'doctorsBySpecialty'])
    ->name('api.specialties.doctors');
// JSON endpoint: occupied times for doctor by date
Route::get('/api/doctors/{doctor}/occupied', [AppointmentController::class, 'occupiedTimes'])
    ->name('api.doctors.occupied');

// Auth (login/logout) para admin y médico
Route::get('/login/admin', function () { return redirect()->route('admin.access'); })->name('login.admin');
Route::get('/login/medico', function () { return redirect()->route('doctor.access'); })->name('login.medico');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin con usuarios propios (similar a Paciente)
Route::prefix('/admin')->group(function () {
    Route::get('/acceso', [AdminAuthController::class, 'access'])->name('admin.access');
    Route::post('/registro', [AdminAuthController::class, 'register'])->name('admin.register')->middleware('throttle:5,1');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login')->middleware('throttle:5,1');
    // Google para Admin
    Route::get('/google', [AdminAuthController::class, 'googleRedirect'])->name('admin.google.redirect');
    Route::get('/google/callback', [AdminAuthController::class, 'googleCallback'])->name('admin.google.callback');
});

// Administrador/Recepción: listar por fecha, marcar llegada y pago
Route::prefix('/admin')->middleware('admin')->group(function () {
    Route::get('/', [ReceptionController::class, 'index'])->name('reception.index');
    Route::post('/appointments/{appointment}/arrived', [ReceptionController::class, 'markArrived'])->name('reception.arrived');
    Route::post('/appointments/{appointment}/paid', [ReceptionController::class, 'markPaid'])->name('reception.paid');
    Route::get('/appointments/{appointment}/edit', [ReceptionController::class, 'edit'])->name('reception.edit');
    Route::put('/appointments/{appointment}', [ReceptionController::class, 'update'])->name('reception.update');
    Route::delete('/appointments/{appointment}', [ReceptionController::class, 'destroy'])->name('reception.destroy');
});

// Médico: ver pacientes del día (arrived/paid)
Route::prefix('/medico')->middleware('doctor')->group(function () {
    Route::get('/', [DoctorController::class, 'index'])->name('doctor.index');
});

// Médico: acceso/registro/login
Route::prefix('/medico')->group(function () {
    Route::get('/acceso', [DoctorAuthController::class, 'access'])->name('doctor.access');
    Route::post('/registro', [DoctorAuthController::class, 'register'])->name('doctor.register')->middleware('throttle:5,1');
    Route::post('/login', [DoctorAuthController::class, 'login'])->name('doctor.login')->middleware('throttle:5,1');
    // Google para Médico
    Route::get('/google', [DoctorAuthController::class, 'googleRedirect'])->name('doctor.google.redirect');
    Route::get('/google/callback', [DoctorAuthController::class, 'googleCallback'])->name('doctor.google.callback');
});


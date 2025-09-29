@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12 col-lg-6 mx-auto">
    <div class="card">
      <div class="card-header">
        Editar turno
      </div>
      <div class="card-body">
        <div class="mb-3">
          <div class="small text-muted">Paciente</div>
          <div class="fw-semibold">{{ $appointment->patient_last_name }}, {{ $appointment->patient_first_name }}</div>
          <div class="text-muted">DNI: {{ $appointment->dni }} • Tel: {{ $appointment->phone }}</div>
          <div class="text-muted">{{ $appointment->doctor->specialty->name }} • {{ $appointment->doctor->name }}</div>
          <div class="text-muted">Obra social: {{ $appointment->has_insurance ? ($appointment->insurance_name ?: 'Con cobertura') : 'Sin obra social' }}</div>
        </div>
        <form method="POST" action="{{ route('reception.update', $appointment) }}">
          @csrf
          @method('PUT')
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Día</label>
              <input type="date" name="date" class="form-control" value="{{ old('date', $appointment->scheduled_at->toDateString()) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Hora</label>
              <input type="time" name="time" class="form-control" value="{{ old('time', $appointment->scheduled_at->format('H:i')) }}" required>
            </div>
          </div>
          <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
            <a href="{{ route('reception.index', ['date' => $appointment->scheduled_at->toDateString()]) }}" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

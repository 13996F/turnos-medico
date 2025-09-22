@extends('layouts.app')

@section('content')
<div class="row g-4">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title">Paciente</h5>
        <p class="card-text">Solicitá tu turno de manera rápida y sencilla.</p>
        <a href="/paciente" class="btn btn-primary mt-auto">Ir a Paciente</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title">Administrador</h5>
        <p class="card-text">Confirmá asistencia y pago de los pacientes.</p>
        @if(session('role')==='admin')
          <a href="/admin" class="btn btn-success mt-auto">Ir al Panel</a>
        @else
          <a href="/login/admin" class="btn btn-outline-primary mt-auto">Iniciar sesión</a>
        @endif
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title">Médico</h5>
        <p class="card-text">Visualizá tus pacientes del día.</p>
        @if(session('role')==='doctor')
          <a href="/medico" class="btn btn-success mt-auto">Ir al Panel</a>
        @else
          <a href="/login/medico" class="btn btn-outline-primary mt-auto">Iniciar sesión</a>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

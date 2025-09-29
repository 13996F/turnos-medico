@extends('layouts.app')

@section('content')
<section class="py-5 text-center">
  <div class="py-5">
    <h1 class="display-5 fw-bold">Solicitá tu turno médico</h1>
    <p class="col-lg-6 mx-auto fs-5 text-muted mt-3">
      Gestioná tu turno de manera simple y rápida. Sin distracciones.
    </p>
    <div class="d-inline-flex gap-2 mt-4">
      <a href="/paciente" class="btn btn-primary btn-lg px-4">Comenzar</a>
    </div>
  </div>
  <div class="text-muted mt-4 small">
    Centro Médico del Milagro
  </div>
  @if(session('role')==='admin')
    <div class="mt-3">
      <a class="btn btn-outline-success btn-sm" href="/admin">Ir al panel de Administración</a>
    </div>
  @elseif(session('role')==='doctor')
    <div class="mt-3">
      <a class="btn btn-outline-success btn-sm" href="/medico">Ir al panel del Médico</a>
    </div>
  @endif
</section>
@endsection

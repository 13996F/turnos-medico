@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12 col-lg-8 mx-auto">
    @if(session('patient_id'))
      <div class="alert alert-warning d-flex justify-content-between align-items-center">
        <div>
          Estás logueado como Paciente. Si llegaste aquí por error, podés volver a la sección de Paciente.
        </div>
        <div>
          <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-outline-primary">Volver a Paciente</a>
        </div>
      </div>
    @endif
    @if(session('role')==='doctor')
      <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
          Ya iniciaste sesión como <strong>Dr./Dra. {{ session('doctor_name') }}</strong>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('doctor.index') }}" class="btn btn-sm btn-primary">Ir a mi agenda</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm btn-outline-secondary" type="submit">Cerrar sesión</button>
          </form>
        </div>
      </div>
    @endif

    <div class="card shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span>Acceso de Médico</span>
      </div>
      <div class="card-body">
        <ul class="nav nav-tabs" id="doctorAccessTabs" role="tablist"
            data-active="{{ old('form')==='register' ? 'register' : (old('form')==='login' ? 'login' : ($errors->has('name') || $errors->has('password_confirmation') ? 'register' : 'login')) }}">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="doctor-login-tab" data-bs-toggle="tab" data-bs-target="#doctor-login-pane" type="button" role="tab" aria-controls="doctor-login-pane" aria-selected="true">Iniciar sesión</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="doctor-register-tab" data-bs-toggle="tab" data-bs-target="#doctor-register-pane" type="button" role="tab" aria-controls="doctor-register-pane" aria-selected="false">Registrarme</button>
          </li>
        </ul>
        <div class="tab-content pt-3">
          <div class="tab-pane fade show active" id="doctor-login-pane" role="tabpanel" aria-labelledby="doctor-login-tab" tabindex="0">
            <form method="POST" action="{{ route('doctor.login') }}" class="row g-3">
              @csrf
              <input type="hidden" name="form" value="login">
              <div class="col-12">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
              </div>
              <div class="col-12">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="col-12 d-grid d-md-flex gap-2">
                <button class="btn btn-primary" type="submit">Ingresar</button>
                <a href="/" class="btn btn-outline-secondary">Volver al inicio</a>
              </div>
              <div class="col-12 text-end">
                <a href="{{ route('doctor.google.redirect') }}" class="btn btn-outline-danger">
                  <i class="bi bi-google me-1"></i> Continuar con Google
                </a>
              </div>
            </form>
          </div>
          <div class="tab-pane fade" id="doctor-register-pane" role="tabpanel" aria-labelledby="doctor-register-tab" tabindex="0">
            <form method="POST" action="{{ route('doctor.register') }}" class="row g-3">
              @csrf
              <input type="hidden" name="form" value="register">
              <div class="col-md-8">
                <label class="form-label">Nombre y Apellido</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Ej: Dra. Ana Pérez" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Especialidad</label>
                <select name="specialty_id" class="form-select" required>
                  <option value="">Seleccioná...</option>
                  @foreach($specialties as $s)
                    <option value="{{ $s->id }}" @selected(old('specialty_id')==$s->id)>{{ $s->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required>
              </div>
              <div class="col-12 d-grid d-md-flex gap-2">
                <button class="btn btn-success" type="submit">Crear cuenta</button>
                <a href="/" class="btn btn-outline-secondary">Volver al inicio</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const tabsEl = document.getElementById('doctorAccessTabs');
    if (!tabsEl) return;
    const desired = tabsEl.getAttribute('data-active');
    if (desired === 'register') {
      const trigger = document.querySelector('#doctor-register-tab');
      if (trigger && window.bootstrap) {
        const tab = new window.bootstrap.Tab(trigger);
        tab.show();
      } else if (trigger) {
        trigger.click();
      }
    }
  });
</script>
@endpush

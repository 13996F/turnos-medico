@extends('layouts.app')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
  <div class="col-12 col-md-6 col-lg-4">
    @if(session('role')==='doctor')
      <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
          Ya iniciaste sesión como <strong>Médico</strong> (ID: {{ session('doctor_id') }}).
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('doctor.index') }}" class="btn btn-sm btn-primary">Ir al panel</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm btn-outline-secondary" type="submit">Cerrar sesión</button>
          </form>
        </div>
      </div>
    @endif
    <div class="text-center mb-3 text-primary">
      <i class="bi bi-stethoscope" style="font-size: 3rem;"></i>
    </div>
    <div class="card shadow-sm">
      <div class="card-header bg-white">
        <h5 class="mb-0">Iniciar sesión — Médico</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('login.perform', 'medico') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Médico</label>
            <select name="doctor_id" class="form-select" required>
              <option value="">Seleccione...</option>
              @foreach($doctors as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Clave</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            <div class="form-text">La clave se toma de la variable <code>DOCTOR_PASS</code> en tu archivo <code>.env</code> (por defecto: <code>doctor123</code>).</div>
          </div>
          <div class="d-grid gap-2">
            <button class="btn btn-primary" type="submit">
              <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
            </button>
            <a href="/" class="btn btn-link">Volver al inicio</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

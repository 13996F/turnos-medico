@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12 col-lg-6 mx-auto">
    <div class="card shadow-sm">
      <div class="card-header">Restablecer contraseña</div>
      <div class="card-body">
        <form method="POST" action="{{ route('patient.password.perform') }}" class="row g-3">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">
          <input type="hidden" name="email" value="{{ $email }}">
          <div class="col-12">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" class="form-control" required>
          </div>
          <div class="col-12 d-grid d-md-flex gap-2">
            <button type="submit" class="btn btn-success">Actualizar contraseña</button>
            <a href="{{ route('patient.access') }}" class="btn btn-outline-secondary">Volver</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

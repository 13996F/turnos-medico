@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12 col-lg-6 mx-auto">
    <div class="card shadow-sm">
      <div class="card-header">Olvidé mi contraseña</div>
      <div class="card-body">
        <form method="POST" action="{{ route('patient.forgot.send') }}" class="row g-3">
          @csrf
          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
          </div>
          <div class="col-12 d-grid d-md-flex gap-2">
            <button type="submit" class="btn btn-primary">Enviar enlace</button>
            <a href="{{ route('patient.access') }}" class="btn btn-outline-secondary">Volver</a>
          </div>
        </form>
        <p class="text-muted small mt-3">Te enviaremos un enlace para restablecer tu contraseña. En ambiente local, el enlace aparecerá en un mensaje de éxito y en el log.</p>
      </div>
    </div>
  </div>
</div>
@endsection

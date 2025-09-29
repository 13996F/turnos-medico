@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12 col-lg-8 mx-auto">
    <div class="card shadow-sm">
      <div class="card-header">Verificación de email requerida</div>
      <div class="card-body">
        @if ($errors->has('verify'))
          <div class="alert alert-warning">{{ $errors->first('verify') }}</div>
        @endif
        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <p>Debes verificar tu email para acceder al panel de administración.</p>
        <p class="text-muted small">Te enviamos un correo con un enlace de verificación que expira en 60 minutos.</p>
        <form method="POST" action="{{ route('verification.admin.send') }}" class="d-inline">
          @csrf
          <button class="btn btn-primary" type="submit">Reenviar correo de verificación</button>
        </form>
        <a href="{{ route('admin.access') }}" class="btn btn-link">Volver al acceso</a>
      </div>
    </div>
  </div>
</div>
@endsection

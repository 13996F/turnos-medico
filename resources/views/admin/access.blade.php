@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12 col-lg-8 mx-auto">
    @if(session('role')==='admin')
      @php($ad = session('admin'))
      <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
          Ya iniciaste sesión como
          <strong>{{ $ad['first_name'] ?? 'Admin' }} {{ $ad['last_name'] ?? '' }}</strong>
          @if(!empty($ad['email']))
            <span class="text-muted">({{ $ad['email'] }})</span>
          @endif
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('reception.index') }}" class="btn btn-sm btn-primary">Ir al panel</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm btn-outline-secondary" type="submit">Cerrar sesión</button>
          </form>
        </div>
      </div>
    @endif

    <div class="card shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span>Acceso de Administrador</span>
      </div>
      <div class="card-body">
        <ul class="nav nav-tabs" id="adminAccessTabs" role="tablist"
            data-active="{{ old('form')==='register' ? 'register' : (old('form')==='login' ? 'login' : ($errors->has('first_name') || $errors->has('last_name') || $errors->has('password_confirmation') ? 'register' : 'login')) }}">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="admin-login-tab" data-bs-toggle="tab" data-bs-target="#admin-login-pane" type="button" role="tab" aria-controls="admin-login-pane" aria-selected="true">Iniciar sesión</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="admin-register-tab" data-bs-toggle="tab" data-bs-target="#admin-register-pane" type="button" role="tab" aria-controls="admin-register-pane" aria-selected="false">Registrarme</button>
          </li>
        </ul>
        <div class="tab-content pt-3">
          <div class="tab-pane fade show active" id="admin-login-pane" role="tabpanel" aria-labelledby="admin-login-tab" tabindex="0">
            <form method="POST" action="{{ route('admin.login') }}" class="row g-3">
              @csrf
              <input type="hidden" name="form" value="login">
              <div class="col-12">
                <label class="form-label">Usuario o Email</label>
                <input type="text" name="login" class="form-control" value="{{ old('login') }}" placeholder="tu_usuario o tu@email.com" required>
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
                <a href="{{ route('admin.google.redirect') }}" class="btn btn-outline-danger">
                  <i class="bi bi-google me-1"></i> Continuar con Google
                </a>
              </div>
            </form>
          </div>
          <div class="tab-pane fade" id="admin-register-pane" role="tabpanel" aria-labelledby="admin-register-tab" tabindex="0">
            <form method="POST" action="{{ route('admin.register') }}" class="row g-3">
              @csrf
              <input type="hidden" name="form" value="register">
              <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Apellido</label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
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
    const tabsEl = document.getElementById('adminAccessTabs');
    if (!tabsEl) return;
    const desired = tabsEl.getAttribute('data-active');
    if (desired === 'register') {
      const trigger = document.querySelector('#admin-register-tab');
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

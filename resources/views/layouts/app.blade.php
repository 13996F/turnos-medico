<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Centro Médico del Milagro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/">
      <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-hospital" viewBox="0 0 16 16" role="img" aria-label="Logo">
        <path d="M6.5 2v2H5v1h1.5v2H8V5H9.5V4H8V2z"/>
        <path d="M3 0a2 2 0 0 0-2 2v13h1v1h12v-1h1V2a2 2 0 0 0-2-2H3m10 2v12H3V2z"/>
      </svg>
      <span class="fw-semibold">Centro Médico del Milagro</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        @unless(request()->routeIs('patient.*', 'appointments.*', 'admin.*') || session('patient_id') || session('role')==='admin')
          <li class="nav-item"><a class="nav-link" href="{{ route('doctor.access') }}">Médico</a></li>
        @endunless
        @if(session('role')==='admin' && !session('patient_id'))
          <li class="nav-item"><a class="nav-link" href="/admin/acceso">Admin</a></li>
        @endif
      </ul>
      <ul class="navbar-nav ms-auto">
        @if(session('role')==='admin')
          @php($ad = session('admin'))
          <li class="nav-item"><span class="navbar-text me-2">
            Conectado: {{ trim(($ad['first_name'] ?? '').' '.($ad['last_name'] ?? '')) ?: 'Admin' }}
            @if(!empty($ad['username']))
              <span class="text-light text-opacity-75">[{{ '@'.$ad['username'] }}]</span>
            @endif
            @if(!empty($ad['email']))
              <span class="text-light text-opacity-75">({{ $ad['email'] }})</span>
            @endif
          </span></li>
          <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="btn btn-sm btn-light" type="submit">Cerrar sesión</button>
            </form>
          </li>
        @elseif(session('role')==='doctor')
          <li class="nav-item"><span class="navbar-text me-2">
            Conectado: {{ session('doctor_name') ? 'Dr./Dra. '.session('doctor_name') : 'Médico #'.session('doctor_id') }}
          </span></li>
          <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="btn btn-sm btn-light" type="submit">Cerrar sesión</button>
            </form>
          </li>
        @elseif(session('patient_id'))
          @php($pg = session('patient_google'))
          <li class="nav-item">
            <span class="navbar-text me-2">
              Paciente: {{ $pg['first_name'] ?? '' }} {{ $pg['last_name'] ?? '' }}
              @if(!empty($pg['email']))
                <span class="text-light text-opacity-75">({{ $pg['email'] }})</span>
              @endif
            </span>
          </li>
          <li class="nav-item">
            <form method="POST" action="{{ route('patient.logout') }}">
              @csrf
              <button class="btn btn-sm btn-light" type="submit">Cerrar sesión</button>
            </form>
          </li>
        @endif
      </ul>
    </div>
  </div>
</nav>
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{ $slot ?? '' }}
    @yield('content')
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

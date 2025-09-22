@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12 col-lg-8 mx-auto">
    <div class="card">
      <div class="card-header">Solicitar turno</div>
      <div class="card-body">
        <form method="POST" action="{{ route('appointments.store') }}" id="turno-form">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="patient_first_name" class="form-control" value="{{ old('patient_first_name') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellido</label>
              <input type="text" name="patient_last_name" class="form-control" value="{{ old('patient_last_name') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">DNI</label>
              <input type="text" name="dni" class="form-control" value="{{ old('dni') }}" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Especialidad</label>
              <select name="specialty_id" id="specialty" class="form-select" required>
                <option value="">Seleccione...</option>
                @foreach($specialties as $s)
                  <option value="{{ $s->id }}" @selected(old('specialty_id')==$s->id)>{{ $s->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Médico</label>
              <select name="doctor_id" id="doctor" class="form-select" required>
                <option value="">Seleccione una especialidad primero</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Día</label>
              <input type="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Hora</label>
              <input type="time" name="time" class="form-control" value="{{ old('time', '09:00') }}" required>
            </div>
          </div>

          <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Reservar turno</button>
            <a href="/admin" class="btn btn-outline-secondary">Ir a Administrador</a>
            <a href="/medico" class="btn btn-outline-secondary">Ir a Médico</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const specialtySelect = document.getElementById('specialty');
  const doctorSelect = document.getElementById('doctor');

  async function loadDoctors(specialtyId, selectedId = null) {
    doctorSelect.innerHTML = '<option value="">Cargando...</option>';
    if (!specialtyId) {
      doctorSelect.innerHTML = '<option value="">Seleccione una especialidad primero</option>';
      return;
    }
    const res = await fetch(`/api/specialties/${specialtyId}/doctors`);
    const doctors = await res.json();
    if (!Array.isArray(doctors) || doctors.length === 0) {
      doctorSelect.innerHTML = '<option value="">No hay médicos activos</option>';
      return;
    }
    doctorSelect.innerHTML = '<option value="">Seleccione...</option>' + doctors.map(d => {
      const sel = selectedId && Number(selectedId) === Number(d.id) ? 'selected' : '';
      return `<option value="${d.id}" ${sel}>${d.name}</option>`;
    }).join('');
  }

  specialtySelect.addEventListener('change', (e) => {
    loadDoctors(e.target.value);
  });

  // precargar si viene old()
  @if(old('specialty_id'))
    loadDoctors({{ old('specialty_id') }}, {{ old('doctor_id') ? (int) old('doctor_id') : 'null' }});
  @endif
</script>
@endpush

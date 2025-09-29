@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12 col-lg-8 mx-auto">
    <div class="card">
      <div class="card-header">Solicitar turno</div>
      <div class="card-body">
        {{-- Modo mínimo: sin integración con Google --}}
        <form method="POST" action="{{ route('appointments.store') }}" id="turno-form">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="patient_first_name" class="form-control" value="{{ old('patient_first_name', $patient['first_name'] ?? '') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellido</label>
              <input type="text" name="patient_last_name" class="form-control" value="{{ old('patient_last_name', $patient['last_name'] ?? '') }}" required>
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
              <select name="time" id="time" class="form-select" required>
                <option value="">Seleccione un médico y una fecha</option>
              </select>
              <div class="form-text">Los horarios ocupados se mostrarán deshabilitados.</div>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="has_insurance" name="has_insurance" {{ old('has_insurance') ? 'checked' : '' }}>
                <label class="form-check-label" for="has_insurance">
                  ¿Tiene obra social?
                </label>
              </div>
            </div>
            <div class="col-12" id="insurance_name_group" style="display: none;">
              <label class="form-label">Obra social</label>
              <input type="text" name="insurance_name" class="form-control" value="{{ old('insurance_name') }}" placeholder="Ej: OSDE, Swiss Medical, etc.">
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary">Reservar turno</button>
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
  const hasInsurance = document.getElementById('has_insurance');
  const insuranceGroup = document.getElementById('insurance_name_group');
  const dateInput = document.querySelector('input[name="date"]');
  const timeSelect = document.getElementById('time');

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
    // Limpiar horarios al cambiar especialidad
    timeSelect.innerHTML = '<option value="">Seleccione un médico y una fecha</option>';
  });

  // precargar si viene old()
  @if(old('specialty_id'))
    loadDoctors({{ old('specialty_id') }}, {{ old('doctor_id') ? (int) old('doctor_id') : 'null' }});
  @endif

  function toggleInsurance() {
    insuranceGroup.style.display = hasInsurance.checked ? 'block' : 'none';
  }
  hasInsurance.addEventListener('change', toggleInsurance);
  // estado inicial
  toggleInsurance();

  // --- Manejo de horarios disponibles ---
  function generateTimeSlots(start = '08:00', end = '18:00', stepMinutes = 30) {
    const slots = [];
    const [sh, sm] = start.split(':').map(Number);
    const [eh, em] = end.split(':').map(Number);
    let current = new Date();
    current.setHours(sh, sm, 0, 0);
    const endDate = new Date();
    endDate.setHours(eh, em, 0, 0);
    while (current <= endDate) {
      const h = current.getHours().toString().padStart(2, '0');
      const m = current.getMinutes().toString().padStart(2, '0');
      slots.push(`${h}:${m}`);
      current.setMinutes(current.getMinutes() + stepMinutes);
    }
    return slots;
  }

  async function updateOccupied() {
    const doctorId = doctorSelect.value;
    const dateVal = dateInput.value;
    if (!doctorId || !dateVal) {
      timeSelect.innerHTML = '<option value="">Seleccione un médico y una fecha</option>';
      return;
    }
    try {
      const res = await fetch(`/api/doctors/${doctorId}/occupied?date=${encodeURIComponent(dateVal)}`);
      const occupied = await res.json(); // array de 'HH:MM'
      const slots = generateTimeSlots('08:00','18:00',30);
      const oldSelected = `{{ old('time') }}`;
      timeSelect.innerHTML = slots.map(t => {
        const disabled = occupied.includes(t) ? 'disabled' : '';
        const selected = (oldSelected && oldSelected === t) ? 'selected' : '';
        const label = occupied.includes(t) ? `${t} (ocupado)` : t;
        return `<option value="${t}" ${disabled} ${selected}>${label}</option>`;
      }).join('');
      // Si el old() está ocupado, no quedará selected; asegurarse de que haya uno seleccionado válido
      if (!timeSelect.value) {
        // seleccionar el primer disponible
        const firstEnabled = Array.from(timeSelect.options).find(o => !o.disabled && o.value);
        if (firstEnabled) firstEnabled.selected = true;
      }
    } catch (e) {
      timeSelect.innerHTML = '<option value="">No se pudieron cargar los horarios</option>';
    }
  }

  doctorSelect.addEventListener('change', updateOccupied);
  dateInput.addEventListener('change', updateOccupied);

  // Cargar ocupados al iniciar si hay old doctor/date
  @if(old('doctor_id') && old('date'))
    document.addEventListener('DOMContentLoaded', updateOccupied);
  @endif
</script>
@endpush

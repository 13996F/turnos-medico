@extends('layouts.app')

@section('content')
<div class="row g-3 align-items-end mb-3">
  <div class="col-md-4">
    <label class="form-label">Fecha</label>
    <form method="GET" action="{{ route('doctor.index') }}" id="filter-form" class="d-flex gap-2">
      <input type="date" name="date" class="form-control" value="{{ $date }}">
      <select name="doctor_id" class="form-select">
        @foreach($doctors as $d)
          <option value="{{ $d->id }}" @selected($doctorId==$d->id)>{{ $d->name }} ({{ $d->specialty->name ?? '' }})</option>
        @endforeach
      </select>
      <button class="btn btn-outline-primary" type="submit">Ver</button>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead>
      <tr>
        <th>Hora</th>
        <th>Paciente</th>
        <th>DNI</th>
        <th>Teléfono</th>
        <th>Especialidad</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      @forelse($appointments->sortByDesc(fn($a) => $a->status === 'paid')->values() as $a)
        <tr class="{{ $a->status === 'paid' ? 'table-success' : '' }}">
          <td>{{ $a->scheduled_at->format('H:i') }}</td>
          <td>{{ $a->patient_last_name }}, {{ $a->patient_first_name }}</td>
          <td>{{ $a->dni }}</td>
          <td>{{ $a->phone }}</td>
          <td>{{ $a->specialty->name }}</td>
          <td>
            @php
              $badge = [
                'arrived' => 'warning',
                'paid' => 'success',
              ][$a->status] ?? 'secondary';
            @endphp
            <span class="badge text-bg-{{ $badge }}">{{ ucfirst($a->status) }}</span>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center">No hay pacientes para el filtro seleccionado.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection

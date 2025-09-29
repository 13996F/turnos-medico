@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5>Recepción - Turnos del día</h5>
  <form method="GET" action="{{ route('reception.index') }}" class="d-flex align-items-center gap-2">
    <input type="date" name="date" class="form-control" value="{{ $date }}">
    <button class="btn btn-outline-primary" type="submit">Ver</button>
  </form>
</div>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>Hora</th>
        <th>Paciente</th>
        <th>DNI</th>
        <th>Teléfono</th>
        <th>Especialidad</th>
        <th>Médico</th>
        <th>Obra social</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($appointments as $a)
        <tr>
          <td>{{ $a->scheduled_at->format('H:i') }}</td>
          <td>{{ $a->patient_last_name }}, {{ $a->patient_first_name }}</td>
          <td>{{ $a->dni }}</td>
          <td>{{ $a->phone }}</td>
          <td>{{ $a->doctor->specialty->name }}</td>
          <td>{{ $a->doctor->name }}</td>
          <td>{{ $a->has_insurance ? ($a->insurance_name ?: 'Con cobertura') : 'Sin obra social' }}</td>
          <td>
            @php
              $badge = [
                'requested' => 'secondary',
                'arrived' => 'warning',
                'paid' => 'success',
                'completed' => 'info',
              ][$a->status] ?? 'secondary';
            @endphp
            <span class="badge text-bg-{{ $badge }}">{{ ucfirst($a->status) }}</span>
          </td>
          <td class="d-flex flex-wrap gap-2">
            @if($a->status === 'requested')
              <form method="POST" action="{{ route('reception.arrived', $a) }}">
                @csrf
                <button class="btn btn-sm btn-warning" type="submit">Confirmar asistencia</button>
              </form>
            @endif
            @if(in_array($a->status, ['requested', 'arrived']))
              <form method="POST" action="{{ route('reception.paid', $a) }}">
                @csrf
                <button class="btn btn-sm btn-success" type="submit">Confirmar pago</button>
              </form>
            @endif
            <a href="{{ route('reception.edit', $a) }}" class="btn btn-sm btn-outline-primary">Editar</a>
            <form method="POST" action="{{ route('reception.destroy', $a) }}" onsubmit="return confirm('¿Eliminar este turno? Esta acción no se puede deshacer.');">
              @csrf
              @method('DELETE')
              <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center">No hay turnos para la fecha seleccionada.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection

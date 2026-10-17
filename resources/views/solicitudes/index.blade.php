<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Solicitudes de Soporte Técnico</title>
  <style>
    body{font-family:Arial,sans-serif;margin:30px}
    table{border-collapse:collapse;width:100%;margin-top:20px}
    th,td{border:1px solid #ccc;padding:8px;text-align:left}
    th{background:#f0f0f0}
    .ok{background:#d4edda;padding:10px;margin:10px 0}
    .err{background:#f8d7da;padding:10px;margin:10px 0}
    label{display:block;margin-top:8px}
  </style>
</head>
<body>
  <h1>Sistema de Solicitudes de Soporte Técnico</h1>

  @if(session('ok'))  <div class="ok">{{ session('ok') }}</div> @endif
  @if(session('error')) <div class="err">{{ session('error') }}</div> @endif
  @if($errors->any())
    <div class="err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <h2>Nueva solicitud</h2>
  <form method="POST" action="{{ route('solicitudes.store') }}">
    @csrf
    <label>Solicitante <input name="solicitante" value="{{ old('solicitante') }}" required></label>
    <label>Departamento <input name="departamento" value="{{ old('departamento') }}" required></label>
    <label>Descripción <textarea name="descripcion" required>{{ old('descripcion') }}</textarea></label>
    <label>Prioridad
      <select name="prioridad">
        <option value="baja">Baja</option>
        <option value="media">Media</option>
        <option value="alta">Alta</option>
      </select>
    </label>
    <label>Estado <input value="Pendiente" disabled></label>
    <button type="submit">Registrar</button>
  </form>

  <h2>Listado</h2>
  <form method="POST" action="{{ route('solicitudes.enviar') }}">
    @csrf
    <button type="submit">Enviar a la base de datos de la red local</button>
  </form>

  <table>
    <tr><th>ID</th><th>Solicitante</th><th>Departamento</th><th>Descripción</th>
        <th>Prioridad</th><th>Estado</th><th>Fecha registro</th><th>Enviado</th><th>Cambiar estado</th></tr>
    @forelse($solicitudes as $s)
      <tr>
        <td>{{ $s->id }}</td><td>{{ $s->solicitante }}</td><td>{{ $s->departamento }}</td>
        <td>{{ $s->descripcion }}</td><td>{{ ucfirst($s->prioridad) }}</td>
        <td>{{ $s->estado }}</td><td>{{ $s->created_at }}</td>
        <td>{{ $s->enviado_at ?? '—' }}</td>
        <td>
          <form method="POST" action="{{ route('solicitudes.estado', $s) }}">
            @csrf @method('PATCH')
            <select name="estado">
              @foreach(['Pendiente','En proceso','Resuelta'] as $e)
                <option {{ $s->estado === $e ? 'selected' : '' }}>{{ $e }}</option>
              @endforeach
            </select>
            <button>Guardar</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="9">No hay solicitudes registradas.</td></tr>
    @endforelse
  </table>
</body>
</html>
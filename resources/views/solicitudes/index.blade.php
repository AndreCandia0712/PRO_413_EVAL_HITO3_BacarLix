<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Solicitudes de Soporte Técnico - bacarlix</title>
  <style>
    body{font-family:Arial,sans-serif;margin:30px;color:#222}
    table{border-collapse:collapse;width:100%;margin-top:15px;font-size:14px}
    th,td{border:1px solid #ccc;padding:8px;text-align:left;vertical-align:top}
    th{background:#f0f0f0}
    .ok{background:#d4edda;padding:10px;margin:10px 0;border-radius:4px}
    .err{background:#f8d7da;padding:10px;margin:10px 0;border-radius:4px}
    label{display:block;margin-top:8px}
    input,select,textarea{padding:6px;min-width:260px}
    button{padding:7px 14px;cursor:pointer}
  </style>
</head>
<body>
  <h1>Sistema de Registro y Control de Solicitudes de Soporte Técnico</h1>

  @if(session('ok'))    <div class="ok">{{ session('ok') }}</div> @endif
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
        @foreach(['baja'=>'Baja','media'=>'Media','alta'=>'Alta'] as $v=>$t)
          <option value="{{ $v }}" {{ old('prioridad')===$v?'selected':'' }}>{{ $t }}</option>
        @endforeach
      </select>
    </label>
    <label>Estado inicial <input value="Pendiente" disabled></label>
    <br><button type="submit">Registrar solicitud</button>
  </form>

  <h2>Listado de solicitudes</h2>
  <form method="POST" action="{{ route('solicitudes.enviar') }}">
    @csrf
    <button type="submit">Enviar todo a la base de datos de la red local</button>
  </form>

  <table>
    <tr>
      <th>Código</th><th>Solicitante</th><th>Departamento</th><th>Descripción</th>
      <th>Prioridad</th><th>Estado</th><th>Fecha de registro</th><th>Enviado a red</th><th>Cambiar estado</th>
    </tr>
    @forelse($solicitudes as $s)
      <tr>
        <td>{{ $s->id }}</td>
        <td>{{ $s->solicitante }}</td>
        <td>{{ $s->departamento }}</td>
        <td>{{ $s->descripcion }}</td>
        <td>{{ ucfirst($s->prioridad) }}</td>
        <td>{{ $s->estado }}</td>
        <td>{{ $s->created_at }}</td>
        <td>{{ $s->enviado_at ? $s->enviado_at : '—' }}</td>
        <td>
          <form method="POST" action="{{ route('solicitudes.estado', $s) }}">
            @csrf @method('PATCH')
            <select name="estado" style="min-width:120px">
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

<?php

namespace App\Http\Controllers;

use App\Models\SolicitudSoporte;
use App\Services\SincronizadorDestino;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class SolicitudController extends Controller
{
    public function __construct(private SincronizadorDestino $sync) {}

    public function index()
    {
        return view('solicitudes.index', [
            'solicitudes' => SolicitudSoporte::orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'solicitante'  => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'descripcion'  => 'required|string',
            'prioridad'    => ['required', Rule::in(['baja', 'media', 'alta'])],
        ]);
        $data['estado'] = 'Pendiente';

        $solicitud = SolicitudSoporte::create($data);

        return $this->respuestaConSync($solicitud, 'Solicitud registrada correctamente.');
    }

    public function actualizarEstado(Request $request, SolicitudSoporte $solicitud)
    {
        $request->validate([
            'estado' => ['required', Rule::in(['Pendiente', 'En proceso', 'Resuelta'])],
        ]);
        $solicitud->update(['estado' => $request->estado]);

        return $this->respuestaConSync($solicitud, "Solicitud #{$solicitud->id} actualizada a '{$solicitud->estado}'.");
    }

    /** Botón manual: envía TODAS las solicitudes a la BD de la red local. */
    public function enviar()
    {
        try {
            $n = $this->sync->sincronizarTodas();
            return back()->with('ok', "Envío exitoso: $n registro(s) sincronizados con la base de datos de la red local.");
        } catch (Throwable $e) {
            return back()->with('error', 'Error al enviar a la red local: ' . $e->getMessage());
        }
    }

    /** Guarda en local y, además, intenta reflejar el cambio en la BD de la red. */
    private function respuestaConSync(SolicitudSoporte $s, string $mensaje)
    {
        try {
            $this->sync->sincronizar($s);
            return back()->with('ok', $mensaje . ' También se envió a la base de datos de la red local.');
        } catch (Throwable $e) {
            return back()->with('ok', $mensaje)
                ->with('error', 'No se pudo enviar a la red local (queda pendiente, use el botón de envío): ' . $e->getMessage());
        }
    }
}

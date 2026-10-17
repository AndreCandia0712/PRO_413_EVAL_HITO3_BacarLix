<?php
namespace App\Http\Controllers;

use App\Models\SolicitudSoporte;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SolicitudController extends Controller
{
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
            'prioridad'    => ['required', Rule::in(['baja','media','alta'])],
        ]);
        $data['estado'] = 'Pendiente';
        SolicitudSoporte::create($data);
        return back()->with('ok', 'Solicitud registrada correctamente.');
    }

    public function actualizarEstado(Request $request, SolicitudSoporte $solicitud)
    {
        $request->validate([
            'estado' => ['required', Rule::in(['Pendiente','En proceso','Resuelta'])],
        ]);
        $solicitud->update(['estado' => $request->estado]);
        return back()->with('ok', "Solicitud #{$solicitud->id} actualizada.");
    }

    // enviar() -> lo implementa Ricardo (Fase 3)
}
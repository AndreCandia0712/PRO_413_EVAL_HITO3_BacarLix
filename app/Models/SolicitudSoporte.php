<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudSoporte extends Model
{
    protected $table = 'solicitudes_soporte';

    protected $fillable = ['solicitante', 'departamento', 'descripcion', 'prioridad', 'estado'];

    protected $casts = ['enviado_at' => 'datetime'];
}

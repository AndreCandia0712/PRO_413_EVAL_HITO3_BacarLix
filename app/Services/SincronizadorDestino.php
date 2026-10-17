<?php

namespace App\Services;

use App\Models\SolicitudSoporte;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * Envía (upsert) las solicitudes de la BD local a la BD MySQL de la red local.
 * Clave de identificación en destino: (grupo, id_origen). Reenviar NO duplica.
 */
class SincronizadorDestino
{
    protected function conexion(): Connection
    {
        config(['database.connections.destino' => [
            'driver'    => 'mysql',
            'host'      => config('destino.host'),
            'port'      => config('destino.port'),
            'database'  => config('destino.database'),
            'username'  => config('destino.username'),
            'password'  => config('destino.password'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'options'   => [\PDO::ATTR_TIMEOUT => 5],
        ]]);
        DB::purge('destino');

        $con = DB::connection('destino');
        $con->getPdo(); // lanza excepción si no hay conexión
        return $con;
    }

    /**
     * Mapeo local -> destino. SI la tabla de destino tiene otros nombres
     * de columna, se ajusta SOLO este método (revisar con DESCRIBE).
     */
    protected function fila(SolicitudSoporte $s): array
    {
        return [
            'solicitante'  => $s->solicitante,
            'departamento' => $s->departamento,
            'descripcion'  => $s->descripcion,
            'prioridad'    => $s->prioridad,
            'estado'       => $s->estado,
            'created_at'   => $s->created_at,
            'updated_at'   => $s->updated_at,
        ];
    }

    protected function enviarUna(Connection $destino, SolicitudSoporte $s): void
    {
        $destino->table(config('destino.table'))->updateOrInsert(
            ['grupo' => config('destino.grupo'), 'id_origen' => $s->id],
            $this->fila($s)
        );

        // Marcar como enviado sin alterar updated_at
        DB::table('solicitudes_soporte')->where('id', $s->id)->update(['enviado_at' => now()]);
    }

    /** Sincroniza un solo registro (se usa al crear/actualizar). */
    public function sincronizar(SolicitudSoporte $s): void
    {
        $this->enviarUna($this->conexion(), $s);
    }

    /** Sincroniza todos los registros. Devuelve la cantidad enviada. */
    public function sincronizarTodas(): int
    {
        $destino = $this->conexion();
        $n = 0;
        foreach (SolicitudSoporte::orderBy('id')->get() as $s) {
            $this->enviarUna($destino, $s);
            $n++;
        }
        return $n;
    }
}

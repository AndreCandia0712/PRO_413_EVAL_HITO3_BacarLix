<?php

namespace App\Console\Commands;

use App\Services\SincronizadorDestino;
use Illuminate\Console\Command;
use Throwable;

class EnviarSolicitudes extends Command
{
    protected $signature = 'solicitudes:enviar';
    protected $description = 'Envía (upsert) las solicitudes locales a la BD MySQL de la red local';

    public function handle(SincronizadorDestino $sync): int
    {
        try {
            $this->info('Registros enviados: ' . $sync->sincronizarTodas());
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

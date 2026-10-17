<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_soporte', function (Blueprint $table) {
            $table->id(); // BIGINT autoincremental
            $table->string('solicitante');
            $table->string('departamento');
            $table->text('descripcion');
            $table->string('prioridad');                   // baja, media, alta
            $table->string('estado')->default('Pendiente'); // Pendiente, En proceso, Resuelta
            $table->timestamp('enviado_at')->nullable();   // control de envío a la red local
            $table->timestamps();                          // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_soporte');
    }
};

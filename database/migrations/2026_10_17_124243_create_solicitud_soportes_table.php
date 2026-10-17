migration Schema::create('solicitudes_soporte', function (Blueprint $t) {
    $t->id();
    $t->string('solicitante');
    $t->string('departamento');
    $t->text('descripcion');
    $t->string('prioridad');              
    $t->string('estado')->default('Pendiente');
    $t->timestamps();
});
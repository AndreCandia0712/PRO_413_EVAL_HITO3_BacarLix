Route::get('/', [SolicitudController::class,'index']);
Route::post('/solicitudes', [SolicitudController::class,'store']);
Route::patch('/solicitudes/{solicitud}/estado', [SolicitudController::class,'updateEstado']);
Route::post('/enviar-destino', [SolicitudController::class,'enviarDestino']);

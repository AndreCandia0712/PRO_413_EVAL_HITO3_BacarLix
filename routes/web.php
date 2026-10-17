use App\Http\Controllers\SolicitudController;

Route::get('/', fn() => redirect()->route('solicitudes.index'));
Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
Route::patch('/solicitudes/{solicitud}/estado', [SolicitudController::class, 'actualizarEstado'])->name('solicitudes.estado');
Route::post('/solicitudes/enviar', [SolicitudController::class, 'enviar'])->name('solicitudes.enviar');
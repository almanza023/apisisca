<?php


use App\Http\Controllers\V1\LogroObservacionController;
use Illuminate\Support\Facades\Route;

Route::get('logros-observacion', [LogroObservacionController::class, 'index']);
Route::post('logros-observacion', [LogroObservacionController::class, 'store']);
Route::get('logros-observacion/{id}', [LogroObservacionController::class, 'show']);
Route::put('logros-observacion/{id}', [LogroObservacionController::class, 'update']);
Route::delete('logros-observacion/{id}', [LogroObservacionController::class, 'destroy']);
Route::post('logros-observacion/cambiarEstado', [LogroObservacionController::class, 'cambiarEstado']);
Route::post('logros-observacion/filtrar', [LogroObservacionController::class, 'filtrar']);
Route::post('logros-observacion/getFiltros', [LogroObservacionController::class, 'getDataFiltro']);



?>

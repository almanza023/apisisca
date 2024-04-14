<?php

use App\Http\Controllers\V1\LogroPreescolarController;
use Illuminate\Support\Facades\Route;

Route::get('logros-preescolar', [LogroPreescolarController::class, 'index']);
Route::post('logros-preescolar', [LogroPreescolarController::class, 'store']);
Route::get('logros-preescolar/{id}', [LogroPreescolarController::class, 'show']);
Route::put('logros-preescolar/{id}', [LogroPreescolarController::class, 'update']);
Route::delete('logros-preescolar/{id}', [LogroPreescolarController::class, 'destroy']);
Route::post('logros-preescolar/cambiarEstado', [LogroPreescolarController::class, 'cambiarEstado']);
Route::post('logros-preescolar/filtrar', [LogroPreescolarController::class, 'filtrar']);

Route::post('logros-preescolar/getFiltros', [LogroPreescolarController::class, 'getDataFiltro']);


?>

<?php

use App\Http\Controllers\V1\LogroDisciplinarioController;
use Illuminate\Support\Facades\Route;

Route::get('logros-disciplinarios', [LogroDisciplinarioController::class, 'index']);
Route::post('logros-disciplinarios', [LogroDisciplinarioController::class, 'store']);
Route::get('logros-disciplinarios/{id}', [LogroDisciplinarioController::class, 'show']);
Route::put('logros-disciplinarios/{id}', [LogroDisciplinarioController::class, 'update']);
Route::delete('logros-disciplinarios/{id}', [LogroDisciplinarioController::class, 'destroy']);
Route::post('logros-disciplinarios/cambiarEstado', [LogroDisciplinarioController::class, 'cambiarEstado']);
Route::post('logros-disciplinarios/filtrar', [LogroDisciplinarioController::class, 'filtrar']);

Route::post('logros-disciplinarios/getFiltros', [LogroDisciplinarioController::class, 'getDataFiltro']);


?>

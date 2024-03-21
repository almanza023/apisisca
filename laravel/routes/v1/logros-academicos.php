<?php

use App\Http\Controllers\V1\CargaAcademicaController;
use App\Http\Controllers\V1\LogroAcademicoController;
use Illuminate\Support\Facades\Route;

Route::get('logros-academicos', [LogroAcademicoController::class, 'index']);
Route::post('logros-academicos', [LogroAcademicoController::class, 'store']);
Route::get('logros-academicos/{id}', [LogroAcademicoController::class, 'show']);
Route::put('logros-academicos/{id}', [LogroAcademicoController::class, 'update']);
Route::delete('logros-academicos/{id}', [LogroAcademicoController::class, 'destroy']);
Route::post('logros-academicos/cambiarEstado', [LogroAcademicoController::class, 'cambiarEstado']);
Route::post('logros-academicos/filtrar', [LogroAcademicoController::class, 'filtrar']);

Route::post('logros-academicos/getFiltros', [LogroAcademicoController::class, 'getDataFiltro']);


?>

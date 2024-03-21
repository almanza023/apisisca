<?php

use App\Http\Controllers\V1\PeriodoController;
use Illuminate\Support\Facades\Route;

Route::get('periodos', [PeriodoController::class, 'index']);
Route::post('periodos', [PeriodoController::class, 'store']);
Route::get('periodos/{id}', [PeriodoController::class, 'show']);
Route::put('periodos/{id}', [PeriodoController::class, 'update']);
Route::delete('periodos/{id}', [PeriodoController::class, 'destroy']);
Route::post('periodos/cambiarEstado', [PeriodoController::class, 'cambiarEstado']);
Route::get('periodos-activos', [PeriodoController::class, 'activos']);

?>

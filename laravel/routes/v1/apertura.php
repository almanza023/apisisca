<?php

use App\Http\Controllers\V1\AperturaPeriodoController;
use Illuminate\Support\Facades\Route;

Route::get('apertura-periodos', [AperturaPeriodoController::class, 'index']);
Route::post('apertura-periodos', [AperturaPeriodoController::class, 'store']);
Route::get('apertura-periodos/{id}', [AperturaPeriodoController::class, 'show']);
Route::put('apertura-periodos/{id}', [AperturaPeriodoController::class, 'update']);
Route::delete('apertura-periodos/{id}', [AperturaPeriodoController::class, 'destroy']);
Route::get('apertura-periodos-activos', [AperturaPeriodoController::class, 'activos']);
Route::post('apertura-periodos/cambiarEstado', [AperturaPeriodoController::class, 'cambiarEstado']);
Route::post('apertura-periodos-abiertos', [AperturaPeriodoController::class, 'getAbiertos']);



?>

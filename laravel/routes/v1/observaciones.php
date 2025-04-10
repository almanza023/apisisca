<?php


use App\Http\Controllers\V1\ObservacionesController;
use Illuminate\Support\Facades\Route;

Route::get('observaciones', [ObservacionesController::class, 'index']);
Route::post('observaciones', [ObservacionesController::class, 'store']);
Route::get('observaciones/{id}', [ObservacionesController::class, 'show']);
Route::delete('observaciones/{id}', [ObservacionesController::class, 'destroy']);
Route::post('observaciones-listado', [ObservacionesController::class, 'getByEstudiantes']);

Route::post('observaciones-periodo', [ObservacionesController::class, 'getConvivenciaByPeriodo']);
Route::post('observaciones-estudiantes', [ObservacionesController::class, 'getByEstudiantes']);
Route::post('observaciones-matricula', [ObservacionesController::class, 'getConvivenciaByMatricula']);
Route::post('observaciones-listado-estudiantes', [ObservacionesController::class, 'getListadoEstudiantes']);

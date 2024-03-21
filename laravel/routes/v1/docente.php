<?php

use App\Http\Controllers\V1\DocenteController;
use Illuminate\Support\Facades\Route;

Route::get('docentes', [DocenteController::class, 'index']);
Route::post('docentes', [DocenteController::class, 'store']);
Route::get('docentes/{id}', [DocenteController::class, 'show']);
Route::put('docentes/{id}', [DocenteController::class, 'update']);
Route::delete('docentes/{id}', [DocenteController::class, 'destroy']);
Route::post('docentes/cambiarEstado', [DocenteController::class, 'cambiarEstado']);
Route::get('docentes-activos', [DocenteController::class, 'activos']);
Route::get('docentes/tipos/{id}', [DocenteController::class, 'getByTipo']);

?>

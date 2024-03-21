<?php

use App\Http\Controllers\V1\DireccionGradoController;
use Illuminate\Support\Facades\Route;

Route::get('direccion-grados', [DireccionGradoController::class, 'index']);
Route::post('direccion-grados', [DireccionGradoController::class, 'store']);
Route::get('direccion-grados/{id}', [DireccionGradoController::class, 'show']);
Route::put('direccion-grados/{id}', [DireccionGradoController::class, 'update']);
Route::delete('direccion-grados/{id}', [DireccionGradoController::class, 'destroy']);
Route::post('direccion-grados/cambiarEstado', [DireccionGradoController::class, 'cambiarEstado']);


?>

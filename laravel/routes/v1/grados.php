<?php

use App\Http\Controllers\V1\GradoController;
use Illuminate\Support\Facades\Route;

Route::get('grados', [GradoController::class, 'index']);
Route::post('grados', [GradoController::class, 'store']);
Route::get('grados/{id}', [GradoController::class, 'show']);
Route::put('grados/{id}', [GradoController::class, 'update']);
Route::delete('grados/{id}', [GradoController::class, 'destroy']);
Route::get('grados-activos', [GradoController::class, 'activos']);
Route::post('grados/cambiarEstado', [GradoController::class, 'cambiarEstado']);
Route::get('grados-preescolar', [GradoController::class, 'getGradosPreescolar']);
Route::get('grados-secundaria', [GradoController::class, 'getGradosSecundaria']);

?>

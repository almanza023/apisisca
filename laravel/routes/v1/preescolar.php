<?php

use App\Http\Controllers\V1\PreescolarController;
use Illuminate\Support\Facades\Route;

Route::get('preescolar', [PreescolarController::class, 'index']);
Route::post('preescolar', [PreescolarController::class, 'store']);
Route::get('preescolar/{id}', [PreescolarController::class, 'show']);
Route::put('preescolar/{id}', [PreescolarController::class, 'update']);
Route::delete('preescolar/{id}', [PreescolarController::class, 'destroy']);


Route::post('preescolar-listado-estudiantes', [PreescolarController::class, 'getListadoEstudiantes']);
Route::post('preescolar-matricula', [PreescolarController::class, 'getNotaByMatricula']);

?>

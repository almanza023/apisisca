<?php

use App\Http\Controllers\V1\CargaAcademicaController;
use Illuminate\Support\Facades\Route;

Route::get('carga', [CargaAcademicaController::class, 'index']);
Route::post('carga', [CargaAcademicaController::class, 'store']);
Route::get('carga/{id}', [CargaAcademicaController::class, 'show']);
Route::put('carga/{id}', [CargaAcademicaController::class, 'update']);
Route::delete('carga/{id}', [CargaAcademicaController::class, 'destroy']);
Route::post('carga/cambiarEstado', [CargaAcademicaController::class, 'cambiarEstado']);
Route::post('carga/filtrar', [CargaAcademicaController::class, 'filtrar']);
Route::get('carga-agregados', [CargaAcademicaController::class, 'agregados']);
Route::post('carga-agregar', [CargaAcademicaController::class, 'agregadosActualizar']);


Route::post('carga-asignaturas-docentes', [CargaAcademicaController::class, 'getByAsignaturasDocente']);
Route::post('carga-grados-docentes', [CargaAcademicaController::class, 'getByGradosDocente']);
Route::post('carga-asignaturas-grados', [CargaAcademicaController::class, 'getByAsignaturasGrado']);
Route::post('carga-grados', [CargaAcademicaController::class, 'getByGrados']);

?>

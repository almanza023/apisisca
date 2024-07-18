<?php

use App\Http\Controllers\V1\NivelacionController;
use Illuminate\Support\Facades\Route;

        Route::get('nivelaciones', [NivelacionController::class, 'index']);
         Route::post('nivelaciones', [NivelacionController::class, 'store']);
         Route::get('nivelaciones/{id}', [NivelacionController::class, 'show']);
         Route::delete('nivelaciones/{id}', [NivelacionController::class, 'destroy']);
         Route::post('nivelaciones-listado', [NivelacionController::class, 'getByEstudiantesCalificacion']);
         Route::post('nivelaciones-sedes', [NivelacionController::class, 'getBySedeGrado']);

         Route::post('nivelaciones-periodo', [NivelacionController::class, 'getNivelacionesPeriodo']);
         Route::post('nivelaciones-estudiantes', [NivelacionController::class, 'getByEstudiantes']);
         Route::post('nivelaciones-individual', [NivelacionController::class, 'storeIndividual']);
         Route::post('nivelaciones-matricula', [NivelacionController::class, 'getNotaByMatricula']);


?>

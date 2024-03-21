<?php

use App\Http\Controllers\V1\CalificacionController;
use Illuminate\Support\Facades\Route;

        Route::get('calificaciones', [CalificacionController::class, 'index']);
         Route::post('calificaciones', [CalificacionController::class, 'store']);
         Route::get('calificaciones/{id}', [CalificacionController::class, 'show']);
         Route::delete('calificaciones/{id}', [CalificacionController::class, 'destroy']);
         Route::post('calificaciones-listado', [CalificacionController::class, 'getByEstudiantesCalificacion']);
         Route::post('calificaciones-sedes', [CalificacionController::class, 'getBySedeGrado']);

         Route::post('calificaciones-periodo', [CalificacionController::class, 'getCalificacionesPeriodo']);
         Route::post('calificaciones-estudiantes', [CalificacionController::class, 'getByEstudiantes']);
         Route::post('calificaciones-individual', [CalificacionController::class, 'storeIndividual']);
         Route::post('calificaciones-matricula', [CalificacionController::class, 'getNotaByMatricula']);


?>

<?php

use App\Http\Controllers\V1\MatriculaController;
use Illuminate\Support\Facades\Route;

        Route::get('matriculas', [MatriculaController::class, 'index']);
         Route::post('matriculas', [MatriculaController::class, 'store']);
         Route::put('matriculas/{id}', [MatriculaController::class, 'update']);
         Route::get('matriculas/{id}', [MatriculaController::class, 'show']);
         Route::delete('matriculas/{id}', [MatriculaController::class, 'destroy']);
         Route::post('matriculas/cambiarEstado', [MatriculaController::class, 'cambiarEstado']);
         Route::post('matriculas-listado', [MatriculaController::class, 'getByEstudiantesCalificacion']);
         Route::post('matriculas-sedes', [MatriculaController::class, 'getBySedeGrado']);
         Route::post('matriculas/filtrar', [MatriculaController::class, 'filtrar']);



?>

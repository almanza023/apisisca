<?php

use App\Http\Controllers\V1\ConvivenciaController;
use Illuminate\Support\Facades\Route;

         Route::get('convivencia', [ConvivenciaController::class, 'index']);
         Route::post('convivencia', [ConvivenciaController::class, 'store']);
         Route::get('convivencia/{id}', [ConvivenciaController::class, 'show']);
         Route::delete('convivencia/{id}', [ConvivenciaController::class, 'destroy']);
         Route::post('convivencia-listado', [ConvivenciaController::class, 'getByEstudiantes']);
         Route::post('convivencia-individual', [ConvivenciaController::class, 'storeIndividual']);


         Route::post('convivencia-periodo', [ConvivenciaController::class, 'getConvivenciaByPeriodo']);
         Route::post('convivencia-estudiantes', [ConvivenciaController::class, 'getByEstudiantes']);
         Route::post('convivencia-matricula', [ConvivenciaController::class, 'getConvivenciaByMatricula']);
         Route::post('convivencia-listado-estudiantes', [ConvivenciaController::class, 'getListadoEstudiantes']);


?>

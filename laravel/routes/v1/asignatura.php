<?php

use App\Http\Controllers\V1\AsignaturaController;
use Illuminate\Support\Facades\Route;

        Route::get('asignaturas', [AsignaturaController::class, 'index']);
         Route::post('asignaturas', [AsignaturaController::class, 'store']);
         Route::get('asignaturas/{id}', [AsignaturaController::class, 'show']);
         Route::put('asignaturas/{id}', [AsignaturaController::class, 'update']);
         Route::delete('asignaturas/{id}', [AsignaturaController::class, 'destroy']);
         Route::post('asignaturas/cambiarEstado', [AsignaturaController::class, 'cambiarEstado']);
         Route::get('asignaturas-activos', [AsignaturaController::class, 'activos']);
         Route::get('asignaturas/tipos/{id}', [AsignaturaController::class, 'getByTipoAsignatura']);


?>

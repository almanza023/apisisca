<?php

use App\Http\Controllers\V1\TipoAsignaturaController;
use Illuminate\Support\Facades\Route;

        Route::get('tipoasignaturas', [TipoAsignaturaController::class, 'index']);
         Route::post('tipoasignaturas', [TipoAsignaturaController::class, 'store']);
         Route::get('tipoasignaturas/{id}', [TipoAsignaturaController::class, 'show']);
         Route::put('tipoasignaturas/{id}', [TipoAsignaturaController::class, 'update']);
         Route::delete('tipoasignaturas/{id}', [TipoAsignaturaController::class, 'destroy']);
         Route::post('tipoasignaturas/cambiarEstado', [TipoAsignaturaController::class, 'cambiarEstado']);
         Route::get('tipoasignaturas-activos', [TipoAsignaturaController::class, 'activos']);

?>

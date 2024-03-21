<?php

use App\Http\Controllers\V1\SedeController;
use Illuminate\Support\Facades\Route;

        Route::get('sedes', [SedeController::class, 'index']);
        Route::post('sedes', [SedeController::class, 'store']);
        Route::get('sedes/{id}', [SedeController::class, 'show']);
        Route::put('sedes/{id}', [SedeController::class, 'update']);
        Route::delete('sedes/{id}', [SedeController::class, 'destroy']);
        Route::post('sedes/cambiarEstado', [SedeController::class, 'cambiarEstado']);
        Route::get('sedes-activos', [SedeController::class, 'activos']);

?>

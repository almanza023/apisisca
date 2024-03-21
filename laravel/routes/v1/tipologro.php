<?php

use App\Http\Controllers\V1\TipoLogroController;
use Illuminate\Support\Facades\Route;

        Route::get('tipologros', [TipoLogroController::class, 'index']);
        Route::post('tipologros', [TipoLogroController::class, 'store']);
        Route::get('tipologros/{id}', [TipoLogroController::class, 'show']);
        Route::put('tipologros/{id}', [TipoLogroController::class, 'update']);
        Route::delete('tipologros/{id}', [TipoLogroController::class, 'destroy']);
        Route::post('tipologros/cambiarEstado', [TipoLogroController::class, 'cambiarEstado']);
        Route::get('tipologros-activos', [TipoLogroController::class, 'activos']);

?>

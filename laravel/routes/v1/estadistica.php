<?php

use App\Http\Controllers\V1\EstadisticaController;
use Illuminate\Support\Facades\Route;

        Route::get('estadisticas/grados', [EstadisticaController::class, 'getTotalEstudianteByGrado']);
       
        Route::get('estadisticas/sedes', [EstadisticaController::class, 'getTotalEstudianteBySede']);
        Route::get('estadisticas/contadores', [EstadisticaController::class, 'getContadores']);
        Route::get('estadisticas/contadoresDocentes/{id}', [EstadisticaController::class, 'getContadoresDocente']);

?>

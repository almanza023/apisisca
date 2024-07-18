<?php

use App\Http\Controllers\V1\Reportes\BoletinController;
use App\Http\Controllers\V1\Reportes\BoletinPreescolarController;
use App\Http\Controllers\V1\Reportes\ReporteController;
use Illuminate\Support\Facades\Route;

        Route::post('reportes/boletin-periodo', [BoletinController::class, 'boletines']);
        Route::post('reportes/calificaciones', [ReporteController::class, 'ReporteNotas']);
        Route::post('reportes/matriculas', [ReporteController::class, 'ReporteMatriculas']);
        Route::post('reportes/consolidado-periodo', [BoletinController::class, 'consolidados']);
        Route::post('reportes/boletin-preescolar', [BoletinPreescolarController::class, 'boletines']);
        Route::post('reportes/estadisticas-periodo', [ReporteController::class, 'ReportEstadisticas']);
        Route::post('reportes/area-periodo', [ReporteController::class, 'ReportAreas']);

        Route::post('reportes/valoraciones', [ReporteController::class, 'ReporteValoraciones']);
        Route::post('reportes/nivelaciones', [ReporteController::class, 'ReporteNivelaciones']);

?>

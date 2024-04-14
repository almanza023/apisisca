<?php

use App\Http\Controllers\V1\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    //Prefijo V1, todo lo que este dentro de este grupo se accedera escribiendo v1 en el navegador, es decir /api/v1/*

        require __DIR__.'/v1/auth.php';

    Route::group(['middleware' => ['jwt.verify']], function() {
        //Todo lo que este dentro de este grupo requiere verificaci��n de usuario.

        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('get-user', [AuthController::class, 'getUser']);
        //Grados
        require __DIR__.'/v1/grados.php';
        //Sede
        require __DIR__.'/v1/sede.php';
        //Tipo Logros
        require __DIR__.'/v1/tipologro.php';
         //Tipo Asignaturas
         require __DIR__.'/v1/tipoasignatura.php';
         //Periodos
         require __DIR__.'/v1/periodo.php';
         //Asignaturas
         require __DIR__.'/v1/asignatura.php';
          //Docentes
         require __DIR__.'/v1/docente.php';
          //Carga Academica
          require __DIR__.'/v1/carga.php';
         //Matriculas
         require __DIR__.'/v1/matricula.php';
         //Calificaciones
         require __DIR__.'/v1/calificacion.php';
          //Usuarios
          require __DIR__.'/v1/usuario.php';
         //Logros Academicos
         require __DIR__.'/v1/logros-academicos.php';
          //Logros Academicos
          require __DIR__.'/v1/direcciongrado.php';
           //Apertura Periodo
           require __DIR__.'/v1/apertura.php';
          //Logros Disciplinarios
          require __DIR__.'/v1/logros-disciplinarios.php';
           //Convivencia Escolar
           require __DIR__.'/v1/convivencia.php';
            //Logros Preescolar
          require __DIR__.'/v1/logros-preescolar.php';
           //Preescolar
           require __DIR__.'/v1/preescolar.php';
           //reportes
           require __DIR__.'/v1/reportes.php';
           //Estadisticas
           require __DIR__.'/v1/estadistica.php';

    });
});

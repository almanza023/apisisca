<?php

namespace App\Models\Reportes;

use App\Models\Calificacion;
use App\Models\CargaAcademica;
use App\Models\Matricula;
use App\Models\Nivelacion;
use App\Models\Prefijo;
use App\Models\Puesto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletinFinalUno extends Model
{


    public static function reporte($sede, $grado, $pdf){

        $matriculas=Matricula::listado($sede, $grado);
        $num1 = count($matriculas);
        $i    = 1;
        $pdf = app('Fpdf');

        while ($i <= $num1) {
            foreach($matriculas as $matricula){
                Auxiliar::cabecera($pdf, $matricula, 'FINAL');
                $ac=0;
                $act=0;
                $c5=0;
                $ac5=0;
                $nota=0;
                $acil=0;
                $acl=0;
                $c2=0;
                $ganadas=0;
                $perdidas=0;
                $niveladas=0;
                $areasg=0;
                $areasp=0;
                //$calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 1, '');

            }
            $pdf->Output();
            exit;
            $i++;

    }
}







}

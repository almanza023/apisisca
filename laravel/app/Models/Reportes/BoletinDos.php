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

class BoletinDos extends Model
{

    public static function reporte($sede, $grado, $periodo, $pdf)
    {
        switch ($periodo)
        {
            case '1':
                //Obtener el numero de estudiante
                $matriculas = Matricula::listado($sede, $grado);
                $num1 = count($matriculas);
                $filas = 0;
                $pdf = app('Fpdf');

                while ($filas <= $num1)
                {

                    foreach ($matriculas as $matricula)
                    {
                        Auxiliar::cabecera($pdf, $matricula, $periodo);
                        //matematicas
                        $acih = 0;
                        $ac = 0;
                        $proma = 0;
                        $acil = 0;
                        $acin = 0;
                        $proml = 0;
                        $acl = 0;
                        $c1 = 0;
                        $c2 = 0;
                        $c3 = 0;
                        $acs = 0;
                        $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 1, 1.3);
                        foreach ($calificaciones as $cal)
                        {
                            $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                            $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                            $notaN = 0;
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetX(18);
                            $nivelacionPeriodo = Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            if (!empty($nivelacionPeriodo) && $nivelacionPeriodo->nota > 0)
                            {
                                $notaN = $nivelacionPeriodo->nota;
                                $pdf->SetX(128);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J', 1);
                            }
                            $pdf->SetFillColor(255, 255, 255);
                            $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);
                            $iha = $carga->ihs;
                            $c1 = 1;
                            $pdf->SetX(10);
                            $acih = $acih + $iha;
                            $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                            $pdf->SetX(18);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre) . '     ' . utf8_decode($carga->porcentaje) . ' ' . utf8_decode("% ".$nombreDocente) , 1, 0, 'J', 1);
                            $nota_a = $cal->nota;
                            $por = $carga->porcentaje / 100;
                            if (empty($notaN))
                            {
                                $nt = round($nota_a * $por, 1, PHP_ROUND_HALF_DOWN);
                                $nota = $cal->nota;
                                $ac = $ac + $nt;
                            }
                            else
                            {
                                $nt = round($notaN * $por, 1);
                                $ac = $ac + $nt;
                            }
                            $pdf->SetX(112);
                            $pdf->Cell(16, 6, $nota, 1, 0, 'J');
                            $pdf->SetX(112);
                            $pdf->SetFont('Arial', 'B', 8);
                            $proma = $nota;
                            if (empty($notaN))
                            {
                                $pdf->SetX(192);
                                $pdf->SetFont('Arial', '', 8);
                                $pdf->Cell(16, 6, $proma, 1, 1, 'J');
                            }
                            else
                            {
                                $pdf->SetX(192);
                                $pdf->SetFont('Arial', '', 8);
                                $pdf->Cell(16, 6, $notaN, 1, 1, 'J');
                            }
                            //Parte de Logros
                            if ($nota_a >= 0 && $nota_a <= 2.99)
                            {
                                $prefijo = Prefijo::find(1);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;

                            }
                            elseif ($nota_a >= 3 && $nota_a <= 3.99)
                            {
                                $prefijo = Prefijo::find(2);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            elseif ($nota_a >= 4 && $nota_a <= 4.49)
                            {
                                $prefijo = Prefijo::find(3);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            else
                            {
                                $prefijo = Prefijo::find(4);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            $pdf->SetFont('Arial', '', 9);
                            $pdf->SetFillColor(255, 255, 255);
                            if (!empty($cal
                                ->logroCognitivo
                                ->descripcion))
                            {
                                $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal
                                    ->logroCognitivo
                                    ->descripcion) . " " . utf8_decode($su1) , 1, 1, 'J', true);
                            }
                            if (!empty($cal
                                ->logroAfectivo
                                ->descripcion))
                            {
                                $pdf->MultiCell('198', '5', utf8_decode($cal
                                    ->logroAfectivo
                                    ->descripcion) , 1, 1, 'J');
                            }
                        }

                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetX(10);
                        $pdf->Cell(8, 6, $acih, 1, 0, 'C');
                        $pdf->SetX(18);
                        $pdf->SetFillColor(232, 232, 232);
                        $pdf->Cell(94, 6, "MATEMATICAS" . '    ' . utf8_decode('100%') , 1, 0, 'J', 1);
                        $pdf->SetX(112);
                        $pdf->Cell(16, 6, $ac, 1, 0, 'J', 1);
                        $pdf->SetX(192);
                        $pdf->SetFont('Arial', 'B', 8);
                        $ac=round($ac, 1,PHP_ROUND_HALF_DOWN );
                        $pdf->Cell(16, 6, $ac , 1, 1, 'J');

                        //OTRAS MATERIAS
                        $act = 0;
                        $c5 = 0;
                        $ac5 = 0;
                        $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 2, '');
                        foreach ($calificaciones as $cal)
                        {
                            $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                            $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                            $c5++;
                            $notaN = 0;
                            $ac5 = $ac5 + $c5;
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetX(18);
                            $nivelacionPeriodo = Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if (!empty($nivelacionPeriodo) && $nivelacionPeriodo->nota > 0)
                            {
                                $notaN = $nivelacionPeriodo->nota;
                                $pdf->SetX(128);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J', 1);
                            }
                            $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);
                            $pdf->SetX(10);
                            $iha = $carga->ihs;
                            $acil = $acil + $iha;
                            $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                            $pdf->SetX(18);
                            $pdf->SetFont('Arial', 'B', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(94, 6, utf8_decode($cal
                                ->asignatura
                                ->nombre) . ' ' . utf8_decode($carga->porcentaje) . '' . utf8_decode("% ".$nombreDocente) , 1, 0, 'J', 1);
                            $nota_a = $cal->nota;
                            $por = $carga->porcentaje / 100;
                            if (empty($notaN))
                            {
                                $nt = round($nota_a * $por, 1, PHP_ROUND_HALF_DOWN);
                                $nota = $cal->nota;
                                $act = $act + $nt;
                            }
                            else
                            {
                                $nt = round($notaN * $por, 1, PHP_ROUND_HALF_DOWN);
                                $act = $act + $nt;
                            }
                            $pdf->SetX(112);
                            $pdf->Cell(16, 6, $nota_a, 1, 0, 'J', 1);
                            $proma = $nota;
                            if (empty($notaN))
                            {
                                $pdf->SetX(192);
                                $pdf->SetFont('Arial', 'B', 8);
                                $pdf->Cell(16, 6, $proma, 1, 1, 'J');
                            }
                            else
                            {
                                $pdf->SetFont('Arial', 'B', 8);
                                $pdf->SetX(192);

                                $pdf->Cell(16, 6, $notaN, 1, 1, 'J');
                            }
                            //Parte de Logros
                            if ($nota_a >= 0 && $nota_a <= 2.99)
                            {
                                $prefijo = Prefijo::find(1);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;

                            }
                            elseif ($nota_a >= 3 && $nota_a <= 3.99)
                            {
                                $prefijo = Prefijo::find(2);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            elseif ($nota_a >= 4 && $nota_a <= 4.49)
                            {
                                $prefijo = Prefijo::find(3);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            else
                            {
                                $prefijo = Prefijo::find(4);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            $pdf->SetFont('Arial', '', 9);
                            $pdf->SetFillColor(255, 255, 255);

                            if (!empty($cal
                                ->logroCognitivo
                                ->descripcion))
                            {
                                $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal
                                    ->logroCognitivo
                                    ->descripcion) . " " . utf8_decode($su1) , 1, 1, 'J', true);
                            }
                            if (!empty($cal
                                ->logroAfectivo
                                ->descripcion))
                            {
                                $pdf->MultiCell('198', '5', utf8_decode($cal
                                    ->logroAfectivo
                                    ->descripcion) , 1, 1, 'J');
                            }

                        }
                        Auxiliar::disciplina($pdf, $matricula, $periodo);
                        $tac = $ac + $acl + $acs + $act;
                        $pdf->SetFillColor(232, 232, 232);
                        $tc = $c1 + $c2 + $c3 + $c5;
                        $promg = $tac / $tc;
                        $nprom = round($promg, 1, PHP_ROUND_HALF_DOWN);
                        Puesto::updateOrCreate(['matricula_id' => $matricula->id, 'periodo_id' => $periodo], ['matricula_id' => $matricula->id, 'periodo_id' => $periodo, 'promedio' => $nprom],);
                        if ($nprom >= 1 && $nprom <= 2.99)
                        {
                            $des = "DESEMPEÑO BAJO";
                        }
                        elseif ($nprom >= 3 && $nprom <= 3.99)
                        {
                            $des = "DESEMPEÑO BÁSICO";
                        }
                        elseif ($nprom >= 4 && $nprom <= 4.49)
                        {
                            $des = "DESEMPEÑO ALTO";
                        }
                        elseif ($nprom >= 4.5)
                        {
                            $des = "DESEMPEÑO SUPERIOR";
                        }
                        else
                        {
                            $des = "";
                        }
                        $pdf->SetFont('Arial', 'B', 9);
                        $posicion = 0;
                        $cont = 0;

                        $puestos = Puesto::getPuesto($sede, $grado, $periodo);
                        $total = count($puestos);
                        foreach ($puestos as $puesto)
                        {
                            $cont++;
                            if ($puesto->matricula_id == $matricula->id)
                            {
                                $posicion = $cont;
                                break;
                            }
                        }
                        $pdf->Cell(198, 6, ' PROMEDIO DE PERIODO: ' . $nprom . '        ' . utf8_decode($des) , 1, 1, 'J', 1);
                        $pdf->Cell(198, 6, ' PUESTO: ' . $posicion . '  DE ' . $total, 1, 1, 'J', 1);
                        Auxiliar::footer($pdf, $grado, $sede);
                        $cal='';
                    }
                    //$pdf->Output();
                    $base64String = chunk_split(base64_encode($pdf->Output('S')));
                    return $base64String;
                    exit;
                    $filas++;
                }

            break;

            case '2':
                //Obtener el numero de estudiante
                $matriculas = Matricula::listado($sede, $grado);
                $num1 = count($matriculas);
                $filas = 0;
                $pdf = app('Fpdf');

                while ($filas <= $num1)
                {

                    foreach ($matriculas as $matricula)
                    {
                        Auxiliar::cabecera($pdf, $matricula, $periodo);
                        //matematicas
                        $acih = 0;
                        $ac = 0;
                        $ac1=0;
                        $proma = 0;
                        $promac = 0;
                        $acil = 0;
                        $porc1 = 0;
                        $acl = 0;
                        $c1 = 0;
                        $c2 = 0;
                        $c3 = 0;
                        $acs = 0;
                        $niveladas=0;
                        $tac=0;
                        $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 1, 1.3);
                        foreach ($calificaciones as $cal)
                        {
                            $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                            $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                            $notaN = 0;
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetX(18);
                            //Nota periodo 1
                            $calPer1 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 1);
                            if (!empty($calPer1))
                            {
                                $notaP1 = $calPer1->nota;
                                $pdf->SetX(112);
                                $pdf->SetFont('Arial', '', 8);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaP1, 1, 0, 'J', 0);

                            }
                            if($cal->nota<3){
                                $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                                //Si existe una nota de nivelacion de periodo
                                if(!empty($nivelacionPeriodo) ){
                                    $notaN=$nivelacionPeriodo->nota;
                                    $pdf->SetX(144);
                                    $pdf->SetFont('Arial', 'B', 10);
                                    $pdf->SetFillColor(232, 232, 232);
                                    $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                                }
                            }
                            $pdf->SetFillColor(255, 255, 255);
                            $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);
                            $iha = $carga->ihs;
                            $c1 = 1;
                            $pdf->SetX(10);
                            $acih = $acih + $iha;
                            $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                            $pdf->SetX(18);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre) . '     ' . utf8_decode($carga->porcentaje) . ' ' . utf8_decode("% ".$nombreDocente) , 1, 0, 'J', 1);
                            $nota_a = $cal->nota;
                            $por = $carga->porcentaje / 100;
                            $porc1=$notaP1*$por;
                            $ac1=$ac1+$porc1;
                            if (empty($notaN))
                            {
                                $nt = round($nota_a * $por, 1,PHP_ROUND_HALF_DOWN);
                                $nota = $cal->nota;
                                $ac = $ac + $nt;
                                $pdf->SetX(128);
                                 $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                            }
                            else
                            {
                                $nt = round($notaN * $por, 1,PHP_ROUND_HALF_DOWN);
                                $nota=$cal->nota;
                                $ac = $ac + $nt;
                                $pdf->SetX(128);
                                $pdf->Cell(16, 6, $nota, 1, 0, 'J', 1);
                            }
                            $tac+=$nota;
                            if (empty($notaN) ) {
                                $proma=round(($nota+$notaP1)/2,1,PHP_ROUND_HALF_DOWN);
                               }else{
                                $niveladas++;
                                $proma=round(($notaN+$notaP1)/2,1,PHP_ROUND_HALF_DOWN);
                               }
                                $pdf->SetX(192);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->Cell(16, 6, $proma, 1, 1, 'C',1);

                            //Parte de Logros
                            if ($nota_a >= 0 && $nota_a <= 2.99)
                            {
                                $prefijo = Prefijo::find(1);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;

                            }
                            elseif ($nota_a >= 3 && $nota_a <= 3.99)
                            {
                                $prefijo = Prefijo::find(2);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            elseif ($nota_a >= 4 && $nota_a <= 4.49)
                            {
                                $prefijo = Prefijo::find(3);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            else
                            {
                                $prefijo = Prefijo::find(4);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            $pdf->SetFont('Arial', '', 9);
                            $pdf->SetFillColor(255, 255, 255);
                            if (!empty($cal
                                ->logroCognitivo
                                ->descripcion))
                            {
                                $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal
                                    ->logroCognitivo
                                    ->descripcion) . " " . utf8_decode($su1) , 1, 1, 'J', true);
                            }
                            if (!empty($cal
                                ->logroAfectivo
                                ->descripcion))
                            {
                                $pdf->MultiCell('198', '5', utf8_decode($cal
                                    ->logroAfectivo
                                    ->descripcion) , 1, 1, 'J');
                            }
                        }

                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetX(10);
                        $pdf->Cell(8, 6, $acih, 1, 0, 'C');
                        $pdf->SetX(18);
                        $pdf->SetFillColor(232, 232, 232);
                        $pdf->Cell(94, 6, "MATEMATICAS" . '    ' . utf8_decode('100%') , 1, 0, 'J', 1);
                        $pdf->SetX(112);
                        $pdf->Cell(16, 6, round($ac1,1,PHP_ROUND_HALF_DOWN), 1, 0, 'J', 0);
                        $pdf->SetX(128);
                        $ac=round($ac, 1,PHP_ROUND_HALF_DOWN);
                        $pdf->Cell(16, 6, $ac, 1, 0, 'J', 1);
                        if($ac>0 && $ac1>0){
                            $promac=round(($ac+$ac1)/2,1);
                        }
                        $pdf->SetX(192);
                        $pdf->Cell(16, 6, ($promac) , 1, 1, 'C',1);

                        //OTRAS MATERIAS
                        $act = 0;
                        $c5 = 0;
                        $ac5 = 0;
                        $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 2, '');
                        foreach ($calificaciones as $cal)
                        {
                            $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                            $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                            $c5++;
                            $notaN = 0;
                            $ac5 = $ac5 + $c5;
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetX(18);
                            $calPer1 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 1);
                            if (!empty($calPer1))
                            {
                                $notaP1 = $calPer1->nota;
                                $pdf->SetX(112);
                                $pdf->SetFont('Arial', '', 8);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaP1, 1, 0, 'J', 0);

                            }
                            if($cal->nota<3){
                                $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                                //Si existe una nota de nivelacion de periodo
                                if(!empty($nivelacionPeriodo) ){
                                    $notaN=$nivelacionPeriodo->nota;
                                    $pdf->SetX(144);
                                    $pdf->SetFont('Arial', 'B', 10);
                                    $pdf->SetFillColor(232, 232, 232);
                                    $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                                }
                            }
                            $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);
                            $pdf->SetX(10);
                            $iha = $carga->ihs;
                            $acil = $acil + $iha;
                            $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                            $pdf->SetX(18);
                            $pdf->SetFont('Arial', 'B', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre) . ' ' . utf8_decode($carga->porcentaje) . ' ' . utf8_decode("% ".$nombreDocente) , 1, 0, 'J', 1);
                            $por = $carga->porcentaje / 100;
                            if (empty($notaN))
                            {
                                $nt = round($nota_a * $por, 1,PHP_ROUND_HALF_DOWN);
                                $nota = $cal->nota;
                                $act = $act + $nota;
                                $pdf->SetX(128);
                                $pdf->Cell(16, 6, $nota, 1, 0, 'J', 1);
                            }
                            else
                            {
                                $nt = round($notaN * $por, 1,PHP_ROUND_HALF_DOWN);
                                $act = $act + $notaN;
                                $pdf->SetX(128);
                                $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 0);
                            }
                            if (empty($notaN) ) {
                                $proma=round(($nota+$notaP1)/2,1,PHP_ROUND_HALF_DOWN);
                               }else{
                                $niveladas++;
                                $proma=round(($notaN+$notaP1)/2,1,PHP_ROUND_HALF_DOWN);
                               }
                                $pdf->SetX(192);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->Cell(16, 6, $proma, 1, 1, 'C',1);
                            //Parte de Logros
                            if ($nota_a >= 0 && $nota_a <= 2.99)
                            {
                                $prefijo = Prefijo::find(1);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;

                            }
                            elseif ($nota_a >= 3 && $nota_a <= 3.99)
                            {
                                $prefijo = Prefijo::find(2);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            elseif ($nota_a >= 4 && $nota_a <= 4.49)
                            {
                                $prefijo = Prefijo::find(3);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            else
                            {
                                $prefijo = Prefijo::find(4);
                                $pre1 = $prefijo->prefijo;
                                $su1 = $prefijo->subfijo;
                            }
                            $pdf->SetFont('Arial', '', 9);
                            $pdf->SetFillColor(255, 255, 255);
                            if (!empty($cal
                                ->logroCognitivo
                                ->descripcion))
                            {
                                $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal
                                    ->logroCognitivo
                                    ->descripcion) . " " . utf8_decode($su1) , 1, 1, 'J', true);
                            }
                            if (!empty($cal
                                ->logroAfectivo
                                ->descripcion))
                            {
                                $pdf->MultiCell('198', '5', utf8_decode($cal
                                    ->logroAfectivo
                                    ->descripcion) , 1, 1, 'J');
                            }

                        }
                        Auxiliar::disciplina($pdf, $matricula, $periodo);
                        $tac =  $ac + $act;
                        $pdf->SetFillColor(232, 232, 232);
                        $tc = $c1 + $c2 + $c3 + $c5;
                          if($tc>0){
                                $promg = $tac/$tc;
                          }
                        $nprom = round($promg, 1,PHP_ROUND_HALF_DOWN);
                        Puesto::updateOrCreate(['matricula_id' => $matricula->id, 'periodo_id' => $periodo], ['matricula_id' => $matricula->id, 'periodo_id' => $periodo, 'promedio' => $nprom],);
                        if ($nprom >= 1 && $nprom <= 2.99)
                        {
                            $des = "DESEMPEÑO BAJO";
                        }
                        elseif ($nprom >= 3 && $nprom <= 3.99)
                        {
                            $des = "DESEMPEÑO BÁSICO";
                        }
                        elseif ($nprom >= 4 && $nprom <= 4.49)
                        {
                            $des = "DESEMPEÑO ALTO";
                        }
                        elseif ($nprom >= 4.5)
                        {
                            $des = "DESEMPEÑO SUPERIOR";
                        }
                        else
                        {
                            $des = "";
                        }
                        $pdf->SetFont('Arial', 'B', 9);
                        $posicion = 0;
                        $cont = 0;

                        $puestos = Puesto::getPuesto($sede, $grado, $periodo);
                        $total = count($puestos);
                        foreach ($puestos as $puesto)
                        {
                            $cont++;
                            if ($puesto->matricula_id == $matricula->id)
                            {
                                $posicion = $cont;
                                break;
                            }
                        }
                        $pdf->Cell(198, 6, ' PROMEDIO DE PERIODO: ' . $nprom . '        ' . utf8_decode($des) , 1, 1, 'J', 1);
                        $pdf->Cell(198, 6, ' PUESTO: ' . $posicion . '  DE ' . $total, 1, 1, 'J', 1);
                        Auxiliar::footer($pdf, $grado, $sede);
                    }
                    //$pdf->Output();
                    $base64String = chunk_split(base64_encode($pdf->Output('S')));
                    return $base64String;
                    exit;
                    $filas++;
                }

            break;
            case '3':
               //Obtener el numero de estudiante
               $matriculas = Matricula::listado($sede, $grado);
               $num1 = count($matriculas);
               $filas = 0;
               $pdf = app('Fpdf');

               while ($filas <= $num1)
               {

                   foreach ($matriculas as $matricula)
                   {
                       Auxiliar::cabecera($pdf, $matricula, $periodo);
                       //matematicas
                       $acih = 0;
                       $ac = 0;
                       $ac1=0;
                       $ac2=0;
                       $proma = 0;
                       $acil = 0;
                       $porc1 = 0;
                       $porc2 = 0;
                       $acl = 0;
                       $c1 = 0;
                       $c2 = 0;
                       $c3 = 0;
                       $acs = 0;
                       $niveladas=0;
                       $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 1, 1.3);
                       foreach ($calificaciones as $cal)
                       {
                        $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                        $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                           $notaN = 0;
                           $pdf->SetFont('Arial', '', 8);
                           $pdf->SetX(18);
                           //Nota periodo 1
                           $calPer1 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 1);
                           if (!empty($calPer1))
                           {
                               $notaP1 = $calPer1->nota;
                               $pdf->SetX(112);
                               $pdf->SetFont('Arial', '', 8);
                               $pdf->SetFillColor(232, 232, 232);
                               $pdf->Cell(16, 6, $notaP1, 1, 0, 'J', 0);
                           }
                           $calPer2 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 2);
                           if (!empty($calPer2))
                           {
                               $notaP2 = $calPer2->nota;
                               $pdf->SetX(128);
                               $pdf->SetFont('Arial', '', 8);
                               $pdf->SetFillColor(232, 232, 232);
                               $pdf->Cell(16, 6, $notaP2, 1, 0, 'J', 0);

                           }
                           if($cal->nota<3){
                               $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                               //Si existe una nota de nivelacion de periodo
                               if(!empty($nivelacionPeriodo) ){
                                   $notaN=$nivelacionPeriodo->nota;
                                   $pdf->SetX(160);
                                   $pdf->SetFont('Arial', 'B', 10);
                                   $pdf->SetFillColor(232, 232, 232);
                                   $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                               }
                           }
                           $pdf->SetFillColor(255, 255, 255);
                           $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);
                           $iha = $carga->ihs;
                           $c1 = 1;
                           $pdf->SetX(10);
                           $acih = $acih + $iha;
                           $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                           $pdf->SetX(18);
                           $pdf->SetFont('Arial', '', 8);
                           $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre) . '     ' . utf8_decode($carga->porcentaje) . ' ' . utf8_decode("% ".$nombreDocente) , 1, 0, 'J', 1);
                           $nota_a = $cal->nota;
                           $por = $carga->porcentaje / 100;
                           $porc1=$notaP1*$por;
                           $ac1=$ac1+$porc1;
                           $porc2=$notaP2*$por;
                           $ac2=$ac2+$porc2;
                           if (empty($notaN))
                           {
                               $nt = round($nota_a * $por, 1);
                               $nota = $cal->nota;
                               $ac = $ac + $nt;
                               $pdf->SetX(144);
                               $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                           }
                           else
                           {
                               $nt = round($notaN * $por, 1);
                               $ac = $ac + $nt;
                               $pdf->SetX(144);
                               $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                           }
                           if (empty($notaN) ) {
                               $proma=round(($nota+$notaP1+$notaP2)/3,1);
                              }else{
                               $niveladas++;
                               $proma=round(($notaN+$notaP1+$notaP2)/3,1);
                              }
                               $pdf->SetX(192);
                               $pdf->SetFont('Arial', 'B', 10);
                               $pdf->Cell(16, 6, $proma, 1, 1, 'C',1);

                           //Parte de Logros
                           if ($nota_a >= 0 && $nota_a <= 2.99)
                           {
                               $prefijo = Prefijo::find(1);
                               $pre1 = $prefijo->prefijo;
                               $su1 = $prefijo->subfijo;

                           }
                           elseif ($nota_a >= 3 && $nota_a <= 3.99)
                           {
                               $prefijo = Prefijo::find(2);
                               $pre1 = $prefijo->prefijo;
                               $su1 = $prefijo->subfijo;
                           }
                           elseif ($nota_a >= 4 && $nota_a <= 4.49)
                           {
                               $prefijo = Prefijo::find(3);
                               $pre1 = $prefijo->prefijo;
                               $su1 = $prefijo->subfijo;
                           }
                           else
                           {
                               $prefijo = Prefijo::find(4);
                               $pre1 = $prefijo->prefijo;
                               $su1 = $prefijo->subfijo;
                           }
                           $pdf->SetFont('Arial', '', 9);
                           $pdf->SetFillColor(255, 255, 255);
                           if (!empty($cal
                               ->logroCognitivo
                               ->descripcion))
                           {
                               $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal
                                   ->logroCognitivo
                                   ->descripcion) . " " . utf8_decode($su1) , 1, 1, 'J', true);
                           }
                           if (!empty($cal
                               ->logroAfectivo
                               ->descripcion))
                           {
                               $pdf->MultiCell('198', '5', utf8_decode($cal
                                   ->logroAfectivo
                                   ->descripcion) , 1, 1, 'J');
                           }
                       }

                       $pdf->SetFont('Arial', 'B', 8);
                       $pdf->SetX(10);
                       $pdf->Cell(8, 6, $acih, 1, 0, 'C');
                       $pdf->SetX(18);
                       $pdf->SetFillColor(232, 232, 232);
                       $pdf->Cell(94, 6, "MATEMATICAS" . '    ' . utf8_decode('100%') , 1, 0, 'J', 1);
                       $pdf->SetX(112);
                       $pdf->Cell(16, 6, round($ac1,1), 1, 0, 'J', 0);
                       $pdf->SetX(128);
                       $pdf->Cell(16, 6, round($ac2,1), 1, 0, 'J', 0);
                       $pdf->SetX(144);
                       $pdf->Cell(16, 6, ($ac), 1, 0, 'J', 1);
                       $promac=round(($ac+$ac1+$ac2)/3,1);

                       $pdf->SetX(192);
                       $pdf->Cell(16, 6, round($promac, 1) , 1, 1, 'C',1);

                       //OTRAS MATERIAS
                       $act = 0;
                       $c5 = 0;
                       $ac5 = 0;
                       $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 2, '');
                       foreach ($calificaciones as $cal)
                       {
                        $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                        $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                           $c5++;
                           $notaN = 0;
                           $ac5 = $ac5 + $c5;
                           $pdf->SetFont('Arial', '', 8);
                           $pdf->SetX(18);
                           $calPer1 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 1);
                           if (!empty($calPer1))
                           {
                               $notaP1 = $calPer1->nota;
                               $pdf->SetX(112);
                               $pdf->SetFont('Arial', '', 8);
                               $pdf->SetFillColor(232, 232, 232);
                               $pdf->Cell(16, 6, $notaP1, 1, 0, 'J', 0);
                           }
                           $calPer2 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 2);
                           if (!empty($calPer2))
                           {
                               $notaP2 = $calPer2->nota;
                               $pdf->SetX(128);
                               $pdf->SetFont('Arial', '', 8);
                               $pdf->SetFillColor(232, 232, 232);
                               $pdf->Cell(16, 6, $notaP2, 1, 0, 'J', 0);

                           }
                           if($cal->nota<3){
                               $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                               //Si existe una nota de nivelacion de periodo
                               if(!empty($nivelacionPeriodo) ){
                                   $notaN=$nivelacionPeriodo->nota;
                                   $pdf->SetX(160);
                                   $pdf->SetFont('Arial', 'B', 10);
                                   $pdf->SetFillColor(232, 232, 232);
                                   $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                               }
                           }
                           $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);
                           $pdf->SetX(10);
                           $iha = $carga->ihs;
                           $acil = $acil + $iha;
                           $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                           $pdf->SetX(18);
                           $pdf->SetFont('Arial', 'B', 8);
                           $pdf->SetFillColor(232, 232, 232);
                           $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre) . ' ' . utf8_decode($carga->porcentaje) . ' ' . utf8_decode("% ".$nombreDocente) , 1, 0, 'J', 1);
                           $nota_a = $cal->nota;
                           $por = $carga->porcentaje / 100;
                           if (empty($notaN))
                           {
                               $nt = round($nota_a * $por, 1);
                               $nota = $cal->nota;
                               $act = $act + $nt;
                               $pdf->SetX(144);
                               $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                           }
                           else
                           {
                               $nt = round($notaN * $por, 1);
                               $act = $act + $nt;
                               $pdf->SetX(128);
                               $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 0);
                           }

                           if (empty($notaN) ) {
                               $proma=round(($nota+$notaP1+$notaP2)/3,1);
                              }else{
                               $niveladas++;
                               $proma=round(($notaN+$notaP1+$notaP2)/3,1);
                              }
                               $pdf->SetX(192);
                               $pdf->SetFont('Arial', 'B', 10);
                               $pdf->Cell(16, 6, $proma, 1, 1, 'C',1);
                           //Parte de Logros
                           if ($nota_a >= 0 && $nota_a <= 2.99)
                           {
                               $prefijo = Prefijo::find(1);
                               $pre1 = $prefijo->prefijo;
                               $su1 = $prefijo->subfijo;

                           }
                           elseif ($nota_a >= 3 && $nota_a <= 3.99)
                           {
                               $prefijo = Prefijo::find(2);
                               $pre1 = $prefijo->prefijo;
                               $su1 = $prefijo->subfijo;
                           }
                           elseif ($nota_a >= 4 && $nota_a <= 4.49)
                           {
                               $prefijo = Prefijo::find(3);
                               $pre1 = $prefijo->prefijo;
                               $su1 = $prefijo->subfijo;
                           }
                           else
                           {
                               $prefijo = Prefijo::find(4);
                               $pre1 = $prefijo->prefijo;
                               $su1 = $prefijo->subfijo;
                           }
                           $pdf->SetFont('Arial', '', 9);
                           $pdf->SetFillColor(255, 255, 255);
                           if (!empty($cal
                               ->logroCognitivo
                               ->descripcion))
                           {
                               $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal
                                   ->logroCognitivo
                                   ->descripcion) . " " . utf8_decode($su1) , 1, 1, 'J', true);
                           }
                           if (!empty($cal
                               ->logroAfectivo
                               ->descripcion))
                           {
                               $pdf->MultiCell('198', '5', utf8_decode($cal
                                   ->logroAfectivo
                                   ->descripcion) , 1, 1, 'J');
                           }

                       }
                       Auxiliar::disciplina($pdf, $matricula, $periodo);
                       $tac = $ac + $acl + $acs + $act;
                       $pdf->SetFillColor(232, 232, 232);
                       $tc = $c1 + $c2 + $c3 + $c5;
                       $promg = $tac / $tc;
                       $nprom = round($promg, 1);
                       Puesto::updateOrCreate(['matricula_id' => $matricula->id, 'periodo_id' => $periodo], ['matricula_id' => $matricula->id, 'periodo_id' => $periodo, 'promedio' => $nprom],);
                       if ($nprom >= 1 && $nprom <= 2.99)
                       {
                           $des = "DESEMPEÑO BAJO";
                       }
                       elseif ($nprom >= 3 && $nprom <= 3.99)
                       {
                           $des = "DESEMPEÑO BÁSICO";
                       }
                       elseif ($nprom >= 4 && $nprom <= 4.49)
                       {
                           $des = "DESEMPEÑO ALTO";
                       }
                       elseif ($nprom >= 4.5)
                       {
                           $des = "DESEMPEÑO SUPERIOR";
                       }
                       else
                       {
                           $des = "";
                       }
                       $pdf->SetFont('Arial', 'B', 9);
                       $posicion = 0;
                       $cont = 0;

                       $puestos = Puesto::getPuesto($sede, $grado, $periodo);
                       $total = count($puestos);
                       foreach ($puestos as $puesto)
                       {
                           $cont++;
                           if ($puesto->matricula_id == $matricula->id)
                           {
                               $posicion = $cont;
                               break;
                           }
                       }
                       $pdf->Cell(198, 6, ' PROMEDIO DE PERIODO: ' . $nprom . '        ' . utf8_decode($des) , 1, 1, 'J', 1);
                       $pdf->Cell(198, 6, ' PUESTO: ' . $posicion . '  DE ' . $total, 1, 1, 'J', 1);
                       Auxiliar::footer($pdf, $grado, $sede);
                   }
                   //$pdf->Output();
                   $base64String = chunk_split(base64_encode($pdf->Output('S')));
                   return $base64String;
                   exit;
                   $filas++;
               }
            break;
            case '4':
               
            break;
            default:
                echo "error";
            break;
        }
        //$pdf->Output();
        exit;
    }

}


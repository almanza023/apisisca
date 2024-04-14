<?php

namespace App\Models\Reportes;

use App\Models\Calificacion;
use App\Models\CargaAcademica;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Nivelacion;
use App\Models\Prefijo;
use App\Models\Puesto;
use App\Models\Sede;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsolidadoCuatro extends Model
{


    public static function reporte($sede, $grado, $periodo, $pdf, $matriculas){
        $promedios=[];
        Auxiliar::headerConsolidados($sede, $grado, $periodo, $pdf);
        $pdf->Cell(80, 6, '', 0, 'C', 1);
        $pdf->Ln();
        $pdf->SetX(90);
        $pdf->Cell(30, 5, 'MATEMATICAS', 1, 0, 'C', 1);
        $pdf->SetX(120);
        $pdf->Cell(30, 5, 'HUMANIDADES', 1, 0, 'C', 1);
        $pdf->SetX(150);
        $pdf->Cell(40, 5, 'C. NATURALES', 1, 0, 'C', 1);
        $pdf->SetX(190);
        $pdf->Cell(30, 5, 'C. SOCIALES', 1, 0, 'C', 1);
        $pdf->Ln();
        $pdf->Cell(8, 5, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(72, 5, 'ESTUDIANTES', 1, 0, 'C', 1);
            $pdf->SetFont('Arial', 'B', 8);
    //Matematicas
        $acronimos=Calificacion::asignaturaCalificacion($grado, $periodo, 1, 2 );
        foreach ($acronimos as $item) {
                  # code...
                    $pdf->SetFont('Arial', 'B', 5.5);
                 $pdf->Cell(10, 5, utf8_decode($item->acronimo.' '.$item->porcentaje.'%'),  1, 0, 'J');
            }
            $pdf->Cell(10, 5, 'DEF',  1, 0, 'J');

        //Lenguaje
        $acronimos=Calificacion::asignaturaCalificacion($grado, $periodo, 2, 2.3 );
        foreach ($acronimos as $item) {
            if($item->porcentaje==100){
                $por=70;
            }else{
                $por=$item->porcentaje;
            }
                  # code...
                    $pdf->SetFont('Arial', 'B', 5.5);
                 $pdf->Cell(10, 5, utf8_decode($item->acronimo.' '.$por.'%'),  1, 0, 'J');
            }
            $pdf->Cell(10, 5, 'DEF',  1, 0, 'J');
        //Ciencias naturales
        $acronimos=Calificacion::asignaturaCalificacion($grado, $periodo, 4, 4.4 );
        foreach ($acronimos as $item) {
                  # code...
                    $pdf->SetFont('Arial', 'B', 5.5);
                 $pdf->Cell(10, 5, utf8_decode($item->acronimo.' '.$item->porcentaje.'%'),  1, 0, 'J');
            }
            $pdf->Cell(10, 5, 'DEF',  1, 0, 'J');
        //SOCIALES
        $acronimos=Calificacion::asignaturaCalificacion($grado, $periodo, 5, 5.5 );
        foreach ($acronimos as $item) {
                  # code...
                   $pdf->SetFont('Arial', 'B', 5.5);
                 $pdf->Cell(10, 5, utf8_decode($item->acronimo.' '.$item->porcentaje.'%'),  1, 0, 'J');
            }
            $pdf->Cell(10, 5, 'DEF',  1, 0, 'J');
         //Otras Materias
         $acronimos=Calificacion::asignaturaCalificacion($grado, $periodo, 5.8, 20 );
         foreach ($acronimos as $item) {
                   # code...
                 $pdf->SetFont('Arial', 'B', 5.5);
                 $pdf->Cell(10, 5, utf8_decode($item->acronimo.' '.$item->porcentaje.'%'),  1, 0, 'J');
             }
          $pdf->Cell(10, 5, 'PER',  1, 0, 'J');
        $pdf->Cell(10, 5, 'GAN',  1, 0, 'J');
        $pdf->Cell(10, 5, 'PROM',  1, 0, 'J', 1);
          $pdf->Ln();

        $i=1;
        foreach ($matriculas as $matricula) {
        $ac=0;
        $c1=0;
        $c2=0;
        $c3=0;
        $c4=0;
        $c5=0;
        $c6=0;
        $cp=0;
        $cg=0;
        $acom=0;
        $mat=0;
        $cn=0;
        $fil=0;
        $et=0;
        $len=0;
        $numn1=0;
        $notaNC=0;
        $notac=0;
        $notaf=0;
        $notaNf=0;
        $notal=0;
        $notaNl=0;
            $pdf->SetFont('Arial', '', 8);
            $idest=$matricula->id;
            $nom = ($matricula->apellidos.' '.$matricula->nombres);
            $pdf->Cell(8, 5, $i, 1, 0, 'J');
            $pdf->Cell(72, 5, utf8_decode($nom), 1, 0, 'J');
            $calificaciones=Calificacion::calificacionOrden($idest, $periodo, 1, 2 );



                foreach ( $calificaciones as $calificacion) {
                  $ida=$calificacion->asignatura_id;
                  $carga=CargaAcademica::getIhs($grado, $ida);
                    $por=($carga->porcentaje/100);
                    $c1=1;
                    $notam=$calificacion->nota;
                   $npm=round($notam*$por,1, PHP_ROUND_HALF_DOWN);
                    if (empty($notam)) {
                      $nulo=0;
                      $pdf->Cell(10, 5, $nulo, 1,0 , 'J');
                    }else   if($notam>=1 && $notam<=2.9){
                    $nivelacion=Nivelacion::getNivelacion($idest, $ida, $periodo);
                    if(!empty($nivelacion)){
                        $notaNm=$nivelacion->nota;
                        $ntpm=round($notaNm*$por,1, PHP_ROUND_HALF_DOWN);
                    }
                    }
                    if (!empty($notaNm)) {
                     $pdf->Cell(10, 5, $notaNm, 1,0 , 'J');
                     $mat=$mat+$ntpm;
                    } else {
                      $pdf->Cell(10, 5, $notam, 1,0 , 'J');
                     $mat=$mat+$npm;
                    }
                }
                $pdf->Cell(10, 5, $mat,  1, 0, 'J', 1);
                if($mat<3){
                  $cp=$cp+1;
                } else {
                  $cg=$cg+1;
                }

    //Lenguaje

    $calificaciones=Calificacion::calificacionOrden($idest, $periodo, 2, 2.3 );
    foreach ( $calificaciones as $calificacion) {
        $ida=$calificacion->asignatura_id;
        $carga=CargaAcademica::getIhs($grado, $ida);
                    $por=($carga->porcentaje/100);
                    $c2=1;
                    $notal=$calificacion->nota;
                    
                    if ($notal=='') {
                      $nulo=0;
                      $pdf->Cell(10, 5, $nulo, 1,0 , 'J');
                    }else if($notal>=1 && $notal<=2.9){
                        $nivelacion=Nivelacion::getNivelacion($idest, $ida, $periodo);
                        if(!empty($nivelacion)){
                            $notaNl=$nivelacion->nota;
                            $ntpl=round($notaNl*$por,1,PHP_ROUND_HALF_DOWN);
                        }
                    }
                    if (!empty($notaNl)) {
                     $pdf->Cell(10, 5, $notaNl, 1,0 , 'J',1);
                     $npl=round($notaNl*$por,1,PHP_ROUND_HALF_DOWN);
                     $len=$len+$npl;
                    } else {
                      $npl=round($notal*$por,1,PHP_ROUND_HALF_DOWN);
                      $len=$len+$npl;
                      $pdf->Cell(10, 5, $notal, 1,0 , 'J');
                    }
                }
                $pdf->Cell(10, 5, $len,  1, 0, 'J', 1);
                if($len<3){
                 $cp=$cp+1;
                } else {
                  $cg=$cg+1;
                }

    //Ciencias Naturales
    $calificaciones=Calificacion::calificacionOrden($idest, $periodo, 4, 4.4 );
    $numn4=count($calificaciones);
      if ($numn4>0) {
        foreach ( $calificaciones as $calificacion) {
            $ida=$calificacion->asignatura_id;
            $carga=CargaAcademica::getIhs($grado, $ida);
            $por=($carga->porcentaje/100);
                    $c3=1;
                    $notac=$calificacion->nota;
                    $npc=round($notac*$por,1,PHP_ROUND_HALF_DOWN);
                    if($notac>=1 && $notac<=2.9){
                        $nivelacion=Nivelacion::getNivelacion($idest, $ida, $periodo);
                        if(!empty($nivelacion)){
                            $notaNC=$nivelacion->nota;
                            $ntpc=round($notaNC*$por,1,PHP_ROUND_HALF_DOWN);
                        }
                    }
                    if (!empty($notaNC)) {
                     $pdf->Cell(10, 5, $notaNC, 1,0 , 'J');
                     $cn=$cn+$ntpc;
                    } else {
                      $cn=$cn+$npc;
                      $pdf->Cell(10, 5, $notac, 1,0 , 'J');
                    }
                }
                $pdf->Cell(10, 5, $cn,  1, 0, 'J', 1);
                if(round($cn,1)<3){
                  $cp=$cp+1;
                } else {
                 $cg=$cg+1;
                }
        }else {
           $pdf->Cell(10, 5, '0',  1, 0, 'J');
        }
    //sociales
    $calificaciones=Calificacion::calificacionOrden($idest, $periodo, 5, 5.5 );
    foreach ( $calificaciones as $calificacion) {
                  $ida=$calificacion->asignatura_id;
                  $carga=CargaAcademica::getIhs($grado, $ida);
                  $por=($carga->porcentaje/100);
                    $c4=1;
                    $notaf=$calificacion->nota;
                    $npf=round($notaf*$por,1,PHP_ROUND_HALF_DOWN);
                    if (empty($notaf)) {
                      $nota=0;
                      $pdf->Cell(10, 5, $nota, 1,0 , 'J');
                    }else if($notaf>=1 && $notaf<=2.9){
                        $nivelacion=Nivelacion::getNivelacion($idest, $ida, $periodo);
                        if(!empty($nivelacion)){
                            $notaNf=$nivelacion->nota;
                            $ntpf=round($notaNf*$por,1,PHP_ROUND_HALF_DOWN);
                        }

                    }
                    if (!empty($notaNf)) {
                     $pdf->Cell(10, 5, $notaNf, 1,0 , 'J');
                     $fil=$fil+$ntpf;

                    } else {
                      $pdf->Cell(10, 5, $notaf, 1,0 , 'J');
                     $fil=$fil+$npf;

                    }
                }
                $pdf->Cell(10, 5, $fil,  1, 0, 'J', 1);
                if($fil<3){
                 $cp=$cp+1;
                } else {
                  $cg=$cg+1;
                }

    //Otras Materias
                $numn1=0;
                $calificaciones=Calificacion::calificacionOrden($idest, $periodo, 5.8, 20 );
                $numn1=count($calificaciones);

        foreach ( $calificaciones as $calificacion) {
            $ida=$calificacion->asignatura_id;
            $carga=CargaAcademica::getIhs($grado, $ida);
                    $por=($carga->porcentaje/100);
                  $notaNo=0;
                  $notao=0;
                    $c6++;
                    $notao=$calificacion->nota;
                   if($notao>=1 && $notao<=2.9){
                    $nivelacion=Nivelacion::getNivelacion($idest, $ida, $periodo);
                    if(!empty($nivelacion)){
                        $notaNo=$nivelacion->nota;
                        $ntpo=$notaNo*$por;
                    }
                    } else {
                       $cg++;
                    }
                    if (!empty($notaNo)) {
                     $pdf->Cell(10, 5, $notaNo, 1,0 , 'J');
                     $acom=$acom+$notaNo;
                     if ($notaNo<3) {
                      $cp++;
                     }

                    } else {
                      $pdf->Cell(10, 5, $notao, 1,0 , 'J', 1);
                      $acom=$acom+$notao;
                     if ($notao<3) {
                      $cp++;
                     }
                    }
            }
            $sumaC=$c1+$c2+$c3+$c4+$c5+$c6;
            $prom=0;
            if($sumaC>0){
                $prom=($mat+$len+$cn+$fil+$et+$acom)/$sumaC;
            }

                $rprom=round($prom,1,PHP_ROUND_HALF_DOWN);
                $promedios[$nom]=($rprom );
                $pdf->Cell(10, 5, $cp, 1,0 , 'J',0);
                $pdf->Cell(10, 5, $cg, 1,0 , 'J',0);
          $pdf->Cell(10, 5, $rprom, 1,0 , 'J',1);

    $pdf->Ln();
    $i++;

        }

        $pdf->Ln();

        arsort($promedios);
        $a=0;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(92, 5, "PUESTOS", 1,1 , 'C',1);
        $pdf->SetFont('Arial', '', 8);
        $acval=0;
    foreach ($promedios as $key => $val) {
        $a++;
        $pdf->Cell(8, 5, $a, 1,0 , 'J',0);
        $pdf->Cell(72, 5, "$key ", 1,0 , 'J',0);
        $pdf->Cell(12, 5, "$val ", 1,1 , 'J',0);
        $acval=$acval+$val;
    }
    $promg=$acval/$a;
    $pdf->SetFont('Arial', 'B', 8);
    $rpromg=round($promg, 1);
    $pdf->Cell(80, 5, "PROMEDIO GENERAL DE GRADO ", 1,0 , 'C',1);
    $pdf->Cell(12, 5, "$rpromg ", 1,1 , 'C',0);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Output();
    exit;

    }






}

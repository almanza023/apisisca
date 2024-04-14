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

class BoletinTres extends Model
{

    public static function reporte($sede, $grado, $periodo, $pdf){
        $data=[];
        $base64String="";
        switch ($periodo) {
            case '1':
                $matriculas=Matricula::listado($sede, $grado);
                $num1 = count($matriculas);
                $fila    =0 ;
                $pdf = app('Fpdf');
                # code...
             foreach($matriculas as $matricula){
                    Auxiliar::cabecera($pdf, $matricula, $periodo);
                     //matematicas
                $acih=0;
                $ac=0;
                $proma=0;
                $acil=0;
                $acin=0;
                $proml=0;
                $acl=0;
                $c1=0;
                $c5=0;
                $ganadas=0;
                $perdidas=0;
                $niveladas=0;
                $areasg=0;
                $areasp=0;
                $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 1, 1.3);

                foreach($calificaciones as $cal){
                    $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                    $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                    $c5++;
                  $notaN=0;
                  $pdf->SetFont('Arial', '', 8);
                  $pdf->SetX(18);
                  $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if(!empty($nivelacionPeriodo) && $nivelacionPeriodo->nota>0){
                                $notaN=$nivelacionPeriodo->nota;
                                $pdf->SetX(128);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                            }
                    $c1=1;
                    $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);
                    $pdf->SetX(10);
                    $iha=$carga->ihs;
                    $acih+=$iha;
                    $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                    $pdf->SetX(18);
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                    $nota_a=$cal->nota;
                    $por=$carga->porcentaje/100;
                    if (empty($notaN)) {
                     $nt=round($nota_a*$por,1);
                     $nota=$cal->nota;
                     $ac=$ac+$nt;
                   } else {
                     $niveladas++;
                     $nt=round($notaN*$por,1);
                     $ac=$ac+$nt;
                   }
                   $pdf->SetX(112);
                   $pdf->Cell(16, 6, $nota_a, 1, 0, 'J');
                   $pdf->SetX(112);

                   $pdf->SetFont('Arial', 'B', 8);
                   $proma=$nota;
                   if (empty($notaN)) {
                    $pdf->SetX(192);
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->Cell(16, 6, $proma, 1, 1, 'J');
                  } else {
                   $pdf->SetX(192);
                   $pdf->SetFont('Arial', '', 8);
                   $pdf->Cell(16, 6, $notaN, 1, 1, 'J');
                  }
                //Parte de Logros
                if ($nota_a>=0 && $nota_a<=2.99) {
                    $perdidas++;
                    $prefijo=Prefijo::find(1);
                    $pre1=$prefijo->prefijo;
                    $su1=$prefijo->subfijo;

                } elseif ($nota_a>=3 && $nota_a<=3.99) {
                    $ganadas++;
                    $prefijo=Prefijo::find(2);
                    $pre1=$prefijo->prefijo;
                    $su1=$prefijo->subfijo;
                } elseif ($nota_a>=4 && $nota_a<=4.49) {
                    $ganadas++;
                    $prefijo=Prefijo::find(3);
                    $pre1=$prefijo->prefijo;
                    $su1=$prefijo->subfijo;
                } else {
                    $ganadas++;
                    $prefijo=Prefijo::find(4);
                    $pre1=$prefijo->prefijo;
                    $su1=$prefijo->subfijo;
                }
                $pdf->SetFont('Arial', '', 9);
                $pdf->SetFillColor(255, 255, 255);
               if(!empty( $cal->logroCognitivo->descripcion)){
                $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
               }
               if( !empty( $cal->logroAfectivo->descripcion)){
                $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
               }

            }
          $pdf->SetFont('Arial', 'B', 8);
          $pdf->SetX(10);
          $pdf->Cell(8, 6,$acih , 1, 0, 'C' );
          $pdf->SetX(18);
          $pdf->SetFillColor(232, 232, 232);
          $pdf->Cell(94, 6, "MATEMATICAS".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
          $pdf->SetX(112);
          $pdf->Cell(16, 6, $ac, 1, 0, 'J', 1);
          $pdf->SetX(192);
          $pdf->SetFont('Arial', 'B', 8);
          if(round($ac)>=3 && round($ac)<=5 ){
              $areasg++;
          }else{
              $areasp++;
          }
          $pdf->Cell(16, 6, round($ac,1), 1, 1, 'J');
          $c2=0;
        //Lenguaje
          $acl=0;
          $acil=0;
          $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 2, 2.2);
          foreach($calificaciones as $cal){
            $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);

            $c2=1;
            $notaN=0;
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetX(18);
            $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if(!empty($nivelacionPeriodo) && $nivelacionPeriodo->nota>0){
                                $notaN=$nivelacionPeriodo->nota;
                                $pdf->SetX(128);
                                $pdf->SetFont('Arial', 'B', 8);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                            }

                            $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);
                            $pdf->SetX(10);
                            $iha=$carga->ihs;
                            $acil+=$iha;
                            $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                            $pdf->SetX(18);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(255, 255, 255);
                            $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                            $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                            $nota_a=$cal->nota;
                            $por=$carga->porcentaje/100;
              if (empty($notaN)) {
               $nt=round($nota_a*$por,1);
               $nota=$cal->nota;
               $acl=$acl+$nt;
             } else {
               $nt=round($notaN*$por,1);
               $acl=$acl+$nt;
             }
             $pdf->SetX(112);
             $pdf->Cell(16, 6, $nota_a, 1, 0, 'J');
             $pdf->SetX(112);
             $pdf->SetFont('Arial', 'B', 8);
             $proma=$nota;

             if (empty($notaN)) {
              $pdf->SetX(192);
              $pdf->SetFont('Arial', '', 8);
              $pdf->Cell(16, 6, $proma, 1, 1, 'J');
            } else {
            $niveladas++;
             $pdf->SetX(192);
             $pdf->SetFont('Arial', '', 8);
             $pdf->Cell(16, 6, $notaN, 1, 1, 'J');
           }
          //Parte de Logros
          if ($nota_a>=0 && $nota_a<=2.99) {
              $perdidas++;
            $prefijo=Prefijo::find(1);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;

        } elseif ($nota_a>=3 && $nota_a<=3.99) {
            $ganadas++;
            $prefijo=Prefijo::find(2);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        } elseif ($nota_a>=4 && $nota_a<=4.49) {
            $ganadas++;
            $prefijo=Prefijo::find(3);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        } else {
            $ganadas++;
            $prefijo=Prefijo::find(4);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        }
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255);
       if(!empty( $cal->logroCognitivo->descripcion)){
        $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
       }
       if( !empty( $cal->logroAfectivo->descripcion)){
        $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
       }

    }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->Cell(8, 6,$acil , 1, 0, 'C' );
        $pdf->SetX(18);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, "HUMANIDADES".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
        $pdf->SetX(112);
        $pdf->Cell(16, 6, $acl, 1, 0, 'J', 1);
        $pdf->SetX(192);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, $acl, 1, 1, 'J',1);
        if(($acl)>=3 && ($acl)<=5 ){
            $areasg++;
        }else{
            $areasp++;
        }

        //CIENCIAS NATURALES
        $acn=0;
        $c3=0;
        $acin=0;
        $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 4, 4.3);
        foreach($calificaciones as $cal){
            $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
            $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
          $c3=1;
          $notaN=0;
          $pdf->SetFont('Arial', '', 8);
                            $pdf->SetX(18);
                            $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if(!empty($nivelacionPeriodo) && $nivelacionPeriodo->nota>0){
                                $notaN=$nivelacionPeriodo->nota;
                                $pdf->SetX(128);
                                $pdf->SetFont('Arial', 'B', 8);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                            }
                            $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);
                            $pdf->SetX(10);
                            $iha=$carga->ihs;
                            $acin+=$iha;
                            $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                            $pdf->SetX(18);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(255, 255, 255);
                            $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                            $nota_a=$cal->nota;
                            $por=$carga->porcentaje/100;
            if (empty($notaN)) {
             $nt=round($nota_a*$por,1);
             $nota=$cal->nota;
             $acn=$acn+$nt;
           } else {
             $niveladas++;
             $nt=round($notaN*$por,1);
             $acn=$acn+$nt;
           }
           $pdf->SetX(112);
           $pdf->Cell(16, 6, $nota_a, 1, 0, 'J');
           $pdf->SetX(112);

           $pdf->SetFont('Arial', 'B', 8);
           $proma=$nota;

           if (empty($notaN)) {
             $pdf->SetX(192);
             $pdf->SetFont('Arial', '', 8);
             $pdf->Cell(16, 6, $proma, 1, 1, 'J');
           } else {
             $pdf->SetX(192);
             $pdf->SetFont('Arial', '', 8);
             $pdf->Cell(16, 6, $notaN, 1, 1, 'J');
           }

           //Parte de Logros
           if ($nota_a>=0 && $nota_a<=2.99) {
            $perdidas++;
            $prefijo=Prefijo::find(1);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;

        } elseif ($nota_a>=3 && $nota_a<=3.99) {
            $ganadas++;
            $prefijo=Prefijo::find(2);
            $pre1=$prefijo->prefijo;
           $su1=$prefijo->subfijo;
        } elseif ($nota_a>=4 && $nota_a<=4.49) {
            $ganadas++;
            $prefijo=Prefijo::find(3);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        } else {
            $ganadas++;
            $prefijo=Prefijo::find(4);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        }
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255);
       if(!empty( $cal->logroCognitivo->descripcion)){
        $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
       }
       if( !empty( $cal->logroAfectivo->descripcion)){
        $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
       }

        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->Cell(8, 6,$acin , 1, 0, 'C' );
        $pdf->SetX(18);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, "CIENCIAS NATURALES".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
        $pdf->SetX(112);
        $pdf->Cell(16, 6, $acn, 1, 0, 'J', 1);
        $pdf->SetX(192);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, $acn, 1, 1, 'J');
        if(($acn)>=3 && ($acn)<=5 ){
            $areasg++;
        }else{
            $areasp++;
        }
                 //CIENCIAS SOCIALES
        $acs=0;
        $acis=0;
        $c4=0;
        $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 5, 5.5);
        foreach($calificaciones as $cal){
            $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
            $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
          $c4=1;
          $notaN=0;
          $pdf->SetFont('Arial', '', 8);
          $pdf->SetX(18);
          $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if(!empty($nivelacionPeriodo) && $nivelacionPeriodo->nota>0){
                                $notaN=$nivelacionPeriodo->nota;
                                $pdf->SetX(128);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                            }
                            $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);

            $pdf->SetX(10);
            $iha=$carga->ihs;
            $acis+=$iha;
            $pdf->Cell(8, 6, $iha, 1, 0, 'C');
            $pdf->SetX(18);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
            $nota_a=$cal->nota;
            $por=$carga->porcentaje/100;
            if (empty($notaN) ) {
             $nt=round($nota_a*$por,1);
             $nota=$cal->nota;
             $acs=$acs+$nt;
           } else {
             $nt=round($notaN*$por,1);
             $acs=$acs+$nt;
           }
           $pdf->SetX(112);
           $pdf->Cell(16, 6, $nota_a, 1, 0, 'J');
           $pdf->SetX(112);
           $pdf->SetFont('Arial', 'B', 8);
           $proma=$nota;

           if (empty($notaN) ) {
            $pdf->SetX(192);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(16, 6, $proma, 1, 1, 'J');
          } else {
            $niveladas++;
            $pdf->SetX(192);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(16, 6, $notaN, 1, 1, 'J');
          }

          //Parte de Logros
          if ($nota_a>=0 && $nota_a<=2.99) {
            $perdidas++;
            $prefijo=Prefijo::find(1);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;

        } elseif ($nota_a>=3 && $nota_a<=3.99) {
            $ganadas++;
            $prefijo=Prefijo::find(2);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        } elseif ($nota_a>=4 && $nota_a<=4.49) {
            $ganadas++;
            $prefijo=Prefijo::find(3);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        } else {
            $ganadas++;
            $prefijo=Prefijo::find(4);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        }
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255);
       if(!empty( $cal->logroCognitivo->descripcion)){
        $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
       }
       if( !empty( $cal->logroAfectivo->descripcion)){
        $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
       }
    }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->Cell(8, 6,$acis , 1, 0, 'C' );
        $pdf->SetX(18);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, "CIENCIAS SOCIALES".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
        $pdf->SetX(112);
        $pdf->Cell(16, 6, $acs, 1, 0, 'J', 1);
        $pdf->SetX(192);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, $acs, 1, 1, 'J');
        if(($acs)>=3 && ($acs)<=5 ){
            $areasg++;
        }else{
            $areasp++;
        }
        //OTRAS MATERIAS
        $act=0;
        $c5=0;
        $ac5=0;
        $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 5.9, '');
        foreach($calificaciones as $cal){
            $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
            $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
         $c5++;
         $notaN=0;
         $ac5=$ac5+$c5;
         $pdf->SetFont('Arial', '', 8);
         $pdf->SetX(18);
         $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if(!empty($nivelacionPeriodo) && $nivelacionPeriodo->nota>0){
                                $notaN=$nivelacionPeriodo->nota;
                                $pdf->SetX(128);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                            }
                            $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);
          $pdf->SetX(10);
          $iha=$carga->ihs;
          $acil=$acil+$iha;
          $pdf->Cell(8, 6, $iha, 1, 0, 'C');
          $pdf->SetX(18);
          $pdf->SetFont('Arial', 'B', 8);
          $pdf->SetFillColor(232, 232, 232);
          $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
          $nota_a=$cal->nota;
          $por=$carga->porcentaje/100;
          if (empty($notaN) ) {
           $nt=round($nota_a*$por,1);
           $nota=$cal->nota;
           $acs=$acs+$nt;
         } else {
           $nt=round($notaN*$por,1);
           $ac=$act+$nt;
         }
         $pdf->SetX(112);
         $pdf->Cell(16, 6, $nota_a, 1, 0, 'J', 1);
         $pdf->SetX(112);
         $proma=$nota;

         if (empty($notaN) ) {
           $pdf->SetX(192);
           $pdf->SetFont('Arial', 'B', 8);

           $pdf->Cell(16, 6, $proma, 1, 1, 'J');
         } else {
          $niveladas++;
          $pdf->SetFont('Arial', 'B', 8);
          $pdf->SetX(192);

          $pdf->Cell(16, 6, $notaN, 1, 1, 'J');
        }
        //Parte de Logros
        if ($nota_a>=0 && $nota_a<=2.99) {
            $perdidas++;
            $areasp++;
            $prefijo=Prefijo::find(1);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;

        } elseif ($nota_a>=3 && $nota_a<=3.99) {
            $ganadas++;
            $areasg++;
            $prefijo=Prefijo::find(2);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        } elseif ($nota_a>=4 && $nota_a<=4.49) {
            $ganadas++;
            $areasg++;

            $prefijo=Prefijo::find(3);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        } else {
            $ganadas++;
            $areasg++;
            $prefijo=Prefijo::find(4);
            $pre1=$prefijo->prefijo;
            $su1=$prefijo->subfijo;
        }
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255);
       if(!empty( $cal->logroCognitivo->descripcion)){
        $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
       }
       if( !empty( $cal->logroAfectivo->descripcion)){
        $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
       }

        }


        Auxiliar::disciplina($pdf, $matricula, $periodo );
        $pdf->SetFillColor(232, 232, 232);
        $tac=$ac+$acl+$acn+$acs+$act;
        $tc=$c1+$c2+$c3+$c4+$c5;
        $promg=0;
        if($tac>0 && $tc>0){
          $promg=$tac/$tc;
        }

        $nprom=round($promg, 1);
        $nom = $matricula->apellidos.' '.$matricula->nombres;
        Puesto::updateOrCreate(
            ['matricula_id' => $matricula->id, 'periodo_id' => $periodo],
            ['matricula_id' => $matricula->id, 'periodo_id' => $periodo,
            'promedio'=>$nprom,
            'ganadas'=>$ganadas,
            'perdidas'=>$perdidas,
            'areasp'=>$areasp,
            'areasg'=>$areasg,
            'niveladas'=>$niveladas]
        );
        if ($nprom>=1 && $nprom<=2.99) {
          $des="DESEMPEÑO BAJO";
        } elseif ($nprom>=3 && $nprom<=3.99) {
         $des="DESEMPEÑO BÁSICO";
        }elseif ($nprom>=4 && $nprom<=4.49) {
         $des="DESEMPEÑO ALTO";
        } elseif($nprom>=4.5){
          $des="DESEMPEÑO SUPERIOR";
        }
        else {
         $des="";
        }
        $posicion=0;
                                $cont=0;

                                $puestos=Puesto::getPuesto($matricula->sede_id, $grado, $periodo);
                                $total=count($puestos);
                                foreach ($puestos as $puesto) {
                                    $cont++;
                                    if($puesto->matricula_id==$matricula->id){
                                        $posicion=$cont;
                                        break;
                                    }
                                }
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->Cell(198, 6, ' PROMEDIO DE PERIODO: '.$nprom.'   '.utf8_decode($des), 1, 1, 'J', 1);
                                $pdf->Cell(198, 6, ' PUESTO: '.$posicion. '  DE '.$total, 1, 1, 'J', 1);
                                Auxiliar::footer($pdf, $grado, $sede);


        }
        break;
                    //periodo
        case '2':
            $matriculas=Matricula::listado($sede, $grado);
            $num1 = count($matriculas);
            $fila    =0 ;
            $pdf = app('Fpdf');
            # code...
         foreach($matriculas as $matricula){
            Auxiliar::cabecera($pdf, $matricula, $periodo);
            //matematicas
            $acih=0;
            $ac=0;
            $proma=0;
            $acil=0;
            $acin=0;
            $proml=0;
            $acl=0;
            $c1=0;
            $c5=0;
            $ganadas=0;
            $perdidas=0;
            $niveladas=0;
            $areasg=0;
            $areasp=0;
            $notaP1=0;
            $ac1=0;
            $ac2=0;
            $ac3=0;
            $ac4=0;
            $promac=0;
            $promac1=0;
            $porc1=0; $porc2=0; $porc3=0; $porc4=0;
            $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 1, 1.3);

            foreach($calificaciones as $cal){
                $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                $notaP1=0;
              $c5++;
              $notaN=0;
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
                $c1=1;
                $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);
                $pdf->SetX(10);
                $iha=$carga->ihs;
                $acih+=$iha;
                $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                $pdf->SetX(18);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetFillColor(255, 255, 255);
                $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).'     '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                $nota_a=$cal->nota;
                $por=$carga->porcentaje/100;
                $porc1=$notaP1*$por;
                $ac1=$ac1+$porc1;
                if (empty($notaN)) {
                 $nt=round($nota_a*$por,1,PHP_ROUND_HALF_DOWN);
                 $nota=$cal->nota;
                 $ac=$ac+$nt;
                 $pdf->SetX(128);
                 $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
               } else {
                 $niveladas++;
                 $nt=round($notaN*$por,1,PHP_ROUND_HALF_DOWN);
                 $ac=$ac+$nt;
                 $pdf->SetX(128);
                 $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
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
            if ($nota_a>=0 && $nota_a<=2.99) {
                $perdidas++;
                $prefijo=Prefijo::find(1);
                $pre1=$prefijo->prefijo;
                $su1=$prefijo->subfijo;

            } elseif ($nota_a>=3 && $nota_a<=3.99) {
                $ganadas++;
                $prefijo=Prefijo::find(2);
                $pre1=$prefijo->prefijo;
                $su1=$prefijo->subfijo;
            } elseif ($nota_a>=4 && $nota_a<=4.49) {
                $ganadas++;
                $prefijo=Prefijo::find(3);
                $pre1=$prefijo->prefijo;
                $su1=$prefijo->subfijo;
            } else {
                $ganadas++;
                $prefijo=Prefijo::find(4);
                $pre1=$prefijo->prefijo;
                $su1=$prefijo->subfijo;
            }
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetFillColor(255, 255, 255);
           if(!empty( $cal->logroCognitivo->descripcion)){
            $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
           }
           if( !empty( $cal->logroAfectivo->descripcion)){
            $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
           }

        }
      $pdf->SetFont('Arial', 'B', 8);
      $pdf->SetX(10);
      $pdf->Cell(8, 6,$acih , 1, 0, 'C' );
      $pdf->SetX(18);
      $pdf->SetFillColor(232, 232, 232);
      $pdf->Cell(94, 6, "MATEMATICAS".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
      $pdf->SetX(112);
      $pdf->Cell(16, 6, round($ac1,1,PHP_ROUND_HALF_DOWN), 1, 0, 'J', 0);
      $pdf->SetX(128);
      $pdf->Cell(16, 6, round($ac,1,PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
      if($ac>0 && $ac1>0){
        $promac=round(($ac+$ac1)/2, 1, PHP_ROUND_HALF_DOWN);
    }
      $pdf->SetX(192);
      $pdf->SetFont('Arial', 'B', 10);
      if(round($ac)>=3 && round($ac)<=5 ){
          $areasg++;
      }else{
          $areasp++;
      }
      $pdf->Cell(16, 6, ($promac), 1, 1, 'C',1);
      $c2=0;
      //Lenguaje
      $acl=0;
      $acil=0;
      $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 2, 2.2);
      foreach($calificaciones as $cal){
        $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
        $notaP1=0;
        $c2=1;
        $notaN=0;
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
                        $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);
                        $pdf->SetX(10);
                        $iha=$carga->ihs;
                        $acil+=$iha;
                        $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                        $pdf->SetX(18);
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetFillColor(255, 255, 255);
                        $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                        $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                        $nota_a=$cal->nota;
                        $por=$carga->porcentaje/100;
                        $porc2=$notaP1*$por;
                        $ac2=$ac2+$porc2;
          if (empty($notaN)) {
           $nt=round($nota_a*$por,1, PHP_ROUND_HALF_DOWN);
           $nota=$cal->nota;
           $acl=$acl+$nt;
           $pdf->SetX(128);
           $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
         } else {
           $nt=round($notaN*$por,1,PHP_ROUND_HALF_DOWN);
           $acl=$acl+$nt;
           $pdf->SetX(128);
           $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
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
      if ($nota_a>=0 && $nota_a<=2.99) {
          $perdidas++;
        $prefijo=Prefijo::find(1);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;

    } elseif ($nota_a>=3 && $nota_a<=3.99) {
        $ganadas++;
        $prefijo=Prefijo::find(2);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    } elseif ($nota_a>=4 && $nota_a<=4.49) {
        $ganadas++;
        $prefijo=Prefijo::find(3);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    } else {
        $ganadas++;
        $prefijo=Prefijo::find(4);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    }
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetFillColor(255, 255, 255);
   if(!empty( $cal->logroCognitivo->descripcion)){
    $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
   }
   if( !empty( $cal->logroAfectivo->descripcion)){
    $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
   }

}
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetX(10);
    $pdf->Cell(8, 6,$acil , 1, 0, 'C' );
    $pdf->SetX(18);
    $pdf->SetFillColor(232, 232, 232);
    $pdf->Cell(94, 6, "HUMANIDADES".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
    $pdf->SetX(112);
    $pdf->Cell(16, 6, round($ac2,1), 1, 0, 'J', 0);
    $pdf->SetX(128);
    $pdf->Cell(16, 6, round($acl,1), 1, 0, 'J', 1);
    if($acl>0 && $ac2>0){
        $promac1=round(($acl+$ac2)/2, 1, PHP_ROUND_HALF_DOWN);
    }
    $pdf->SetX(192);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(16, 6, $promac1, 1, 1, 'C',1);
    if(($acl)>=3 && ($acl)<=5 ){
        $areasg++;
    }else{
        $areasp++;
    }
    //CIENCIAS NATURALES
    $acn=0;
    $c3=0;
    $acin=0;
    $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 4, 4.3);
    foreach($calificaciones as $cal){
        $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
        $notaP1=0;
      $c3=1;
      $notaN=0;
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

                        $nivelacionPeriodo=Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
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
                        $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);
                        $pdf->SetX(10);
                        $iha=$carga->ihs;
                        $acin+=$iha;
                        $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                        $pdf->SetX(18);
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetFillColor(255, 255, 255);
                        $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                        $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                        $nota_a=$cal->nota;
                        $por=$carga->porcentaje/100;
                        $porc3=$notaP1*$por;
                        $ac3=$ac3+$porc3;
        if (empty($notaN)) {
         $nt=round($nota_a*$por,1,PHP_ROUND_HALF_DOWN);
         $nota=$cal->nota;
         $acn=$acn+$nt;
         $pdf->SetX(128);
         $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
       } else {
         $niveladas++;
         $nt=round($notaN*$por,1,PHP_ROUND_HALF_DOWN);
         $acn=$acn+$nt;
         $pdf->SetX(128);
         $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
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
       if ($nota_a>=0 && $nota_a<=2.99) {
        $perdidas++;
        $prefijo=Prefijo::find(1);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;

    } elseif ($nota_a>=3 && $nota_a<=3.99) {
        $ganadas++;
        $prefijo=Prefijo::find(2);
        $pre1=$prefijo->prefijo;
       $su1=$prefijo->subfijo;
    } elseif ($nota_a>=4 && $nota_a<=4.49) {
        $ganadas++;
        $prefijo=Prefijo::find(3);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    } else {
        $ganadas++;
        $prefijo=Prefijo::find(4);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    }
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetFillColor(255, 255, 255);
   if(!empty( $cal->logroCognitivo->descripcion)){
    $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
   }
   if( !empty( $cal->logroAfectivo->descripcion)){
    $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
   }

    }
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetX(10);
    $pdf->Cell(8, 6,$acin , 1, 0, 'C' );
    $pdf->SetX(18);
    $pdf->SetFillColor(232, 232, 232);
    $pdf->Cell(94, 6, "CIENCIAS NATURALES".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
    $pdf->SetX(112);
    $ac3=round($ac3,1);
    $acn=round($acn,1);
    $pdf->Cell(16, 6, ($ac3), 1, 0, 'J', 0);
    $pdf->SetX(128);
    $pdf->Cell(16, 6, ($acn),1,0, 'J', 1);
    if($acn>0 && $ac3>0){
        $promac=round(($acn+$ac3)/2, 1, PHP_ROUND_HALF_DOWN);
    }
    $pdf->SetX(192);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(16, 6, ($promac) , 1, 1, 'C',1);
    if(($acn)>=3 && ($acn)<=5 ){
        $areasg++;
    }else{
        $areasp++;
    }
    //CIENCIAS SOCIALES
    $acs=0;
    $acis=0;
    $c4=0;
    $ac3=0;
    $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 5, 5.5);
    foreach($calificaciones as $cal){
        $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
    $notaP1=0;
      $c4=1;
      $notaN=0;
      $por=0;
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
        $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);

        $pdf->SetX(10);
        $iha=$carga->ihs;
        $acis+=$iha;
        $pdf->Cell(8, 6, $iha, 1, 0, 'C');
        $pdf->SetX(18);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetFillColor(255, 255, 255);
       $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                        $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
        $nota_a=$cal->nota;
        $por=$carga->porcentaje/100;
        $nt=round($notaP1*$por,1,PHP_ROUND_HALF_DOWN);
        $ac3+=$nt;
        if (empty($notaN) ) {
         $nt=round($nota_a*$por,1,PHP_ROUND_HALF_DOWN);
         $nota=$cal->nota;
         $acs=$acs+$nt;
         $pdf->SetX(128);
         $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
       } else {
         $nt=round($notaN*$por,1,PHP_ROUND_HALF_DOWN);
         $acs=$acs+$nt;
         $pdf->SetX(128);
         $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
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
      if ($nota_a>=0 && $nota_a<=2.99) {
        $perdidas++;
        $prefijo=Prefijo::find(1);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;

    } elseif ($nota_a>=3 && $nota_a<=3.99) {
        $ganadas++;
        $prefijo=Prefijo::find(2);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    } elseif ($nota_a>=4 && $nota_a<=4.49) {
        $ganadas++;
        $prefijo=Prefijo::find(3);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    } else {
        $ganadas++;
        $prefijo=Prefijo::find(4);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    }
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetFillColor(255, 255, 255);
   if(!empty( $cal->logroCognitivo->descripcion)){
    $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
   }
   if( !empty( $cal->logroAfectivo->descripcion)){
    $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
   }
}
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetX(10);
    $pdf->Cell(8, 6,$acis , 1, 0, 'C' );
    $pdf->SetX(18);
    $pdf->SetFillColor(232, 232, 232);
    $pdf->Cell(94, 6, "CIENCIAS SOCIALES".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
    $pdf->SetX(112);
    $pdf->Cell(16, 6, round($ac3,1), 1, 0, 'J', 0);
    $pdf->SetX(128);
    $pdf->Cell(16, 6, round($acs,1), 1, 0, 'J', 1);
    $pdf->SetX(192);
    if($acs>0 && $ac3>0){
        $promac=round(($acs+$ac3)/2, 1, PHP_ROUND_HALF_DOWN);
    }
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(16, 6, $promac, 1, 1, 'C', 1);
    if(($acs)>=3 && ($acs)<=5 ){
        $areasg++;
    }else{
        $areasp++;
    }
    //OTRAS MATERIAS
    $act=0;
    $c5=0;
    $ac5=0;
    $calificaciones=Calificacion::reportCalificaciones($matricula->id, $periodo, 5.9, '');
    foreach($calificaciones as $cal){
     $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
     $notaP1=0;
     $c5++;
     $notaN=0;
     $ac5=$ac5+$c5;
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
      $carga=CargaAcademica::getIhs($grado, $cal->asignatura_id);
      $pdf->SetX(10);
      $iha=$carga->ihs;
      $acil=$acil+$iha;
      $pdf->Cell(8, 6, $iha, 1, 0, 'C');
      $pdf->SetX(18);
      $pdf->SetFont('Arial', 'B', 8);
      $pdf->SetFillColor(232, 232, 232);
      $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
      $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
      $nota_a=$cal->nota;
      $por=$carga->porcentaje/100;
      if (empty($notaN) ) {
       $nt=round($nota_a*$por,1);
       $nota=$cal->nota;
       $act = $act + $nt;
       $pdf->SetX(128);
       $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
     } else {
       $nt=round($notaN*$por,1);
       $act = $act + $nt;
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
    if ($nota_a>=0 && $nota_a<=2.99) {
        $perdidas++;
        $areasp++;
        $prefijo=Prefijo::find(1);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;

    } elseif ($nota_a>=3 && $nota_a<=3.99) {
        $ganadas++;
        $areasg++;
        $prefijo=Prefijo::find(2);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    } elseif ($nota_a>=4 && $nota_a<=4.49) {
        $ganadas++;
        $areasg++;

        $prefijo=Prefijo::find(3);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    } else {
        $ganadas++;
        $areasg++;
        $prefijo=Prefijo::find(4);
        $pre1=$prefijo->prefijo;
        $su1=$prefijo->subfijo;
    }
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetFillColor(255, 255, 255);
   if(!empty( $cal->logroCognitivo->descripcion)){
    $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($cal->logroCognitivo->descripcion)." ".utf8_decode($su1), 1, 1, 'J',true);
   }
   if( !empty( $cal->logroAfectivo->descripcion)){
    $pdf->MultiCell('198', '5',utf8_decode( $cal->logroAfectivo->descripcion), 1, 1, 'J');
   }

    }


    Auxiliar::disciplina($pdf, $matricula, $periodo );
    $pdf->SetFillColor(232, 232, 232);
    $tac=$ac+$acl+$acn+$acs+$act;
    $tc=$c1+$c2+$c3+$c4+$c5;
    $promg=$tac/$tc;
    $nprom=round($promg, 1,PHP_ROUND_HALF_DOWN);
    $nom = $matricula->apellidos.' '.$matricula->nombres;
    Puesto::updateOrCreate(
        ['matricula_id' => $matricula->id, 'periodo_id' => $periodo],
        ['matricula_id' => $matricula->id, 'periodo_id' => $periodo,
        'promedio'=>$nprom,
        'ganadas'=>$ganadas,
        'perdidas'=>$perdidas,
        'areasp'=>$areasp,
        'areasg'=>$areasg,
        'niveladas'=>$niveladas]
    );
    if ($nprom>=1 && $nprom<=2.99) {
      $des="DESEMPEÑO BAJO";
    } elseif ($nprom>=3 && $nprom<=3.99) {
     $des="DESEMPEÑO BÁSICO";
    }elseif ($nprom>=4 && $nprom<=4.49) {
     $des="DESEMPEÑO ALTO";
    } elseif($nprom>=4.5){
      $des="DESEMPEÑO SUPERIOR";
    }
    else {
     $des="";
    }
    $posicion=0;
                            $cont=0;

                            $puestos=Puesto::getPuesto($matricula->sede_id, $grado, $periodo);
                            $total=count($puestos);
                            foreach ($puestos as $puesto) {
                                $cont++;
                                if($puesto->matricula_id==$matricula->id){
                                    $posicion=$cont;
                                    break;
                                }
                            }
                            $pdf->SetFont('Arial', 'B', 10);
                            $pdf->Cell(198, 6, ' PROMEDIO DE PERIODO: '.$nprom.'   '.utf8_decode($des), 1, 1, 'J', 1);
                            $pdf->Cell(198, 6, ' PUESTO: '.$posicion. '  DE '.$total, 1, 1, 'J', 1);
                            Auxiliar::footer($pdf, $grado, $sede);


    }
        break;
          case '3':
                $matriculas = Matricula::listado($sede, $grado);
                $pdf = app('Fpdf');
                # code...
                foreach ($matriculas as $matricula) {
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
                    $c5 = 0;
                    $ganadas = 0;
                    $perdidas = 0;
                    $niveladas = 0;
                    $areasg = 0;
                    $areasp = 0;
                    $notaP1 = 0;
                    $notaP2 = 0;
                    $ac1 = 0;
                    $ac1p = 0;
                    $ac2 = 0;
                    $ac2p = 0;
                    $ac3 = 0;
                    $ac3p = 0;
                    $ac4 = 0;
                    $ac4p = 0;
                    $porc1 = 0;
                    $porc2 = 0;
                    $porc3 = 0;
                    $porc4 = 0;
                    $porc1p = 0;
                    $porc2p = 0;
                    $porc3p = 0;
                    $porc4p = 0;

                    $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 1, 1.3);
                    foreach ($calificaciones as $cal) {
                        $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                        $notaP2 = 0;
                        $notaP1 = 0;
                        $c5++;
                        $notaN = 0;
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetX(18);
                        //Nota periodo 1
                        $calPer1 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 1);
                        if (!empty($calPer1)) {
                            $notaP1 = $calPer1->nota;
                            $pdf->SetX(112);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(16, 6, $notaP1, 1, 0, 'J', 0);

                        }
                        $calPer2 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 2);
                        if (!empty($calPer2)) {
                            $notaP2 = $calPer2->nota;
                            $pdf->SetX(128);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(16, 6, $notaP2, 1, 0, 'J', 0);

                        }

                        if ($cal->nota < 3) {
                            $nivelacionPeriodo = Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if (!empty($nivelacionPeriodo)) {
                                $notaN = $nivelacionPeriodo->nota;
                                $pdf->SetX(160);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J', 1);
                            }
                        }
                        $c1 = 1;
                        $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);
                        $pdf->SetX(10);
                        $iha = $carga->ihs;
                        $acih += $iha;
                        $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                        $pdf->SetX(18);
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetFillColor(255, 255, 255);
                        $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                        $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                        $nota_a = $cal->nota;
                        $por = $carga->porcentaje / 100;
                        $porc1 = $notaP1 * $por;
                        $ac1 = $ac1 + $porc1;

                        $porc2 = $notaP2 * $por;
                        $ac2p = $ac2p + $porc2;
                        if (empty($notaN)) {
                            $nt = round($nota_a * $por, 1, PHP_ROUND_HALF_DOWN);
                            $nota = $cal->nota;
                            $ac = $ac + $nt;
                            $pdf->SetX(144);
                            $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                        } else {
                            $niveladas++;
                            $nt = round($notaN * $por, 1, PHP_ROUND_HALF_DOWN);
                            $ac = $ac + $nt;
                            $pdf->SetX(144);
                            $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                        }

                        if (empty($notaN)) {
                            $proma = round(($nota + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                        } else {
                            $niveladas++;
                            $proma = round(($notaN + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                        }
                        $pdf->SetX(192);
                        $pdf->SetFont('Arial', 'B', 10);
                        $pdf->Cell(16, 6, $proma, 1, 1, 'C', 1);

                        //Parte de Logros
                        if ($nota_a >= 0 && $nota_a <= 2.99) {
                            $perdidas++;
                            $prefijo = Prefijo::find(1);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;

                        } elseif ($nota_a >= 3 && $nota_a <= 3.99) {
                            $ganadas++;
                            $prefijo = Prefijo::find(2);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        } elseif ($nota_a >= 4 && $nota_a <= 4.49) {
                            $ganadas++;
                            $prefijo = Prefijo::find(3);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        } else {
                            $ganadas++;
                            $prefijo = Prefijo::find(4);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        }
                        $pdf->SetFont('Arial', '', 9);
                        $pdf->SetFillColor(255, 255, 255);
                        if (!empty($cal->logroCognitivo->descripcion)) {
                            $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal->logroCognitivo->descripcion) . " " . utf8_decode($su1), 1, 1, 'J', true);
                        }
                        if (!empty($cal->logroAfectivo->descripcion)) {
                            $pdf->MultiCell('198', '5', utf8_decode($cal->logroAfectivo->descripcion), 1, 1, 'J');
                        }

                    }
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetX(10);
                    $pdf->Cell(8, 6, $acih, 1, 0, 'C');
                    $pdf->SetX(18);
                    $pdf->SetFillColor(232, 232, 232);
                    $pdf->Cell(94, 6, "MATEMATICAS" . '    ' . utf8_decode('100%'), 1, 0, 'J', 1);
                    $pdf->SetX(112);
                    $pdf->Cell(16, 6, round($ac1, 1, PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
                    $pdf->SetX(128);
                    $pdf->Cell(16, 6, round($ac2p, 1, PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
                    $pdf->SetX(144);
                    $pdf->Cell(16, 6, round($ac, 1, PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
                    if ($ac > 0 && $ac1 > 0) {
                        $promac = round(($ac + $ac1 + $ac2p) / 3, 1, PHP_ROUND_HALF_DOWN);
                    }
                    $pdf->SetX(192);
                    $pdf->SetFont('Arial', 'B', 10);
                    if (round($ac) >= 3 && round($ac) <= 5) {
                        $areasg++;
                    } else {
                        $areasp++;
                    }
                    $pdf->Cell(16, 6,$promac, 1, 1, 'C', 1);
                    $c2 = 0;
                   //Lenguaje
                   $acl = 0;
                   $acil = 0;
                   $ac2p=0;
                   $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 2, 2.2);
                   foreach ($calificaciones as $cal) {
                       $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                       $notaP1 = 0;
                       $notaP2 = 0;
                       $c2 = 1;
                       $notaN = 0;
                       $pdf->SetFont('Arial', '', 8);
                       $pdf->SetX(18);
                       //Nota periodo 1
                       $calPer1 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 1);
                       if (!empty($calPer1)) {
                           $notaP1 = $calPer1->nota;
                           $pdf->SetX(112);
                           $pdf->SetFont('Arial', '', 8);
                           $pdf->SetFillColor(232, 232, 232);
                           $pdf->Cell(16, 6, $notaP1, 1, 0, 'J', 0);
                       }
                         //Nota periodo 2
                         $calPer2 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 2);
                         if (!empty($calPer1)) {
                             $notaP2 = $calPer2->nota;
                             $pdf->SetX(128);
                             $pdf->SetFont('Arial', '', 8);
                             $pdf->SetFillColor(232, 232, 232);
                             $pdf->Cell(16, 6, $notaP2, 1, 0, 'J', 0);
                         }

                       if ($cal->nota < 3) {
                           $nivelacionPeriodo = Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                           //Si existe una nota de nivelacion de periodo
                           if (!empty($nivelacionPeriodo)) {
                               $notaN = $nivelacionPeriodo->nota;
                               $pdf->SetX(160);
                               $pdf->SetFont('Arial', 'B', 10);
                               $pdf->SetFillColor(232, 232, 232);
                               $pdf->Cell(16, 6, $notaN, 1, 0, 'J', 1);
                           }
                       }
                       $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);
                       $pdf->SetX(10);
                       $iha = $carga->ihs;
                       $acil += $iha;
                       $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                       $pdf->SetX(18);
                       $pdf->SetFont('Arial', '', 8);
                       $pdf->SetFillColor(255, 255, 255);
                       $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                       $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                       $nota_a = $cal->nota;
                       $por = $carga->porcentaje / 100;
                       $porc1 = $notaP1 * $por;
                       $ac2 = $ac2 + $porc1;
                       $porc2 = $notaP2 * $por;
                       $ac2p = $ac2p + $porc2;
                       if (empty($notaN)) {
                           $nt = round($nota_a * $por, 1, PHP_ROUND_HALF_DOWN);
                           $nota = $cal->nota;
                           $acl = $acl + $nt;
                           $pdf->SetX(144);
                           $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                       } else {
                           $nt = round($notaN * $por, 1, PHP_ROUND_HALF_DOWN);
                           $acl = $acl + $nt;
                           $pdf->SetX(144);
                           $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                       }

                       if (empty($notaN)) {
                           $proma = round(($nota + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                       } else {
                           $niveladas++;
                           $proma = round(($notaN + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                       }
                       $pdf->SetX(192);
                       $pdf->SetFont('Arial', 'B', 10);
                       $pdf->Cell(16, 6, $proma, 1, 1, 'C', 1);

                       //Parte de Logros
                       if ($nota_a >= 0 && $nota_a <= 2.99) {
                           $perdidas++;
                           $prefijo = Prefijo::find(1);
                           $pre1 = $prefijo->prefijo;
                           $su1 = $prefijo->subfijo;

                       } elseif ($nota_a >= 3 && $nota_a <= 3.99) {
                           $ganadas++;
                           $prefijo = Prefijo::find(2);
                           $pre1 = $prefijo->prefijo;
                           $su1 = $prefijo->subfijo;
                       } elseif ($nota_a >= 4 && $nota_a <= 4.49) {
                           $ganadas++;
                           $prefijo = Prefijo::find(3);
                           $pre1 = $prefijo->prefijo;
                           $su1 = $prefijo->subfijo;
                       } else {
                           $ganadas++;
                           $prefijo = Prefijo::find(4);
                           $pre1 = $prefijo->prefijo;
                           $su1 = $prefijo->subfijo;
                       }
                       $pdf->SetFont('Arial', '', 9);
                       $pdf->SetFillColor(255, 255, 255);
                       if (!empty($cal->logroCognitivo->descripcion)) {
                           $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal->logroCognitivo->descripcion) . " " . utf8_decode($su1), 1, 1, 'J', true);
                       }
                       if (!empty($cal->logroAfectivo->descripcion)) {
                           $pdf->MultiCell('198', '5', utf8_decode($cal->logroAfectivo->descripcion), 1, 1, 'J');
                       }

                   }
                   $pdf->SetFont('Arial', 'B', 8);
                   $pdf->SetX(10);
                   $pdf->Cell(8, 6, $acil, 1, 0, 'C');
                   $pdf->SetX(18);
                   $pdf->SetFillColor(232, 232, 232);
                   $pdf->Cell(94, 6, "HUMANIDADES" . '    ' . utf8_decode('100%'), 1, 0, 'J', 1);
                   $pdf->SetX(112);
                   $pdf->Cell(16, 6, round($ac2, 1, PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
                   $pdf->SetX(128);
                   $pdf->Cell(16, 6, round($ac2p, 1, PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
                   $pdf->SetX(144);
                   $pdf->Cell(16, 6, round($acl, 1, PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
                   if ($acl > 0 && $ac2 > 0) {
                    $suma=($acl + $ac2+ $ac2p)/3;
                       $promac1 = round($suma,0, PHP_ROUND_HALF_DOWN  );
                   }
                   $pdf->SetX(192);
                   $pdf->SetFont('Arial', 'B', 10);
                   $pdf->Cell(16, 6, $promac1, 1, 1, 'C', 1);
                   if (($acl) >= 3 && ($acl) <= 5) {
                       $areasg++;
                   } else {
                       $areasp++;
                   }
                    //CIENCIAS NATURALES
                    $acn = 0;
                    $c3 = 0;
                    $acin = 0;
                    $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 4, 4.3);
                    foreach ($calificaciones as $cal) {
                        $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                        $notaP2 = 0;
                        $notaP1 = 0;
                        $c3 = 1;
                        $notaN = 0;
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetX(18);
                        //Nota periodo 1
                        $calPer1 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 1);
                        if (!empty($calPer1)) {
                            $notaP1 = $calPer1->nota;
                            $pdf->SetX(112);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(16, 6, $notaP1, 1, 0, 'J', 0);

                        }
                        $calPer2 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 2);
                        if (!empty($calPer2)) {
                            $notaP2 = $calPer2->nota;
                            $pdf->SetX(128);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(16, 6, $notaP2, 1, 0, 'J', 0);

                        }
                        if ($cal->nota < 3) {
                            $nivelacionPeriodo = Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if (!empty($nivelacionPeriodo)) {
                                $notaN = $nivelacionPeriodo->nota;
                                $pdf->SetX(144);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J', 1);
                            }
                        }

                        $nivelacionPeriodo = Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                        if ($cal->nota < 3) {
                            $nivelacionPeriodo = Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if (!empty($nivelacionPeriodo)) {
                                $notaN = $nivelacionPeriodo->nota;
                                $pdf->SetX(160);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J', 1);
                            }
                        }
                        $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);
                        $pdf->SetX(10);
                        $iha = $carga->ihs;
                        $acin += $iha;
                        $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                        $pdf->SetX(18);
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetFillColor(255, 255, 255);
                        $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                            $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                        $nota_a = $cal->nota;
                        $por = $carga->porcentaje / 100;
                        $porc3 = $notaP1 * $por;
                        $ac3 = $ac3 + $porc3;
                        $porc3p = $notaP2 * $por;
                        $ac3p = $ac3p + $porc3p;
                        if (empty($notaN)) {
                            $nt = round($nota_a * $por, 1);
                            $nota = $cal->nota;
                            $acn = $acn + $nt;
                            $pdf->SetX(144);
                            $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                        } else {
                            $niveladas++;
                            $nt = round($notaN * $por, 1);
                            $acn = $acn + $nt;
                            $pdf->SetX(144);
                            $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                        }

                        if (empty($notaN)) {
                            $proma = round(($nota + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                        } else {
                            $niveladas++;
                            $proma = round(($notaN + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                        }
                        $pdf->SetX(192);
                        $pdf->SetFont('Arial', 'B', 10);
                        $pdf->Cell(16, 6, $proma, 1, 1, 'C', 1);

                        //Parte de Logros
                        if ($nota_a >= 0 && $nota_a <= 2.99) {
                            $perdidas++;
                            $prefijo = Prefijo::find(1);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;

                        } elseif ($nota_a >= 3 && $nota_a <= 3.99) {
                            $ganadas++;
                            $prefijo = Prefijo::find(2);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        } elseif ($nota_a >= 4 && $nota_a <= 4.49) {
                            $ganadas++;
                            $prefijo = Prefijo::find(3);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        } else {
                            $ganadas++;
                            $prefijo = Prefijo::find(4);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        }
                        $pdf->SetFont('Arial', '', 9);
                        $pdf->SetFillColor(255, 255, 255);
                        if (!empty($cal->logroCognitivo->descripcion)) {
                            $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal->logroCognitivo->descripcion) . " " . utf8_decode($su1), 1, 1, 'J', true);
                        }
                        if (!empty($cal->logroAfectivo->descripcion)) {
                            $pdf->MultiCell('198', '5', utf8_decode($cal->logroAfectivo->descripcion), 1, 1, 'J');
                        }

                    }
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetX(10);
                    $pdf->Cell(8, 6, $acin, 1, 0, 'C');
                    $pdf->SetX(18);
                    $pdf->SetFillColor(232, 232, 232);
                    $pdf->Cell(94, 6, "CIENCIAS NATURALES" . '    ' . utf8_decode('100%'), 1, 0, 'J', 1);
                    $pdf->SetX(112);
                    $pdf->Cell(16, 6, round($ac3, 1, PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
                    $pdf->SetX(128);
                    $pdf->Cell(16, 6, round($ac3p, 1, PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
                    $pdf->SetX(144);
                    $pdf->Cell(16, 6, round($acn, 1, PHP_ROUND_HALF_DOWN), 1, 0, 'J', 1);
                    if ($acn > 0 && $ac3 > 0 && $ac3p > 0) {
                        $proma = round(($acn + $ac3 + $ac3p) / 3, 1, PHP_ROUND_HALF_DOWN);
                    }
                    $pdf->SetX(192);
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(16, 6, round($proma, 1, PHP_ROUND_HALF_DOWN), 1, 1, 'C', 1);
                    if (($acn) >= 3 && ($acn) <= 5) {
                        $areasg++;
                    } else {
                        $areasp++;
                    }
                    //CIENCIAS SOCIALES
                    $acs = 0;
                    $ac4 = 0;
                    $acis = 0;
                    $c4 = 0;
                    $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 5, 5.5);
                    foreach ($calificaciones as $cal) {
                        $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                        $notaP2 = 0;
                        $notaP1 = 0;
                        $c4 = 1;
                        $notaN = 0;
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetX(18);
                        //Nota periodo 1
                        $calPer1 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 1);
                        if (!empty($calPer1)) {
                            $notaP1 = $calPer1->nota;
                            $pdf->SetX(112);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(16, 6, $notaP1, 1, 0, 'J', 0);

                        }
                        $calPer2 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 2);
                        if (!empty($calPer2)) {
                            $notaP2 = $calPer2->nota;
                            $pdf->SetX(128);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(16, 6, $notaP2, 1, 0, 'J', 0);

                        }
                        if ($cal->nota < 3) {
                            $nivelacionPeriodo = Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if (!empty($nivelacionPeriodo)) {
                                $notaN = $nivelacionPeriodo->nota;
                                $pdf->SetX(160);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J', 1);
                            }
                        }
                        $carga = CargaAcademica::getIhs($grado, $cal->asignatura_id);

                        $pdf->SetX(10);
                        $iha = $carga->ihs;
                        $acis += $iha;
                        $pdf->Cell(8, 6, $iha, 1, 0, 'C');
                        $pdf->SetX(18);
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetFillColor(255, 255, 255);
                        $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                            $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                        $nota_a = $cal->nota;
                        $por = $carga->porcentaje / 100;
                        $porc4 = $notaP1 * $por;
                        $ac4 = $ac4 + $porc4;

                        $porc4p = $notaP2 * $por;
                        $ac4p = $ac4p + $porc4p;

                        if (empty($notaN)) {
                            $nt = round($nota_a * $por, 1, PHP_ROUND_HALF_DOWN);
                            $nota = $cal->nota;
                            $acs = $acs + $nt;
                            $pdf->SetX(144);
                            $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                        } else {
                            $nt = round($notaN * $por, 1, PHP_ROUND_HALF_DOWN);
                            $acs = $acs + $nt;
                            $pdf->SetX(144);
                            $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                        }
                        if (empty($notaN)) {
                            $proma = round(($nota + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                        } else {
                            $niveladas++;
                            $proma = round(($notaN + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                        }
                        $pdf->SetX(192);
                        $pdf->SetFont('Arial', 'B', 10);
                        $pdf->Cell(16, 6, $proma, 1, 1, 'C', 1);

                        //Parte de Logros
                        if ($nota_a >= 0 && $nota_a <= 2.99) {
                            $perdidas++;
                            $prefijo = Prefijo::find(1);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;

                        } elseif ($nota_a >= 3 && $nota_a <= 3.99) {
                            $ganadas++;
                            $prefijo = Prefijo::find(2);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        } elseif ($nota_a >= 4 && $nota_a <= 4.49) {
                            $ganadas++;
                            $prefijo = Prefijo::find(3);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        } else {
                            $ganadas++;
                            $prefijo = Prefijo::find(4);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        }
                        $pdf->SetFont('Arial', '', 9);
                        $pdf->SetFillColor(255, 255, 255);
                        if (!empty($cal->logroCognitivo->descripcion)) {
                            $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal->logroCognitivo->descripcion) . " " . utf8_decode($su1), 1, 1, 'J', true);
                        }
                        if (!empty($cal->logroAfectivo->descripcion)) {
                            $pdf->MultiCell('198', '5', utf8_decode($cal->logroAfectivo->descripcion), 1, 1, 'J');
                        }
                    }
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetX(10);
                    $pdf->Cell(8, 6, $acis, 1, 0, 'C');
                    $pdf->SetX(18);
                    $pdf->SetFillColor(232, 232, 232);
                    $pdf->Cell(94, 6, "CIENCIAS SOCIALES" . '    ' . utf8_decode('100%'), 1, 0, 'J', 1);
                    $pdf->SetX(112);
                    $pdf->Cell(16, 6, round($ac4, 1), 1, 0, 'J', 1);
                    $pdf->SetX(128);
                    $pdf->Cell(16, 6, round($ac4p, 1), 1, 0, 'J', 1);
                    $pdf->SetX(144);
                    $pdf->Cell(16, 6, round($acs, 1), 1, 0, 'J', 1);
                    $pdf->SetX(192);
                    if ($acs > 0 && $ac4 > 0 && $ac4p > 0) {
                        $promac = round(($acs + $ac4 + $ac4p) / 3, 1);
                    }
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(16, 6, $promac, 1, 1, 'C');
                    if (($acs) >= 3 && ($acs) <= 5) {
                        $areasg++;
                    } else {
                        $areasp++;
                    }
                    //OTRAS MATERIAS
                    $act = 0;
                    $c5 = 0;
                    $ac5 = 0;
                    $calificaciones = Calificacion::reportCalificaciones($matricula->id, $periodo, 5.9, '');
                    foreach ($calificaciones as $cal) {
                        $docente=CargaAcademica::getDocente($sede, $grado, $cal->asignatura->id);
                        $c5++;
                        $notaN = 0;
                        $notaP2 = 0;
                        $notaP1 = 0;
                        $ac5 = $ac5 + $c5;
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->SetX(18);
                        $calPer1 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 1);
                        if (!empty($calPer1)) {
                            $notaP1 = $calPer1->nota;
                            $pdf->SetX(112);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(16, 6, $notaP1, 1, 0, 'J', 0);

                        }
                        $calPer2 = Calificacion::notaAnteriorEst($matricula->id, $cal->asignatura_id, 2);
                        if (!empty($calPer2)) {
                            $notaP2 = $calPer2->nota;
                            $pdf->SetX(128);
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->SetFillColor(232, 232, 232);
                            $pdf->Cell(16, 6, $notaP2, 1, 0, 'J', 0);

                        }

                        if ($cal->nota < 3) {
                            $nivelacionPeriodo = Nivelacion::getNivelacion($matricula->id, $cal->asignatura_id, $periodo);
                            //Si existe una nota de nivelacion de periodo
                            if (!empty($nivelacionPeriodo)) {
                                $notaN = $nivelacionPeriodo->nota;
                                $pdf->SetX(144);
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->SetFillColor(232, 232, 232);
                                $pdf->Cell(16, 6, $notaN, 1, 0, 'J', 1);
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
                        $nombreDocente=$docente->docente->nombres.' '.$docente->docente->apellidos;
                        $pdf->Cell(94, 6, utf8_decode($cal->asignatura->nombre).' '.utf8_decode($carga->porcentaje).' '.utf8_decode("% ".$nombreDocente), 1, 0, 'J',1);
                        $nota_a = $cal->nota;
                        $por = $carga->porcentaje / 100;
                        if (empty($notaN)) {
                            $nt = round($nota_a * $por, 1, PHP_ROUND_HALF_DOWN);
                            $nota = $cal->nota;
                            $act = $act + $nt;
                            $pdf->SetX(144);
                            $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 1);
                        } else {
                            $nt = round($notaN * $por, 1, PHP_ROUND_HALF_DOWN);
                            $act = $act + $nt;
                            $pdf->SetX(144);
                            $pdf->Cell(16, 6, $cal->nota, 1, 0, 'J', 0);
                        }

                        if (empty($notaN)) {
                            $proma = round(($nota + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                        } else {
                            $niveladas++;
                            $proma = round(($notaN + $notaP1 + $notaP2) / 3, 1, PHP_ROUND_HALF_DOWN);
                        }
                        $pdf->SetX(192);
                        $pdf->SetFont('Arial', 'B', 10);
                        $pdf->Cell(16, 6, $proma, 1, 1, 'C', 1);

                        //Parte de Logros
                        if ($nota_a >= 0 && $nota_a <= 2.99) {
                            $perdidas++;
                            $areasp++;
                            $prefijo = Prefijo::find(1);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;

                        } elseif ($nota_a >= 3 && $nota_a <= 3.99) {
                            $ganadas++;
                            $areasg++;
                            $prefijo = Prefijo::find(2);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        } elseif ($nota_a >= 4 && $nota_a <= 4.49) {
                            $ganadas++;
                            $areasg++;

                            $prefijo = Prefijo::find(3);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        } else {
                            $ganadas++;
                            $areasg++;
                            $prefijo = Prefijo::find(4);
                            $pre1 = $prefijo->prefijo;
                            $su1 = $prefijo->subfijo;
                        }
                        $pdf->SetFont('Arial', '', 9);
                        $pdf->SetFillColor(255, 255, 255);
                        if (!empty($cal->logroCognitivo->descripcion)) {
                            $pdf->MultiCell('198', '5', utf8_decode($pre1) . " " . utf8_decode($cal->logroCognitivo->descripcion) . " " . utf8_decode($su1), 1, 1, 'J', true);
                        }
                        if (!empty($cal->logroAfectivo->descripcion)) {
                            $pdf->MultiCell('198', '5', utf8_decode($cal->logroAfectivo->descripcion), 1, 1, 'J');
                        }

                    }

                    Auxiliar::disciplina($pdf, $matricula, $periodo);
                    $pdf->SetFillColor(232, 232, 232);
                    $tac = $ac + $acl + $acn + $acs + $act;
                    $tc = $c1 + $c2 + $c3 + $c4 + $c5;
                    $promg = $tac / $tc;
                    $nprom = round($promg, 1, PHP_ROUND_HALF_DOWN);
                    $nom = $matricula->apellidos . ' ' . $matricula->nombres;
                    Puesto::updateOrCreate(
                        ['matricula_id' => $matricula->id, 'periodo_id' => $periodo],
                        ['matricula_id' => $matricula->id, 'periodo_id' => $periodo,
                            'promedio' => $nprom,
                            'ganadas' => $ganadas,
                            'perdidas' => $perdidas,
                            'areasp' => $areasp,
                            'areasg' => $areasg,
                            'niveladas' => $niveladas]
                    );
                    if ($nprom >= 1 && $nprom <= 2.99) {
                        $des = "DESEMPEÑO BAJO";
                    } elseif ($nprom >= 3 && $nprom <= 3.99) {
                        $des = "DESEMPEÑO BÁSICO";
                    } elseif ($nprom >= 4 && $nprom <= 4.49) {
                        $des = "DESEMPEÑO ALTO";
                    } elseif ($nprom >= 4.5) {
                        $des = "DESEMPEÑO SUPERIOR";
                    } else {
                        $des = "";
                    }
                    $posicion = 0;
                    $cont = 0;

                    $puestos = Puesto::getPuesto($matricula->sede_id, $grado, $periodo);
                    $total = count($puestos);
                    foreach ($puestos as $puesto) {
                        $cont++;
                        if ($puesto->matricula_id == $matricula->id) {
                            $posicion = $cont;
                            break;
                        }
                    }
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(198, 6, ' PROMEDIO DE PERIODO: ' . $nprom . '   ' . utf8_decode($des), 1, 1, 'J', 1);
                    $pdf->Cell(198, 6, ' PUESTO: ' . $posicion . '  DE ' . $total, 1, 1, 'J', 1);
                    Auxiliar::footer($pdf, $grado, $sede);

                }


        break;
        case '4':
        $sql2 = "SELECT distinct idestudiante from calificaciones where idgrado='$grado' and  periodo ='$periodo' and idsede='$sede'";
        $res2 = $mysqli->query($sql2);
        $num1 = $res2->num_rows;
        $i    = 1;
        $pdf  = new FPDF('P', 'mm', 'Legal');
        $sql  = "SELECT distinct c.idestudiante, e.nombres, e.apellidos, g.descripcion, s.nombre, e.num_doc, e.folio from calificaciones c inner join estudiantes e on c.idestudiante=e.idestudiante inner join grados g on c.idgrado=g.idgrado inner join sedes s on c.idsede=s.idsede  where c.idgrado='$grado' and  periodo ='$periodo' and  c.idsede='$sede' order by e.apellidos asc";
        $res  = $mysqli->query($sql);
        while ($i <= $num1) {
          while ($reg = $res->fetch_row()) {
            $pdf->AddPage();
            $pdf->SetFillColor(232, 232, 232);
            $sqlc="SELECT * FROM datos_colegio ";
            $resc=$mysqli->query($sqlc);
            $regc=$resc->fetch_row();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(190, 6, utf8_decode($regc[1]), 0, 1, 'C');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(190, 4, utf8_decode($regc[2]), 0, 1, 'C');
            $pdf->Cell(190, 4, utf8_decode(' Plantel de Carácter Oficial'), 0, 1, 'C');
            $pdf->Cell(190, 4, utf8_decode(' Resolución N° 1072 Mayo 31/04 y 1566 Agosto 06/04'), 0, 1, 'C');
            $pdf->Cell(190, 6, utf8_decode(' Código Icfes '.$regc[7]), 0, 1, 'C');
            $pdf->Cell(190, 6, utf8_decode($regc[6]), 0, 1, 'C');
            $pdf->Image('logo-ineda.png', 8, 6, 20);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Ln(2);
            $pdf->Cell(198, 6, utf8_decode(' INFORME ACADEMICO Y/O DISCIPLINARIO'), 1, 1, 'C', 1);
            $idest= $reg['0'];
            $nom = $reg['1'] . ' ' . $reg['2'];
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(113, 4, 'Nombres: ' . $nom, 1, 0, 'J');
            $pdf->Cell(47, 4, 'Grado: ' . $reg['3'], 1, 0, 'J');
            $pdf->Cell(38, 4, 'Periodo: ' . $periodo, 1, 1, 'J');
            $pdf->Cell(40, 5, 'Sede: ' . $reg['4'], 1, 0, 'J');
            $pdf->Cell(40, 5, utf8_decode(' N° Doc: ') . $reg['5'], 1, 0, 'J');
            $pdf->Cell(33, 5, utf8_decode(' N° Folio: ') . $reg['6'], 1, 0, 'J');
            $pdf->Cell(47, 5, utf8_decode(' Jornada: ') . $jornada, 1, 0, 'J');
            $pdf->Cell(38, 5, utf8_decode(' Año: 2020 '), 1, 1, 'J');
            $pdf->Ln();
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(102, 6, ' ', 0, 0, 'J');
            $pdf->Cell(96, 6, 'PERIODOS  ', 1, 1, 'C', 1);
            $pdf->Cell(8, 6, ' IHS', 1, 0, 'C', 1);
            $pdf->Cell(94, 6, utf8_decode('ARÉAS Y/O ASIGNATURAS: '), 1, 0, 'J', 1);
            $pdf->Cell(16, 6, 'I  ', 1, 0, 'C', 1);
            $pdf->Cell(16, 6, 'II  ', 1, 0, 'C', 1);
            $pdf->Cell(16, 6, 'III  ', 1, 0, 'C', 1);
            $pdf->Cell(16, 6, 'IV  ', 1, 0, 'C', 1);
            $pdf->Cell(16, 6, 'NIV  ', 1, 0, 'C', 1);
            $pdf->Cell(16, 6, 'DEF  ', 1, 1, 'C', 1);
            $pdf->SetFont('Arial', '', 8);

        //matematicas
            $acih=0;
            $ac=0;
            $acp1=0;
            $acdef=0;
            $proma=0;
            $acil=0;
            $acin=0;
            $c1=0;
            $acp2=0; $acp3=0; $aclp2=0; $aclp3=0; $acnp2=0; $acnp3=0; $acsp2=0; $acsp3=0;
            $sql1 = "SELECT * from calificaciones where idestudiante='$idest' and periodo='$periodo' and idgrado='$grado' and  idsede='$sede' and orden>1 and orden<=1.3  order by orden asc";
            $res1 = $mysqli->query($sql1);
            while ($reg1 = $res1->fetch_row()) {
              $pdf->SetFont('Arial', '', 8);
              $pdf->SetX(18);
              $nota=$reg1['5'];
              $idasig=$reg1['2'];
              $sqlm = "SELECT c.ihs, c.porcentaje, a.nombre, c.idasignatura from carga_academica c inner join asignaturas a on a.idasignatura=c.idasignatura where c.idasignatura='$idasig' and c.idgrado='$grado' and c.idsede='$sede' ";
              $resm = $mysqli->query($sqlm);
              while ($regm=$resm->fetch_row()) {
                $idasig=$regm[3];
                $sqln1 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='$periodo'";
                $resn1 = $mysqli->query($sqln1);
               $nv1=$resn1->num_rows;
                if($nv1>0){
                   $regnp = $resn1->fetch_row();
                  $pdf->SetFillColor(232, 232, 232);
                  $pdf->SetX(176);
                  $notaN=$regnp[0];
                  $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
                }
                $notap3N=0;
                $sqlnp3 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='3'";
                $resnp3 = $mysqli->query($sqlnp3);
                $regnp3 =$resnp3->fetch_row();
                if (!empty($regnp3['0'])) {
                 $notap3N=$regnp3['0'];
                }
                $notap2N=0;
                $sqlnp2 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='2'";
                $resnp2 = $mysqli->query($sqlnp2);
                $regnp2 =$resnp2->fetch_row();
                if (!empty($regnp2['0'])) {
                 $notap2N=$regnp2['0'];
               }
                $notap1N=0;
                $sqlnp1 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='1'";
                $resnp1 = $mysqli->query($sqlnp1);
               $regnp1 =$resnp1->fetch_row();
                if (!empty($regnp1['0'])) {
                 $notap1N=$regnp1['0'];
               }

                $sqlcp3 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='3' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig' ";
                $rescp3 = $mysqli->query($sqlcp3);
                $regcp3 = $rescp3->fetch_row();
                if(!empty($regcp3['0'])){
                  $ncp3=$regcp3['0'];

                } else {
                 $ncp3=0;
               }

               $sqlcp2 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='2' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig' ";
               $rescp2 = $mysqli->query($sqlcp2);
               $regcp2 = $rescp2->fetch_row();
               if(!empty($regcp2['0'])){
                $ncp2=$regcp2['0'];

              } else {
               $ncp2=0;
             }

             $sqlcp1 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='3' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig' ";
             $rescp1 = $mysqli->query($sqlcp1);
             $regcp1 = $rescp1->fetch_row();
             if(!empty($regcp1['0'])){
              $ncp1=$regcp1['0'];

            } else {
             $ncp1=0;
           }

           if (empty($notap1N)) {
             $ntemp=round($ncp1*($regm['1']/100),1);
             $acp1=$acp1+$ntemp;
             $pdf->SetX(112);
             $np1=$ncp1;
             $pdf->Cell(16, 6, $np1, 1, 0, 'J');
           } else {
            $ntemp=round($notap1N*($regm['1']/100),1);
            $acp1=$acp1+$ntemp;
            $np1=$notap1N;
            $pdf->SetX(112);
            $pdf->Cell(16, 6, $np1, 1, 0, 'J');
          }
          if (empty($notap2N)) {
           $ntemp=round($ncp2*($regm['1']/100),1);
           $acp2=$acp2+$ntemp;
           $pdf->SetX(128);
           $np2=$ncp2;
           $pdf->Cell(16, 6, $np2, 1, 0, 'J');
         } else {
          $ntemp=round($notap2N*($regm['1']/100),1);
          $acp2=$acp2+$ntemp;
          $np2=$notap2N;
          $pdf->SetX(128);
          $pdf->Cell(16, 6, $np2, 1, 0, 'J');
        }
        if (empty($notap3N)) {
         $ntemp=round($ncp3*($regm['1']/100),1);
         $acp3=$acp3+$ntemp;
         $pdf->SetX(144);
         $np3=$ncp3;
         $pdf->Cell(16, 6, $np3, 1, 0, 'J');
        } else {
          $ntemp=round($notap3N*($regm['1']/100),1);
          $acp3=$acp3+$ntemp;
          $np3=$notap3N;
          $pdf->SetX(144);
          $pdf->Cell(16, 6, $np3, 1, 0, 'J');
        }
        $pdf->SetFillColor(232, 232, 232);
        $c1=1;
        $pdf->SetX(10);
        $iha=$regm['0'];
        $acih=$acih+$iha;
        $pdf->Cell(8, 6, $regm['0'], 1, 0, 'C');
        $pdf->SetX(18);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(94, 6, utf8_decode($regm['2']).'     '.$regm['1'].' '.utf8_decode("%"), 1, 0, 'J');

        if ($nota<3 && !empty($notaN)) {
          $por=$regm['1']/100;
          $nt=round($notaN*$por,1);
          $def=($np1+$np2+$np3+$notaN)/4;
          $ac=$ac+$nt;
        } else {
         $por=$regm['1']/100;
         $nt=round($nota*$por,1);
         $def=($np1+$np2+$np3+$nota)/4;
         $ac=$ac+$nt;
        }
        $pdf->SetX(160);
        $pdf->Cell(16, 6, $reg1['5'], 1, 0, 'J');
        $pdf->SetX(112);
        $nota_a=$reg1['5'];
        $pdf->SetFont('Arial', 'B', 8);
        $proma=round($def,1);
        $pdf->SetX(192);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, $proma, 1, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        if ($nota_a>=0 && $nota_a<=2.99) {
          $sql3   = "SELECT * from prefijos where idprefijo='1'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];

        } elseif ($nota_a>=3 && $nota_a<=3.99) {
         $sql3   = "SELECT * from prefijos where idprefijo='2'";
         $res3   = $mysqli->query($sql3);
         $reg = $res3->fetch_row();
         $pre1=$reg['1'];
         $su1=$reg['2'];
        } elseif ($nota_a>=4 && $nota_a<=4.49) {
          $sql3   = "SELECT * from prefijos where idprefijo='3'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        } else {
          $sql3   = "SELECT * from prefijos where idprefijo='4'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        }
        $pdf->SetFont('Arial', '', 9);
        $logro_c = $reg1['7'];
        $logro_d=$reg1['8'];
        $sql3   = "SELECT descripcion from logros_academicos where idlogro='$logro_c' and idasignatura='$idasig'";
        $resl   = $mysqli->query($sql3);
        while ($regl = $resl->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($regl['0'])." ".utf8_decode($su1), 1, 1, 'J',true);

        }
        $sql4   = "SELECT descripcion from logros_academicos where idlogro='$logro_d' and idasignatura='$idasig'";
        $res4   = $mysqli->query($sql4);
        while ($reg4 = $res4->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5',utf8_decode( $reg4['0']), 1, 1, 'J');
        }

        }

        }

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->Cell(8, 6,$acih , 1, 0, 'C' );
        $pdf->SetX(18);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, "MATEMATICAS".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
        $pdf->SetX(112);
        $pdf->Cell(16, 6, $acp1, 1, 0, 'J');
        $pdf->SetX(128);
        $pdf->Cell(16, 6, $acp2, 1, 0, 'J');
        $pdf->SetX(144);
        $pdf->Cell(16, 6, $acp3, 1, 0, 'J');
        $pdf->SetX(160);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(16, 6, $ac, 1, 0, 'J',1);
        $pdf->SetX(192);
        $acdef=($acp1+$acp2+$acp3+$ac)/4;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, round($acdef,1), 1, 1, 'J',1);
        $pdf->Ln(3.5);
        $acp1=0;
        $acp2=0;
        $acp3=0;
                    //Lenguaje
        $c2=0;
        $acl=0;
        $acihl=0;
        $aclp1=0;
        $acldef=0;
        $promal=0;
        $notaN=0;
        $notap1N=0;
        $aclp1=0;
        $defl=0;
        $ntemp=0;
        $sql1 = "SELECT * from calificaciones where idestudiante='$idest' and periodo='$periodo' and idgrado='$grado' and  idsede='$sede' and orden>2 and orden<=2.3  order by orden asc";
        $res1 = $mysqli->query($sql1);
        while ($reg1 = $res1->fetch_row()) {
          $pdf->SetFont('Arial', '', 8);
          $pdf->SetX(18);
          $nota=$reg1['5'];
          $idasig=$reg1['2'];
          $sqlm = "SELECT c.ihs, c.porcentaje, a.nombre, c.idasignatura from carga_academica c inner join asignaturas a on a.idasignatura=c.idasignatura where c.idasignatura='$idasig' and c.idgrado='$grado' and c.idsede='$sede' ";
          $resm = $mysqli->query($sqlm);
          while ($regm=$resm->fetch_row()) {
            $idasig=$regm[3];
            $notaN=0;
            $sqln1 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='$periodo'";
            $resn1 = $mysqli->query($sqln1);
            $nv1=$resn1->num_rows;
            while ( $regnp = $resn1->fetch_row()) {
              $pdf->SetX(176);
              $pdf->SetFillColor(232, 232, 232);
              $notaN=$regnp['0'];
              $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
            }
            $notap3N=0;
            $sqlnp3 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='3'";
            $resnp3 = $mysqli->query($sqlnp3);
            while ( $regnp3 =$resnp3->fetch_row()) {
              $notap3N=$regnp3['0'];
            }
            $notap2N=0;
            $sqlnp2 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='2'";
            $resnp2 = $mysqli->query($sqlnp2);
            while ( $regnp2 =$resnp2->fetch_row()) {
              $notap2N=$regnp2['0'];
            }
            $notap1N=0;
            $sqlnp1 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='1'";
            $resnp1 = $mysqli->query($sqlnp1);
            while ( $regnp1 = $resnp1->fetch_row()) {
              $notap1N=$regnp1['0'];
            }
            $sqlcp3 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='3' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig'";
            $rescp3 = $mysqli->query($sqlcp3);
            $regcp3 = $rescp3->fetch_row();
            if(!empty($regcp3['0'])){
              $nclp3=$regcp3['0'];
            } else {
             $nclp3=0;
           }


           $sqlcp2 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='2' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig'";
           $rescp2 = $mysqli->query($sqlcp2);
           $regcp2 = $rescp2->fetch_row();
           if(!empty($regcp2['0'])){
            $nclp2=$regcp2['0'];
          } else {
           $nclp2=0;
         }

         $sqlcp1 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='1' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig'";
         $rescp1 = $mysqli->query($sqlcp1);
         $regcp1 = $rescp1->fetch_row();
         if(!empty($regcp1['0'])){
          $nclp1=$regcp1['0'];
        } else {
         $nclp1=0;
        }

        if (empty($notap1N)) {
         $ntemp=round($nclp1*($regm['1']/100),1);
         $aclp1=$aclp1+$ntemp;
         $pdf->SetX(112);
         $np1=$nclp1;
         $pdf->Cell(16, 6, $np1, 1, 0, 'J');

        } else {
          $ntemp=round($notap1N*($regm['1']/100),1);
          $aclp1=$aclp1+$ntemp;
          $np1=$notap1N;
          $pdf->SetX(112);
          $pdf->Cell(16, 6, $notap1N, 1, 0, 'J');
        }
        if (empty($notap2N)) {
         $ntemp=round($nclp2*($regm['1']/100),1);
         $aclp2=$aclp2+$ntemp;
         $pdf->SetX(128);
         $np2=$nclp2;
         $pdf->Cell(16, 6, $np2, 1, 0, 'J');

        } else {
          $ntemp=round($notap2N*($regm['1']/100),1);
          $aclp2=$aclp2+$ntemp;
          $np2=$notap2N;
          $pdf->SetX(128);
          $pdf->Cell(16, 6, $np2, 1, 0, 'J');
        } if (empty($notap3N)) {
         $ntemp=round($nclp3*($regm['1']/100),1);
         $aclp3=$aclp3+$ntemp;
         $pdf->SetX(144);
         $np3=$nclp3;
         $pdf->Cell(16, 6, $np3, 1, 0, 'J');

        } else {
          $ntemp=round($notap3N*($regm['1']/100),1);
          $aclp3=$aclp3+$ntemp;
          $np3=$notap3N;
          $pdf->SetX(144);
          $pdf->Cell(16, 6, $np3, 1, 0, 'J');
        }

        $c2=1;
        $pdf->SetX(10);
        $iha=$regm['0'];
        $acihl=$acihl+$iha;
        $pdf->Cell(8, 6, $regm['0'], 1, 0, 'C');
        $pdf->SetX(18);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, utf8_decode($regm['2']).'     '.$regm['1'].' '.utf8_decode("%"), 1, 0, 'J');
        if ($nota<3 && !empty($notaN)) {
          $por=$regm['1']/100;
          $nt=round($notaN*$por,1);
          $defl=($np1+$np2+$np3+$notaN)/4;
          $acl=$acl+$nt;
        } else {
          $por=$regm['1']/100;
          $nt=round($nota*$por,1);
          $defl=($np1+$np2+$np3+$nota)/4;
          $acl=$acl+$nt;
        }
        $pdf->SetX(160);
        $pdf->Cell(16, 6, $reg1['5'], 1, 0, 'J');
        $pdf->SetX(112);
        $nota_a=$reg1['5'];
        $pdf->SetFont('Arial', 'B', 8);
        $promal=round($defl,1);
        $pdf->SetX(192);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, $promal, 1, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        if ($nota_a>=0 && $nota_a<=2.99) {
          $sql3   = "select * from prefijos where idprefijo='1'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];

        } elseif ($nota_a>=3 && $nota_a<=3.99) {
         $sql3   = "select * from prefijos where idprefijo='2'";
         $res3   = $mysqli->query($sql3);
         $reg = $res3->fetch_row();
         $pre1=$reg['1'];
         $su1=$reg['2'];
        } elseif ($nota_a>=4 && $nota_a<=4.49) {
          $sql3   = "select * from prefijos where idprefijo='3'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        } else {
          $sql3   = "select * from prefijos where idprefijo='4'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        }
        $pdf->SetFont('Arial', '', 9);
        $logro_c = $reg1['7'];
        $logro_d=$reg1['8'];
        $sql3   = "SELECT descripcion from logros_academicos where idlogro='$logro_c' and idasignatura='$idasig'";
        $resl   = $mysqli->query($sql3);
        while ($regl = $resl->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($regl['0'])." ".utf8_decode($su1), 1, 1, 'J',true);
        }
        $sql4   = "SELECT descripcion from logros_academicos where idlogro='$logro_d' and idasignatura='$idasig'";
        $res4   = $mysqli->query($sql4);
        while ($reg4 = $res4->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5',utf8_decode( $reg4['0']), 1, 1, 'J');
        }
        }
        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->Cell(8, 6,$acihl , 1, 0, 'C' );
        $pdf->SetX(18);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, "HUMANIDADES".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
        $pdf->SetX(112);
        $pdf->Cell(16, 6, $aclp1, 1, 0, 'J');
        $pdf->SetX(128);
        $pdf->Cell(16, 6, $aclp2, 1, 0, 'J');
        $pdf->SetX(144);
        $pdf->Cell(16, 6, $aclp3, 1, 0, 'J');
        $pdf->SetX(160);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(16, 6, $acl, 1, 0, 'J',1);
        $pdf->SetX(192);
        $acdefl=($aclp1+$aclp2+$aclp3+$acl)/4;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, round($acdefl,1), 1, 1, 'J',1);
        $pdf->Ln(3.5);
        $aclp1=0;
        $aclp2=0;
        $aclp3=0;
                    //CIENCIAS NATURALES
        $c3=0;
        $acn=0;
        $acihn=0;
        $acnp1=0;
        $acndef=0;
        $proman=0;
        $notap1N=0;
        $acnp1=0;
        $defn=0;
        $ntemp=0;
        $sql1 = "SELECT * from calificaciones where idestudiante='$idest' and periodo='$periodo' and idgrado='$grado' and  idsede='$sede' and orden>4 and orden<=4.3  order by orden asc";
        $res1 = $mysqli->query($sql1);
        while ($reg1 = $res1->fetch_row()) {
          $pdf->SetFont('Arial', '', 8);
          $pdf->SetX(18);
          $nota=$reg1['5'];
          $idasig=$reg1['2'];
          $sqlm = "SELECT c.ihs, c.porcentaje, a.nombre, c.idasignatura from carga_academica c inner join asignaturas a on a.idasignatura=c.idasignatura where c.idasignatura='$idasig' and c.idgrado='$grado' and c.idsede='$sede' ";
          $resm = $mysqli->query($sqlm);
          while ($regm=$resm->fetch_row()) {
            $idasig=$regm[3];
            $notaN=0;
            $sqlnp = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='$periodo'";
            $resnp = $mysqli->query($sqlnp);
            while ( $regnp = $resnp->fetch_row()) {
              $pdf->SetX(176);
              $notaN=$regnp['0'];
              $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
            }
            $notap3N=0;
            $sqlnp3 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='3'";
            $resnp3 = $mysqli->query($sqlnp3);
            $regnp3 = $resnp3->fetch_row();
            $notap3N=$regnp3['0'];

            $notap2N=0;
            $sqlnp2 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='2'";
            $resnp2 = $mysqli->query($sqlnp2);
            $regnp2 = $resnp2->fetch_row();
            $notap2N=$regnp2['0'];

            $notap1N=0;
            $sqlnp1 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='1'";
            $resnp1 = $mysqli->query($sqlnp1);
            $regnp1 = $resnp1->fetch_row();
            $notap1N=$regnp1['0'];

            $sqlcp3 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='3' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig' ";
            $rescp3 = $mysqli->query($sqlcp3);
            $regcp3 = $rescp3->fetch_row();
            if(!empty($regcp3['0'])){
              $ncnp3=$regcp3['0'];
            } else {
             $ncnp3=0;
           }
           $sqlcp2 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='2' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig' ";
           $rescp2 = $mysqli->query($sqlcp2);
           $regcp2 = $rescp2->fetch_row();
           if(!empty($regcp2['0'])){
            $ncnp2=$regcp2['0'];
          } else {
           $ncnp2=0;
         }
         $sqlcp1 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='1' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig' ";
         $rescp1 = $mysqli->query($sqlcp1);
         $regcp1 = $rescp1->fetch_row();
         if(!empty($regcp1['0'])){
          $ncnp1=$regcp1['0'];
        } else {
         $ncnp1=0;
        }

        if (empty($notap1N)) {
         $ntemp=round($ncnp1*($regm['1']/100),1);
         $acnp1=$acnp1+$ntemp;
         $pdf->SetX(112);
         $np1=$ncnp1;
         $pdf->Cell(16, 6, $np1, 1, 0, 'J');
        } else {
          $ntemp=round($notap1N*($regm['1']/100),1);
          $acnp1=$acnp1+$ntemp;
          $np1=$notap1N;
          $pdf->SetX(112);
          $pdf->Cell(16, 6, $notap1N, 1, 0, 'J');
        }
        if (empty($notap2N)) {
         $ntemp=round($ncnp2*($regm['1']/100),1);
         $acnp2=$acnp2+$ntemp;
         $pdf->SetX(128);
         $np2=$ncnp2;
         $pdf->Cell(16, 6, $np2, 1, 0, 'J');
        } else {
          $ntemp=round($notap2N*($regm['1']/100),1);
          $acnp2=$acnp2+$ntemp;
          $np2=$notap2N;
          $pdf->SetX(128);
          $pdf->Cell(16, 6, $np2, 1, 0, 'J');
        }
        if (empty($notap3N)) {
         $ntemp=round($ncnp3*($regm['1']/100),1);
         $acnp3=$acnp3+$ntemp;
         $pdf->SetX(144);
         $np3=$ncnp3;
         $pdf->Cell(16, 6, $np3, 1, 0, 'J');
        } else {
          $ntemp=round($notap3N*($regm['1']/100),1);
          $acnp3=$acnp3+$ntemp;
          $np3=$notap3N;
          $pdf->SetX(144);
          $pdf->Cell(16, 6, $np4, 1, 0, 'J');
        }
        $c3=1;
        $pdf->SetX(10);
        $iha=$regm['0'];
        $acihn=$acihn+$iha;
        $pdf->Cell(8, 6, $regm['0'], 1, 0, 'C');
        $pdf->SetX(18);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(94, 6, utf8_decode($regm['2']).'     '.$regm['1'].' '.utf8_decode("%"), 1, 0, 'J');
        if ($nota<3 && !empty($notaN)) {
          $por=$regm['1']/100;
          $nt=round($notaN*$por,1);
          $defn=($np1+$np2+$np3+$notaN)/4;
          $acn=$acn+$nt;
        } else {
          $por=$regm['1']/100;
          $nt=round($nota*$por,1);
          $defn=($np1+$np2+$np3+$nota)/4;
          $acn=$acn+$nt;
        }
        $pdf->SetX(160);
        $pdf->Cell(16, 6, $reg1['5'], 1, 0, 'J');
        $pdf->SetX(112);
        $nota_a=$reg1['5'];
        $pdf->SetFont('Arial', 'B', 8);
        $proman=round($defn,1);
        $pdf->SetX(192);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, $proman, 1, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        if ($nota_a>=0 && $nota_a<=2.99) {
          $sql3   = "select * from prefijos where idprefijo='1'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        } elseif ($nota_a>=3 && $nota_a<=3.99) {
         $sql3   = "select * from prefijos where idprefijo='2'";
         $res3   = $mysqli->query($sql3);
         $reg = $res3->fetch_row();
         $pre1=$reg['1'];
         $su1=$reg['2'];
        } elseif ($nota_a>=4 && $nota_a<=4.49) {
          $sql3   = "select * from prefijos where idprefijo='3'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        } else {
          $sql3   = "select * from prefijos where idprefijo='4'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        }
        $pdf->SetFont('Arial', '', 9);
        $logro_c = $reg1['7'];
        $logro_d=$reg1['8'];
        $sql3   = "SELECT descripcion from logros_academicos where idlogro='$logro_c' and idasignatura='$idasig'";
        $resl   = $mysqli->query($sql3);
        while ($regl = $resl->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($regl['0'])." ".utf8_decode($su1), 1, 1, 'J',true);

        }
        $sql4   = "SELECT descripcion from logros_academicos where idlogro='$logro_d' and idasignatura='$idasig'";
        $res4   = $mysqli->query($sql4);
        while ($reg4 = $res4->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5',utf8_decode( $reg4['0']), 1, 1, 'J');
        }

        }

        }

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->Cell(8, 6,$acihn , 1, 0, 'C' );
        $pdf->SetX(18);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, "CIENCIAS NATURALES".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
        $pdf->SetX(112);
        $pdf->Cell(16, 6, $acnp1, 1, 0, 'J');
        $pdf->SetX(128);
        $pdf->Cell(16, 6, $acnp2, 1, 0, 'J');
        $pdf->SetX(144);
        $pdf->Cell(16, 6, $acnp3, 1, 0, 'J');
        $pdf->SetX(160);
        $pdf->Cell(16, 6, $acn, 1, 0, 'J',1);
        $pdf->SetX(192);
        $acdefn=($acnp1+$acnp2+$acnp3+$acn)/4;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, round($acdefn,1), 1, 1, 'J',1);
        $pdf->Ln(3.5);
        $acnp2=0;
        $acnp1=0;
        $acnp3=0;
                 //CIENCIAS SOCIALES
        $c4=0;
        $acs=0;
        $acihs=0;
        $acsp1=0;
        $acsdef=0;
        $promas=0;
        $notap1N=0;
        $acsp1=0;
        $defs=0;
        $ntemp=0;
        $sql1 = "SELECT * from calificaciones where idestudiante='$idest' and periodo='$periodo' and idgrado='$grado' and  idsede='$sede' and orden>5 and orden<=5.5  order by orden asc";
        $res1 = $mysqli->query($sql1);
        while ($reg1 = $res1->fetch_row()) {
          $pdf->SetFont('Arial', '', 8);
          $pdf->SetX(18);
          $nota=$reg1['5'];
          $idasig=$reg1['2'];
          $sqlm = "SELECT c.ihs, c.porcentaje, a.nombre, c.idasignatura from carga_academica c inner join asignaturas a on a.idasignatura=c.idasignatura where c.idasignatura='$idasig' and c.idgrado='$grado' and c.idsede='$sede' ";
          $resm = $mysqli->query($sqlm);
          while ($regm=$resm->fetch_row()) {
            $idasig=$regm[3];
            $notaN=0;
            $sqlnp = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='$periodo'";
            $resnp = $mysqli->query($sqlnp);
            while ( $regnp = $resnp->fetch_row()) {
              $pdf->SetX(176);
              $notaN=$regnp['0'];
              $pdf->SetFillColor(232, 232, 232);
              $pdf->Cell(16, 6, $notaN, 1, 0, 'J',1);
            }
            $notap3N=0;
            $sqlnp3 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='3'";
            $resnp3 = $mysqli->query($sqlnp3);
            while ( $regnp3 = $resnp3->fetch_row()) {
              $notap3N=$regnp3['0'];
            }
            $notap2N=0;
            $sqlnp2 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='2'";
            $resnp2 = $mysqli->query($sqlnp2);
            while ( $regnp2 = $resnp2->fetch_row()) {
              $notap2N=$regnp2['0'];
            }
            $notap1N=0;
            $sqlnp1 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='1'";
            $resnp1 = $mysqli->query($sqlnp1);
            while ( $regnp1 = $resnp1->fetch_row()) {
              $notap1N=$regnp1['0'];
            }
            $sqlcp3 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='3' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig' ";
            $rescp3 = $mysqli->query($sqlcp3);
            if ($rescp3) {
              while ( $regcp3 = $rescp3->fetch_row()) {
                $ncsp3=$regcp3['0'];
              }
            } else {
              $ncsp3=0.0;
            }

            $sqlcp2 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='2' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig' ";
            $rescp2 = $mysqli->query($sqlcp2);
            $regcp2 = $rescp2->fetch_row();
            if(!empty($regcp2['0'])){
              $ncsp2=$regcp2['0'];
            } else {
             $ncsp2=0;
           }
           $sqlcp1 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='1' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig' ";
           $rescp1 = $mysqli->query($sqlcp1);
           $regcp1 = $rescp1->fetch_row();
           if(!empty($regcp1['0'])){
            $ncsp1=$regcp1['0'];
          } else {
           $ncsp1=0;
         }

         if (empty($notap1N)) {
           $ntemp=round($ncsp1*($regm['1']/100),1);
           $acsp1=$acsp1+$ntemp;
           $pdf->SetX(112);
           $np1=$ncsp1;
           $pdf->Cell(16, 6, $np1, 1, 0, 'J');
         } else {
          $ntemp=round($notap1N*($regm['1']/100),1);
          $acsp1=$acsp1+$ntemp;
          $np1=$notap1N;
          $pdf->SetX(112);
          $pdf->Cell(16, 6, $notap1N, 1, 0, 'J');
        }
        if (empty($notap2N)) {
         $ntemp=round($ncsp2*($regm['1']/100),1);
         $acsp2=$acsp2+$ntemp;
         $pdf->SetX(128);
         $np2=$ncsp2;
         $pdf->Cell(16, 6, $np2, 1, 0, 'J');
        } else {
          $ntemp=round($notap2N*($regm['1']/100),1);
          $acsp2=$acsp2+$ntemp;
          $np2=$notap2N;
          $pdf->SetX(128);
          $pdf->Cell(16, 6, $np2, 1, 0, 'J');
        }
        if (empty($notap3N)) {
         $ntemp=round($ncsp3*($regm['1']/100),1);
         $acsp3=$acsp3+$ntemp;
         $pdf->SetX(144);
         $np3=$ncsp3;
         $pdf->Cell(16, 6, $np3, 1, 0, 'J');
        } else {
          $ntemp=round($notap2N*($regm['1']/100),1);
          $acsp3=$acsp3+$ntemp;
          $np3=$notap3N;
          $pdf->SetX(144);
          $pdf->Cell(16, 6, $np3, 1, 0, 'J');
        }

        $c4=1;
        $pdf->SetX(10);
        $iha=$regm['0'];
        $acihs=$acihs+$iha;
        $pdf->Cell(8, 6, utf8_decode($regm['0']), 1, 0, 'C');
        $pdf->SetX(18);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, $regm['2'].'     '.$regm['1'].' '.utf8_decode("%"), 1, 0, 'J');
        if ($nota<3 && !empty($notaN)) {
          $por=$regm['1']/100;
          $nt=round($notaN*$por,1);
          $defs=($np1+$np2+$np3+$notaN)/4;
          $acs=$acs+$nt;
        } else {
          $por=$regm['1']/100;
          $nt=round($nota*$por,1);
          $defs=($np1+$np2+$np3+$nota)/4;
          $acs=$acs+$nt;
        }
        $pdf->SetX(160);
        $pdf->Cell(16, 6, $reg1['5'], 1, 0, 'J');
        $pdf->SetX(112);
        $nota_a=$reg1['5'];
        $pdf->SetFont('Arial', 'B', 8);
        $promas=round($defs,1);
        $pdf->SetX(192);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(16, 6, $promas, 1, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        if ($nota_a>=0 && $nota_a<=2.99) {
          $sql3   = "select * from prefijos where idprefijo='1'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];

        } elseif ($nota_a>=3 && $nota_a<=3.99) {
         $sql3   = "select * from prefijos where idprefijo='2'";
         $res3   = $mysqli->query($sql3);
         $reg = $res3->fetch_row();
         $pre1=$reg['1'];
         $su1=$reg['2'];
        } elseif ($nota_a>=4 && $nota_a<=4.49) {
          $sql3   = "select * from prefijos where idprefijo='3'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        } else {
          $sql3   = "select * from prefijos where idprefijo='4'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        }
        $pdf->SetFont('Arial', '', 9);
        $logro_c = $reg1['7'];
        $logro_d=$reg1['8'];
        $sql3   = "SELECT descripcion from logros_academicos where idlogro='$logro_c' and idasignatura='$idasig'";
        $resl   = $mysqli->query($sql3);
        while ($regl = $resl->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($regl['0'])." ".utf8_decode($su1), 1, 1, 'J',true);
        }
        $sql4   = "SELECT descripcion from logros_academicos where idlogro='$logro_d' and idasignatura='$idasig'";
        $res4   = $mysqli->query($sql4);
        while ($reg4 = $res4->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5',utf8_decode( $reg4['0']), 1, 1, 'J');
        }
        }
        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->Cell(8, 6,$acihs , 1, 0, 'C' );
        $pdf->SetX(18);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, "CIENCIAS SOCIALES".'    '.utf8_decode('100%'), 1, 0, 'J', 1);
        $pdf->SetX(112);
        $pdf->Cell(16, 6, $acsp1, 1, 0, 'J');
        $pdf->SetX(128);
        $pdf->Cell(16, 6, $acsp2, 1, 0, 'J');
        $pdf->SetX(144);
        $pdf->Cell(16, 6, $acsp3, 1, 0, 'J');
        $pdf->SetX(160);
        $pdf->Cell(16, 6, $acs, 1, 0, 'J',1);
        $pdf->SetX(192);
        $acdefs=($acsp1+$acsp2+$acsp3+$acs)/4;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, round($acdefs,1), 1, 1, 'J',1);
        $pdf->Ln(3.5);
        $acsp1=0;
        $acsp2=0;
        $acsp3=0;

        $pdf->SetFillColor(255, 255, 255);
        //OTRAS MATERIAS
        $act=0;
        $c5=0;
        $ac5=0;
        $acomp1=0;
        $sql1 = "SELECT * from calificaciones where idestudiante='$idest'  and periodo='$periodo' and idgrado='$grado' and idsede='$sede' and orden>=5.9  order by orden asc";
        $res1 = $mysqli->query($sql1);
        while ($reg1 = $res1->fetch_row()) {
          $c5++;
          $notaN=0;
          $ac5=$ac5+$c5;
          $pdf->SetFont('Arial', '', 8);
          $pdf->SetX(18);
          $idasig=$reg1['2'];
          $sqlm = "SELECT c.ihs, c.porcentaje, a.nombre, c.idasignatura from carga_academica c inner join asignaturas a on a.idasignatura=c.idasignatura where c.idasignatura='$idasig' and c.idgrado='$grado' and c.idsede='$sede' ";
          $resm = $mysqli->query($sqlm);
          while ($regm=$resm->fetch_row()) {
            $sqln1 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='$periodo'";
            $resn1 = $mysqli->query($sqln1);
            $nv1=$resn1->num_rows;
            while ( $regn1 = $resn1->fetch_row()) {
              $pdf->SetX(176);
              $pdf->SetFillColor(232, 232, 232);
              $pdf->SetFont('Arial', 'B', 8);
              $pdf->Cell(16, 6, $regn1['0'], 1, 0, 'J',1);
              $notaN=$regn1['0'];
            }
            $notap3N=0;
            $sqlnp3 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='3'";
            $resnp3 = $mysqli->query($sqlnp3);
            while ( $regnp3 = $resnp3->fetch_row()) {
              $notap3N=$regnp3['0'];
            }
            $notap2N=0;
            $sqlnp2 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='2'";
            $resnp2 = $mysqli->query($sqlnp2);
            while ( $regnp2 = $resnp2->fetch_row()) {
              $notap2N=$regnp2['0'];
            }
            $notap1N=0;
            $sqlnp1 = "SELECT nota from nivelaciones where idestudiante='$idest'  and idasignatura='$idasig' and periodo ='1'";
            $resnp1 = $mysqli->query($sqlnp1);
            while ( $regnp1 = $resnp1->fetch_row()) {
              $notap1N=$regnp1['0'];
            }
                    //Calificaciones
            $sqlcp3 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='3' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig'";
            $rescp3 = $mysqli->query($sqlcp3);
            $regcp3 = $rescp3->fetch_row();
            if (!empty($regcp3['0'])) {
             $ncomp3=$regcp3['0'];
           } else {
             $ncomp3=0;
           }
           $sqlcp2 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='2' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig'";
           $rescp2 = $mysqli->query($sqlcp2);
           $regcp2= $rescp2->fetch_row();
           if (!empty($regcp2['0'])) {
             $ncomp2=$regcp2['0'];
           } else {
             $ncomp2=0;
           }

           $sqlcp1 = "SELECT nota from calificaciones where idestudiante='$idest' and periodo='1' and idgrado='$grado' and  idsede='$sede' and idasignatura='$idasig'";
           $rescp1 = $mysqli->query($sqlcp1);
           $regcp1 = $rescp1->fetch_row();
           if (!empty($regcp1['0'])) {
             $ncomp1=$regcp1['0'];
           } else {
             $ncomp1=0;
           }

           if (empty($notap1N)) {
            $pdf->SetX(112);
            $np1=$ncomp1;
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(16, 6, $np1, 1, 0, 'J');
          } else {
           $np1=$notap1N;
           $pdf->SetX(112);
           $pdf->Cell(16, 6, $np1, 1, 0, 'J');
         }
         if (empty($notap2N)) {
          $pdf->SetX(128);
          $np2=$ncomp2;
          $pdf->SetFont('Arial', 'B', 8);
          $pdf->Cell(16, 6, $np2, 1, 0, 'J');
        } else {
         $np2=$notap2N;
         $pdf->SetX(128);
         $pdf->Cell(16, 6, $np2, 1, 0, 'J');
        }
        if (empty($notap3N)) {
          $pdf->SetX(144);
          $np3=$ncomp3;
          $pdf->SetFont('Arial', 'B', 8);
          $pdf->Cell(16, 6, $np3, 1, 0, 'J');
        } else {
         $np3=$notap3N;
         $pdf->SetX(144);
         $pdf->Cell(16, 6, $np3, 1, 0, 'J');
        }
        $pdf->SetX(10);
        $pdf->Cell(8, 6, $regm['0'], 1, 0, 'C');
        $pdf->SetX(18);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(94, 6, utf8_decode($regm['2']).'     '.$regm['1'].' '.utf8_decode("%"), 1, 0, 'J',1);
        $nota_a=$reg1[5];
        if ($nota_a<3 && !empty($notaN)) {
          $por=$regm['1']/100;
          $nt=round($notaN*$por,2);
          $def=($np1+$np2+$np3+$nota_a)/4;
          $act=$act+$notaN;
        } else {
          $por=$regm['1']/100;
          $nt=round($nota_a*$por,2);
          $act=$act+$nota_a;
          $def=($np1+$np2+$np3+$nota_a)/4;
        }
        $pdf->SetX(160);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(16, 6, $reg1['5'], 1, 0, 'J',1);
        $pdf->SetX(112);
        $nota_a=$reg1['5'];
        $pdf->SetFont('Arial', 'B', 8);
        $promom=round($def,1);
        $pdf->SetX(192);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(16, 6, $promom, 1, 1, 'J',1);
        $pdf->SetFont('Arial', '', 8);
        if ($nota_a>=0 && $nota_a<=2.99) {
          $sql3   = "select * from prefijos where idprefijo='1'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];

        } elseif ($nota_a>=3 && $nota_a<=3.99) {
         $sql3   = "select * from prefijos where idprefijo='2'";
         $res3   = $mysqli->query($sql3);
         $reg = $res3->fetch_row();
         $pre1=$reg['1'];
         $su1=$reg['2'];
        } elseif ($nota_a>=4 && $nota_a<=4.49) {
          $sql3   = "select * from prefijos where idprefijo='3'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        } else {
          $sql3   = "select * from prefijos where idprefijo='4'";
          $res3   = $mysqli->query($sql3);
          $reg = $res3->fetch_row();
          $pre1=$reg['1'];
          $su1=$reg['2'];
        }
        $pdf->SetFont('Arial', '', 9);
        $logro_c = $reg1['7'];
        $logro_d=$reg1['8'];
        $sql3   = "SELECT descripcion from logros_academicos where idlogro='$logro_c' and idasignatura='$idasig'";
        $resl   = $mysqli->query($sql3);
        while ($regl = $resl->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5', utf8_decode($pre1)." ".utf8_decode($regl['0'])." ".utf8_decode($su1), 1, 1, 'J',true);

        }
        $sql4   = "SELECT descripcion from logros_academicos where idlogro='$logro_d' and idasignatura='$idasig'";
        $res4   = $mysqli->query($sql4);
        while ($reg4 = $res4->fetch_row()) {
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell('198', '5',utf8_decode( $reg4['0']), 1, 1, 'J');
        }
        }
        $pdf->Ln(3.5);
        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->Cell(198, 6, ' DISCIPLINA ', 1, 1, 'J', 1);
        $sqlc=  "SELECT l.descripcion  from convivencia_escolar c inner join logros_disciplinarios l on c.idlogro_dis=l.idlogro_dis where idestudiante='$idest' and c.idgrado='$grado' and c.periodo='$periodo' and c.idsede='$sede' ";
        $resc = $mysqli->query($sqlc);
        $regc=$resc->fetch_row();
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell('198', '5', utf8_decode( $regc['0']), 1, 1, 'J');
        $tac=$ac+$acl+$acn+$acs+$act;
        $tc=$c1+$c2+$c3+$c4+$c5;
        $promg=$tac/$tc;
        $nprom=round($promg, 1);
        if ($nprom>=1 && $nprom<=2.99) {
          $des="DESEMPEÑO BAJO";
        } elseif ($nprom>=3 && $nprom<=3.99) {
         $des="DESEMPEÑO BÁSICO";
        }elseif ($nprom>=4 && $nprom<=4.49) {
         $des="DESEMPEÑO ALTO";
        } elseif($nprom>=4.5){
          $des="DESEMPEÑO SUPERIOR";
        }
        else {
         $des="";
        }
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(198, 6, ' PROMEDIO DE PERIODO: '.$nprom.'        '.utf8_decode($des), 1, 1, 'J', 1);
        $pr=round($promg, 2);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(198, 6, ' OBSERVACIONES', 1, 1, 'J', 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(198, 9, ' ', 1, 1, 'J');
        $pdf->Ln(15);
        $pdf->Cell(80, 0, ' ', 1, 1, 'J');
        $pdf->Ln(0.5);
        $sql= "SELECT p.nombres, p.apellidos  from direccion_grado d inner join personal p on d.iddocente=p.idpersonal where d.idgrado='$grado' and d.idsede='$sede'";
        $resa = $mysqli->query($sql);
        $rega = $resa->fetch_row();
        $nom_ac=$rega['0'].' '.$rega['1'];
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(190, 4,utf8_decode($nom_ac) , 0, 1, 'J');
        $pdf->Cell(40, 6, 'Director de Grupo', 0, 1, 'J');
        $pdf->Ln();

        }

        $i++;

        }
        break;
        break;

        default:
        echo "error";
        break;
        }
        $base64String = chunk_split(base64_encode($pdf->Output('S')));
        return $base64String;
        //$pdf->Output();
        exit;

    }



}


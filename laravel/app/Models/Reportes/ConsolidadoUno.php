<?php

namespace App\Models\Reportes;

use App\Models\Calificacion;
use App\Models\CargaAcademica;
use App\Models\Nivelacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsolidadoUno extends Model
{


    public static function reporte($sede, $grado, $periodo, $pdf, $matriculas){
        $promedios=[];
        $base64String ="";
        Auxiliar::headerConsolidados($sede, $grado, $periodo, $pdf);
        $pdf->Cell(80, 6, '', 0, 'C', 1);
      
        $pdf->Ln();
        $pdf->Cell(8, 5, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(72, 5, 'ESTUDIANTES', 1, 0, 'C', 1);
            $pdf->SetFont('Arial', 'B', 8);
   
         //Otras Materias
         $acronimos=Calificacion::asignaturaCalificacion($grado, $periodo, 0, 30 );
         foreach ($acronimos as $item) {
                   # code...
                 $pdf->SetFont('Arial', 'B', 8);
                 $pdf->Cell(15, 5, utf8_decode($item->acronimo),  1, 0, 'J');
             }
          $pdf->Cell(15, 5, 'PER',  1, 0, 'J');
        $pdf->Cell(15, 5, 'GAN',  1, 0, 'J');
        $pdf->Cell(15, 5, 'PROM',  1, 0, 'J', 1);
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
            $nom = utf8_decode($matricula->apellidos.' '.$matricula->nombres);
            $pdf->Cell(8, 5, $i, 1, 0, 'J');
            $pdf->Cell(72, 5, ($nom), 1, 0, 'J');
            


    //Otras Materias
                $numn1=0;
                $calificaciones=Calificacion::calificacionOrden($idest, $periodo, 0, 30 );
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
                     $pdf->Cell(15, 5, $notaNo, 1,0 , 'J');
                     $acom=$acom+$notaNo;
                     if ($notaNo<3) {
                      $cp++;
                     }

                    } else {
                      $pdf->Cell(15, 5, $notao, 1,0 , 'J', 1);
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

                $rprom=round($prom,1);
                $promedios[$nom]=($rprom );
                $pdf->Cell(15, 5, $cp, 1,0 , 'J',0);
                $pdf->Cell(15, 5, $cg, 1,0 , 'J',0);
          $pdf->Cell(15, 5, $rprom, 1,0 , 'J',1);

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
    //$pdf->Output();
    $base64String = chunk_split(base64_encode($pdf->Output('S')));
    return $base64String;
    exit;

    }






}

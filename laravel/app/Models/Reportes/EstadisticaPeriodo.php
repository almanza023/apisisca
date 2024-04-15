<?php

namespace App\Models\Reportes;

use App\Models\CargaAcademica;
use App\models\Convivencia;
use App\models\DireccionGrado;
use App\Models\LogroDisciplinario;
use App\Models\Nivelacion;
use App\Models\Prefijo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadisticaPeriodo extends Model
{

   public static function reporte($pdf, $data, $periodo){
    $base64String ="";
    $titulo='ESTADISTICAS DE PERIODO';
    Auxiliar::headerPdf($pdf, $periodo, $titulo);
    $pdf->SetFont('Arial', '', 10);
    $grado=$data[0]->matricula->grado->descripcion;
    $sede=$data[0]->matricula->sede->nombre;
    $pdf->Cell(190, 5, 'GRADO: ' .$grado, 1, 1);
    $pdf->Cell(60, 5, 'SEDE: ' . $sede, 1, 0, 'J');
    $pdf->Cell(60, 5, 'PERIODO: ' . $periodo, 1, 0, 'J');
    $pdf->Cell(70, 5, utf8_decode(' AÑO: 2024 '), 1, 1, 'J');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(10, 6, 'PUE  ', 1, 0, 'C', 1);
        $pdf->Cell(80, 6, utf8_decode('ESTUDIANTE'), 1, 0, 'J', 1);
        $pdf->Cell(10, 6, 'GAN ', 1, 0, 'C', 1);
        $pdf->Cell(10, 6, 'PER  ', 1, 0, 'C', 1);
        $pdf->Cell(10, 6, 'PRO', 1, 0, 'C', 1);
        $pdf->Cell(70, 6, 'ACUDIENTE  ', 1, 1, 'C', 1);
        $pdf->SetFont('Arial', '', 9);
        $c=0;
        foreach ($data as $item) {
            $c++;
            $nom=$item->matricula->estudiante->apellidos.' '.$item->matricula->estudiante->nombres;
            $pdf->Cell(10, 6, $c  , 1, 0, 'C');
            $pdf->Cell(80, 6, utf8_decode($nom), 1, 0, 'J');
            $pdf->Cell(10, 6, $item->areasg, 1, 0, 'C');
            $pdf->Cell(10, 6,  $item->areasp, 1, 0, 'C');
            $pdf->Cell(10, 6,  $item->promedio, 1, 0, 'C');

            $pdf->Cell(70, 6, '  ', 1, 1, 'C');
        }
        $base64String = chunk_split(base64_encode($pdf->Output('S')));
        return $base64String;
    exit;

   }
}










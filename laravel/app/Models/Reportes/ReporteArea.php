<?php

namespace App\Models\Reportes;


use Illuminate\Database\Eloquent\Model;

class ReporteArea extends Model
{

   public static function reporte($pdf, $data, $periodo){
    $base64String ="";
    $titulo='DEFINITIVA POR AREA';
    Auxiliar::headerPdf($pdf, $periodo, $titulo);
    $pdf->SetFont('Arial', '', 10);
    $grado=$data[0]->grado;
    $sede=$data[0]->sede;
    $pdf->Cell(190, 5, 'GRADO: ' .$grado, 1, 1);
    $pdf->Cell(60, 5, 'SEDE: ' . $sede, 1, 0, 'J');
    $pdf->Cell(60, 5, 'PERIODO: ' . $periodo, 1, 0, 'J');
    $pdf->Cell(70, 5, utf8_decode(' AÑO: 2024 '), 1, 1, 'J');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(10, 6, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(70, 6, utf8_decode('ESTUDIANTE'), 1, 0, 'J', 1);
        $pdf->Cell(40, 6, utf8_decode('EST 20%'), 1, 0, 'C', 1);
        $pdf->Cell(40, 6, utf8_decode('MAT  80%'), 1, 0, 'C', 1);
        $pdf->Cell(30, 6, utf8_decode('DEF 100%'), 1, 0, 'C', 1);    
        $pdf->Ln();   
        $pdf->SetFont('Arial', '', 9);
        $c=0;
        foreach ($data as $item) {
            $c++;
            $nom=$item->estudiante;
            $pdf->Cell(10, 6, $c  , 1, 0, 'C');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(70, 6, utf8_decode($nom), 1, 0, 'J');
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(20, 6, $item->notaasig1, 1, 0, 'C');
            $pdf->Cell(20, 6, $item->asig1, 1, 0, 'C',1);
            $pdf->Cell(20, 6, $item->notasig2, 1, 0, 'C');
            $pdf->Cell(20, 6,  $item->asig2, 1, 0, 'C',1);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(30, 6,  $item->definitiva, 1, 1, 'C',1);           
        }
        $base64String = chunk_split(base64_encode($pdf->Output('S')));
        return $base64String;
    exit;

   }
}










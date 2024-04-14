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

class ReporteSubidaDocente extends Model
{

    public static function reporte($pdf, $cabecera, $data, $periodo)
    {
        Auxiliar::headerPdf($pdf, $periodo, 'REPORTE SUBIDA DE CALIFICACIONES DOCENTE');

        $pdf->Cell(190, 15, '', 1, 0, 'J');
        $pdf->Ln(4);


        $pdf->Cell(150, 6, 'PERIODO: ' . $periodo, 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(10);
        $pdf->Cell(20, 6, utf8_decode('GRADO: ') . utf8_decode($cabecera['grado']), 0, 0, 'J');
        $pdf->SetX(80);
        $pdf->Cell(20, 6, utf8_decode('SEDE: ') . utf8_decode($cabecera['sede']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(170);
        $pdf->Cell(20, 6, utf8_decode('AÑO: 2024'), 0, 0, 'J');
        $pdf->Ln(20);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 6, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(90, 6, utf8_decode('DOCENTE'), 1, 0, 'J', 1);
        $pdf->Cell(60, 6, utf8_decode('AREA/ASIGNATURA'), 1, 0, 'J', 1);
        $pdf->Cell(30, 6, utf8_decode('TOTAL'), 1, 0, 'J', 1);
        $pdf->Ln();
        $i=0;
        foreach ($data as  $item) {
            $i++;
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(10, 6, $i, 1, 0, 'J');
            $pdf->Cell(90, 6, utf8_decode($item['docente']), 1, 0, 'J');
            $pdf->Cell(60, 6, utf8_decode($item['asignatura']), 1, 0, 'J');
            $pdf->Cell(30, 6, $item['total'], 1, 0, 'J');
            $pdf->Ln();
        }

        $pdf->Output();
        exit;



    }
}

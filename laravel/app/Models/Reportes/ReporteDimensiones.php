<?php

namespace App\Models\Reportes;


use Illuminate\Database\Eloquent\Model;

class ReporteDimensiones extends Model
{

    public static function reporte($pdf, $cabecera, $data, $periodo)
    {
        Auxiliar::headerPdf($pdf, $periodo, 'CALIFICACION DE DIMENSION DE PERIODO');

        $pdf->Cell(190, 15, '', 1, 0, 'J');
        $pdf->Ln(4);

        $pdf->Cell(100, 6, utf8_decode('DIMENSION: ') . utf8_decode($cabecera['asignatura']), 0, 0, 'J');
        $pdf->Cell(130, 6, 'PERIODO: ' . $periodo, 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->Cell(120, 6, 'DOCENTE: ' . utf8_decode($cabecera['docente']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(110);
        $pdf->Cell(20, 6, utf8_decode('SEDE: ') . utf8_decode($cabecera['sede']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(170);
        $pdf->Cell(20, 6, utf8_decode('AÑO: 2024'), 0, 0, 'J');
        $pdf->Ln(20);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 6, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(80, 6, utf8_decode('ESTUDIANTE'), 1, 0, 'J', 1);
        $pdf->Cell(25, 6, utf8_decode('LOGRO N° 1'), 1, 0, 'J', 1);
        $pdf->Cell(25, 6, utf8_decode('LOGRO N° 2'), 1, 0, 'J', 1);
        $pdf->Cell(25, 6, utf8_decode('LOGRO N° 3'), 1, 0, 'J', 1);
        $pdf->Cell(25, 6, utf8_decode('LOGRO N° 4'), 1, 0, 'J', 1);
        $pdf->Ln();
        $i=0;
        foreach ($data as  $item) {
            $i++;
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(10, 6, $i, 1, 0, 'J');
            $pdf->Cell(80, 6, utf8_decode($item->apellidos . ' ' . $item->nombres), 1, 0, 'J');
            $pdf->Cell(25, 6, $item->logro_a, 1, 0, 'J');
            $pdf->Cell(25, 6, $item->logro_b, 1, 0, 'J');
            $pdf->Cell(25, 6, $item->logro_c, 1, 0, 'J');
            $pdf->Cell(25, 6, $item->logro_d, 1, 0, 'J');
            $pdf->Ln();
        }

        $pdf->Ln(60);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, utf8_decode('Pagína N°: ') . $pdf->PageNo(), 0, 0, 'R');
        $pdf->Ln();
        $pdf->Cell(0, 10, 'Impreso por SIA INEDA 2024', 0, 0, 'R');
        $pdf->Output();
        exit;



    }
}

<?php

namespace App\Models\Reportes;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteNotas extends Model
{

    public static function reporte($pdf, $cabecera, $data, $periodo)
    {
        $base64String ="";
        Auxiliar::headerPdf($pdf, $periodo, 'CALIFICACIONES  DE PERIODO');

        $pdf->Cell(190, 15, '', 1, 0, 'J');
        $pdf->Ln(4);

        $pdf->Cell(120, 6, utf8_decode('AREA/ASIGNATURA: ') . utf8_decode($cabecera['asignatura']), 0, 0, 'J');
        $pdf->Cell(150, 6, 'PERIODO: ' . $periodo, 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->Cell(120, 6, 'DOCENTE: ' . utf8_decode($cabecera['docente']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(130);
        $pdf->Cell(20, 6, utf8_decode('SEDE: ') . utf8_decode($cabecera['sede']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(170);
        $pdf->Cell(20, 6, utf8_decode('AÑO: 2024'), 0, 0, 'J');
        $pdf->Ln(20);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 6, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(100, 6, utf8_decode('ESTUDIANTE'), 1, 0, 'J', 1);
        $pdf->Cell(40, 6, utf8_decode('NOTA DE PERIODO'), 1, 0, 'J', 1);
        $pdf->Cell(40, 6, utf8_decode('FECHA SUBIDA'), 1, 0, 'J', 1);
        $pdf->Ln();
        $i=0;
        foreach ($data as  $item) {
            $i++;
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(10, 6, $i, 1, 0, 'J');
            $pdf->Cell(100, 6, utf8_decode($item->apellidos . ' ' . $item->nombres), 1, 0, 'J');
            $pdf->Cell(40, 6, $item->nota, 1, 0, 'J');
            $pdf->Cell(40, 6, $item->created_at, 1, 0, 'J');
            $pdf->Ln();
        }

        $base64String = chunk_split(base64_encode($pdf->Output('S')));
        return $base64String;
        exit;



    }

    public static function reporteMatricula($pdf, $cabecera, $data)
    {
        $base64String ="";
        Auxiliar::headerPdf($pdf, '', 'LISTADO DE ESTUDIANTES');
        $pdf->Cell(190, 15, '', 1, 0, 'J');
        $pdf->Ln(3);
        $pdf->Cell(120, 6, utf8_decode('SEDE: ') . utf8_decode($cabecera['sede']), 0, 0, 'J');
        $pdf->SetX(170);
        $pdf->Cell(20, 6, utf8_decode('AÑO: 2024'), 0, 0, 'J');
        $pdf->Ln(20);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 6, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(100, 6, utf8_decode('ESTUDIANTE'), 1, 0, 'J', 1);
        $pdf->Cell(30, 6, utf8_decode('N° DOC'), 1, 0, 'J', 1);
        $pdf->Cell(30, 6, utf8_decode('FECHA NAC'), 1, 0, 'J', 1);
        $pdf->Cell(20, 6, utf8_decode('N° FOL'), 1, 0, 'J', 1);
        $pdf->Ln();
        $i=0;
        foreach ($data as  $item) {
            $i++;
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(10, 6, $i, 1, 0, 'J');
            $pdf->Cell(100, 6, utf8_decode($item->apellidos . ' ' . $item->nombres), 1, 0, 'J');
            $pdf->Cell(30, 6, $item->num_doc, 1, 0, 'J');
            $pdf->Cell(30, 6, $item->fecha_nac, 1, 0, 'J');
            $pdf->Cell(20, 6, $item->folio, 1, 0, 'J');
            $pdf->Ln();
        }

        $base64String = chunk_split(base64_encode($pdf->Output('S')));
        return $base64String;
        exit;



    }

    public static function reporteValoraciones($pdf, $cabecera, $data)
    {
        $base64String ="";
        Auxiliar::headerPdf($pdf, "", 'VALORACIONES');
        $pdf->Cell(190, 15, '', 1, 0, 'J');
        $pdf->Ln(4);
        $pdf->Cell(120, 6, utf8_decode('DESARROLLO: ') . utf8_decode($cabecera['asignatura']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->Cell(120, 6, 'DOCENTE: ' . utf8_decode($cabecera['docente']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(130);
        $pdf->Cell(20, 6, utf8_decode('SEDE: ') . utf8_decode($cabecera['sede']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(170);
        $pdf->Cell(20, 6, utf8_decode('AÑO: 2024'), 0, 0, 'J');
        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(15, 6, utf8_decode('COD'), 1, 0, 'C', 1);
        $pdf->Cell(175, 6, utf8_decode('VALORACION'), 1, 0, 'J', 1);
        $pdf->Ln();
        $i=0;
        foreach ($data as  $item) {
            $i++;
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(15, 6, $item->id, 1, 0, 'J');
            $pdf->MultiCell('175', '5', utf8_decode($item->descripcion), 1, 1, 'J',true);
            $pdf->Ln();
        }
        $base64String = chunk_split(base64_encode($pdf->Output('S')));
        return $base64String;
        exit;
    }

    public static function reporteNivelacion($pdf, $cabecera, $data, $periodo)
    {
        $base64String ="";
        Auxiliar::headerPdf($pdf, $periodo, 'NIVELACIONES  DE SEMESTRE');

        $pdf->Cell(190, 15, '', 1, 0, 'J');
        $pdf->Ln(4);

        $pdf->Cell(120, 6, utf8_decode('AREA/ASIGNATURA: ') . utf8_decode($cabecera['asignatura']), 0, 0, 'J');
        $pdf->Cell(150, 6, 'SEMESTRE: ' . $periodo, 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->Cell(120, 6, 'DOCENTE: ' . utf8_decode($cabecera['docente']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(130);
        $pdf->Cell(20, 6, utf8_decode('SEDE: ') . utf8_decode($cabecera['sede']), 0, 0, 'J');
        $pdf->SetY(48);
        $pdf->SetX(170);
        $pdf->Cell(20, 6, utf8_decode('AÑO: 2024'), 0, 0, 'J');
        $pdf->Ln(20);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(5, 6, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(80, 6, utf8_decode('ESTUDIANTE'), 1, 0, 'J', 1);
        $pdf->Cell(20, 6, utf8_decode('NOTA ANT'), 1, 0, 'J', 1);
        $pdf->Cell(20, 6, utf8_decode('NOTA PERIODO'), 1, 0, 'J', 1);
        $pdf->Cell(20, 6, utf8_decode('PROMEDIO'), 1, 0, 'J', 1);
        $pdf->Cell(20, 6, utf8_decode('NOTA NIV'), 1, 0, 'J', 1);
        $pdf->Cell(25, 6, utf8_decode('FEC SUBIDA'), 1, 0, 'J', 1);
        $pdf->Ln();
        $i=0;
        foreach ($data as  $item) {
            $i++;
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(5, 6, $i, 1, 0, 'J');
            $pdf->Cell(80, 6, utf8_decode($item->apellidos . ' ' . $item->nombres), 1, 0, 'J');
            $pdf->Cell(20, 6, $item->notaanterior, 1, 0, 'J');
            $pdf->Cell(20, 6, $item->notaperiodo, 1, 0, 'J');
            $pdf->Cell(20, 6, $item->promedio, 1, 0, 'J');
            $pdf->Cell(20, 6, $item->nota, 1, 0, 'J');
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(25, 6, $item->created_at, 1, 0, 'J');
            $pdf->Ln();
        }

        $base64String = chunk_split(base64_encode($pdf->Output('S')));
        return $base64String;
        exit;



    }
}

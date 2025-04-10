<?php

namespace App\Models\Reportes;


use App\Models\Convivencia;
use App\Models\DireccionGrado;
use App\Models\Grado;
use App\Models\LogroDisciplinario;

use App\Models\Sede;

use Illuminate\Database\Eloquent\Model;

class Auxiliar extends Model
{

    public static function headerPdf($pdf, $periodo, $titulo)
    {
        $institucion = "INSTITUCION EDUCATIVA DON ALONSO";
        $lema = 'SABER - ESFUERZO - ESPERANZA';
        $path = public_path() . '/logo.png';
        $path2 = public_path() . '/bandera.jpg';
        $codIcfes = '092908 - 128413';
        $jornada = 'MATINAL';
        $url = 'http://iedonalonso.com.co';
        $pdf->AddPage();
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(190, 6, utf8_decode($institucion), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(190, 4, utf8_decode($lema), 0, 1, 'C');
        $pdf->Cell(190, 4, utf8_decode(' Plantel de Carácter Oficial'), 0, 1, 'C');
        $pdf->Cell(190, 4, utf8_decode(' Resolución N° 1072 Mayo 31/04 y 1566 Agosto 06/04'), 0, 1, 'C');
        $pdf->Cell(190, 6, utf8_decode(' Código Icfes ' . $codIcfes), 0, 1, 'C');
        $pdf->Cell(190, 6, utf8_decode($url), 0, 1, 'C');
        $pdf->Image($path, 8, 6, 20);
        $pdf->Image($path2, 180, 8, 20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Ln(2);
        $pdf->Cell(190, 6, utf8_decode($titulo), 1, 1, 'C', 1);
    }

    public static function cabecera($pdf, $matricula, $periodo)
    {
        $institucion = "INSTITUCION EDUCATIVA DON ALONSO";
        $lema = 'SABER - ESFUERZO - ESPERANZA';
        $path = public_path() . '/logo.png';
        $path2 = public_path() . '/bandera.jpg';
        $codIcfes = '092908 - 128413';
        $jornada = 'MATINAL';
        $url = 'http://iedonalonso.com.co';
        $pdf->AddPage();
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(190, 6, utf8_decode($institucion), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(190, 4, utf8_decode($lema), 0, 1, 'C');
        $pdf->Cell(190, 4, utf8_decode(' Plantel de Carácter Oficial'), 0, 1, 'C');
        $pdf->Cell(190, 4, utf8_decode(' Resolución N° 1072 Mayo 31/04 y 1566 Agosto 06/04'), 0, 1, 'C');
        $pdf->Cell(190, 6, utf8_decode(' Código Icfes ' . $codIcfes), 0, 1, 'C');
        $pdf->Cell(190, 6, utf8_decode($url), 0, 1, 'C');
        $pdf->Image($path, 8, 6, 20);
        $pdf->Image($path2, 180, 8, 20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Ln(2);
        $pdf->Cell(198, 6, utf8_decode(' INFORME ACADEMICO Y/O DISCIPLINARIO'), 1, 1, 'C', 1);
        $pdf->SetFont('Arial', '', 8);
        $nom = $matricula->apellidos . ' ' . $matricula->nombres;
        $pdf->Cell(113, 4, 'Nombres: ' . $nom, 1, 0, 'J');
        $pdf->Cell(47, 4, 'Grado: ' . $matricula->grado, 1, 0, 'J');
        $pdf->Cell(38, 4, 'Periodo: ' . $periodo, 1, 1, 'J');
        $pdf->Cell(40, 5, 'Sede: ' . $matricula->sede, 1, 0, 'J');
        $pdf->Cell(40, 5, utf8_decode(' N° Doc: ') . $matricula->num_doc, 1, 0, 'J');
        $pdf->Cell(33, 5, utf8_decode(' N° Folio: ') . $matricula->folio, 1, 0, 'J');
        $pdf->Cell(47, 5, utf8_decode(' Jornada: ') . $jornada, 1, 0, 'J');
        $pdf->Cell(38, 5, utf8_decode(' Año: 2024 '), 1, 1, 'J');
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(102, 6, ' ', 0, 0, 'J');
        $pdf->Cell(96, 6, 'PERIODOS  ', 1, 1, 'C', 1);
        $pdf->Cell(8, 6, ' IHS', 1, 0, 'C', 1);
        $pdf->Cell(94, 6, utf8_decode('ARÉAS Y/O ASIGNATURAS: '), 1, 0, 'J', 1);
        $pdf->Cell(16, 6, 'I  ', 1, 0, 'C', 1);
        switch ($periodo) {
            case '1':
                $pdf->Cell(16, 6, 'NIV  ', 1, 0, 'C', 1);
                $pdf->Cell(16, 6, 'II  ', 1, 0, 'C', 1);
                $pdf->Cell(16, 6, 'III  ', 1, 0, 'C', 1);
                break;
            case '2':
                $pdf->Cell(16, 6, 'II  ', 1, 0, 'C', 1);
                $pdf->Cell(16, 6, 'NIV  ', 1, 0, 'C', 1);
                $pdf->Cell(16, 6, 'III  ', 1, 0, 'C', 1);
                break;
            case '3':
                $pdf->Cell(16, 6, 'II  ', 1, 0, 'C', 1);
                $pdf->Cell(16, 6, 'III  ', 1, 0, 'C', 1);
                $pdf->Cell(16, 6, 'NIV  ', 1, 0, 'C', 1);
                break;
            default:
                # code...
                break;
        }
        $pdf->Cell(16, 6, 'IV  ', 1, 0, 'C', 1);
        $pdf->Cell(16, 6, 'DEF  ', 1, 1, 'C', 1);
        $pdf->SetFont('Arial', '', 10);
    }

    public static function footer($pdf, $grado, $sede)
    {

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(198, 5, ' OBSERVACIONES', 1, 1, 'J', 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(198, 10, ' ', 1, 1, 'J');
        $pdf->Ln(8);

        $direccion = DireccionGrado::getByGrado($grado, $sede);
        $pathFirma = public_path() . '/firmas/' . $direccion->docente_id . '.png';
        $nom_ac = $direccion->docente->nombres . ' ' . $direccion->docente->apellidos;
        //$pdf->Image($pathFirma, 8, 135, 40);


        $pdf->SetFont('Arial', 'B', 10);

        $pdf->Cell(190, 4, utf8_decode($nom_ac), 0, 1, 'J');
        $pdf->Cell(40, 6, 'Director de Grupo', 0, 1, 'J');
    }
    public static function disciplina($pdf, $matricula, $periodo)
    {
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(198, 6, ' DISCIPLINA ', 1, 1, 'J', 1);
        $convivencia = Convivencia::reportConvivencia($matricula->id, $periodo);
        if ($convivencia) {
            $logro = LogroDisciplinario::find($convivencia->logro);
        }
        if (!empty($logro)) {
            if (!empty($logro->descripcion)) {
                $pdf->SetFont('Arial', '', 9);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->MultiCell('198', '5', utf8_decode($logro->descripcion), 1, 1, 'J');
            }
        }
    }

    public static function headerConsolidados($sede, $grado, $periodo, $pdf)
    {
        $Objgrado = Grado::find($grado);
        $Objsede = Sede::find($sede);
        $pdf->AddPage('L', 'Legal');
        $path = public_path() . '/logo.png';
        $pdf->SetX(94);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetX(94);
        $pdf->Cell(190, 6, utf8_decode('INSTITUCION EDUCATIVA DON ALONSO'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX(94);
        $pdf->Cell(190, 4, utf8_decode('SABER - ESFUERZO - ESPERANZA'), 0, 1, 'C');
        $pdf->SetX(94);
        $pdf->Cell(190, 4, utf8_decode(' Plantel de Carácter Oficial'), 0, 1, 'C');
        $pdf->SetX(94);
        $pdf->Cell(190, 4, utf8_decode(' Resolución N° 1072 Mayo 31/04 y 1566 Agosto 06/04'), 0, 1, 'C');
        $pdf->SetX(94);
        $pdf->Cell(190, 6, utf8_decode(' Código Icfes 000'), 0, 1, 'C');
        $pdf->SetX(94);
        $pdf->Cell(190, 6, utf8_decode('iedonalonso.com.co'), 0, 1, 'C');
        $pdf->Image($path, 8, 6, 20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(370, 5, 'CONSOLIDADO DE NOTAS ', 0, 1, 'C');
        $pdf->Cell(320, 10, '', 1, 0, 'J');
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 10);

        $pdf->Cell(90, 6, 'GRADO: ' . $Objgrado->descripcion, 0, 0, 'J');
        $pdf->Cell(90, 6, 'SEDE: ' . $Objsede->nombre, 0, 0, 'J');
        $pdf->Cell(80, 6, 'PERIODO: ' . $periodo, 0, 0, 'J');
        $pdf->Cell(50, 6, utf8_decode('AÑO: 2024'), 0, 0, 'J');
        $pdf->Ln(5);
    }

    public static function cabeceraFinal($pdf, $matricula, $periodo)
    {
        $institucion = "INSTITUCION EDUCATIVA DON ALONSO";
        $lema = 'SABER - ESFUERZO - ESPERANZA';
        $path = public_path() . '/logo.png';
        $path2 = public_path() . '/bandera.jpg';
        $codIcfes = '092908 - 128413';
        $jornada = 'MATINAL';
        $url = 'http://iedonalonso.com.co';
        $pdf->AddPage();
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(190, 6, utf8_decode($institucion), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(190, 4, utf8_decode($lema), 0, 1, 'C');
        $pdf->Cell(190, 4, utf8_decode(' Plantel de Carácter Oficial'), 0, 1, 'C');
        $pdf->Cell(190, 4, utf8_decode(' Resolución N° 1072 Mayo 31/04 y 1566 Agosto 06/04'), 0, 1, 'C');
        $pdf->Cell(190, 6, utf8_decode(' Código Icfes ' . $codIcfes), 0, 1, 'C');
        $pdf->Cell(190, 6, utf8_decode($url), 0, 1, 'C');
        $pdf->Image($path, 8, 6, 20);
        $pdf->Image($path2, 180, 8, 20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Ln(2);
        $pdf->Cell(198, 6, utf8_decode(' INFORME FINAL'), 1, 1, 'C', 1);
        $pdf->SetFont('Arial', '', 8);
        $nom = $matricula->apellidos . ' ' . $matricula->nombres;
        $pdf->Cell(113, 4, 'Nombres: ' . $nom, 1, 0, 'J');
        $pdf->Cell(47, 4, 'Grado: ' . $matricula->grado, 1, 0, 'J');
        $pdf->Cell(38, 4, 'Periodo: ' . $periodo, 1, 1, 'J');
        $pdf->Cell(40, 5, 'Sede: ' . $matricula->sede, 1, 0, 'J');
        $pdf->Cell(40, 5, utf8_decode(' N° Doc: ') . $matricula->num_doc, 1, 0, 'J');
        $pdf->Cell(33, 5, utf8_decode(' N° Folio: ') . $matricula->folio, 1, 0, 'J');
        $pdf->Cell(47, 5, utf8_decode(' Jornada: ') . $jornada, 1, 0, 'J');
        $pdf->Cell(38, 5, utf8_decode(' Año: 2024 '), 1, 1, 'J');
        $pdf->Ln();

        $pdf->Ln();
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(8, 6, ' IHS', 1, 0, 'C', 1);
        $pdf->Cell(170, 6, utf8_decode('ARÉAS Y/O ASIGNATURAS '), 1, 0, 'J', 1);
        $pdf->SetX(188);
        $pdf->Cell(20, 6, 'NOTA  ', 1, 1, 'C', 1);
        $pdf->SetFont('Arial', '', 9);
    }

    public static function siguienteGrado($grado)
    {
        $nuevo = "";
        switch ($grado) {
            case '3':
                $nuevo = "2°";
                break;
            case '4':
                $nuevo = "3°";
                break;
            case '5':
                $nuevo = "4°";
                break;
            case '6':
                $nuevo = "5°";
                break;
            case '7':
                $nuevo = "6°";
                break;
            case '8':
                $nuevo = "7°";
                break;
            case '9':
                $nuevo = "8°";
                break;
            case '10':
                $nuevo = "9°";
                break;
            case '11':
                $nuevo = "10";
                break;
            case '12':
                $nuevo = "11°";
                break;

            default:
               $nuevo="";
                break;
        }
        return $nuevo;
    }
}

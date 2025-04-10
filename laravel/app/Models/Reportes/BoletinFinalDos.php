<?php

namespace App\Models\Reportes;

use App\Models\Calificacion;
use App\Models\CargaAcademica;
use App\Models\DireccionGrado;
use App\Models\Matricula;
use App\Models\Prefijo;
use App\Models\Puesto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletinFinalDos extends Model
{
    public static function reporte($sede, $grado, $pdf)
    {
        $matriculas = Matricula::listado($sede, $grado);
        $pdf = app('Fpdf');
        $periodo = 5;
        $prefijos = Prefijo::all()->keyBy('id'); // Cargar todos los prefijos previamente

        foreach ($matriculas as $matricula) {
            self::generateHeader($pdf, $matricula);
            $total = 0;
            $promedioGeneral = 0;
            $ganadas = 0;
            $perdidas = 0;
            $niveladas = 0;
            $matetemicas = 0;
            $ihsMat = 0;

            // Calificación de matemáticas
            $matematicas = self::processMatematicas($sede, $grado, $matricula, $pdf, $ganadas, $perdidas, $matetemicas, $ihsMat, $prefijos);
            $promedioGeneral += $matematicas['promedioGeneral'];
            $total += $matematicas['total'];

            // Calificación de otras áreas
            $otrasAreas = self::processOtrasAreas($sede, $grado, $matricula, $pdf, $promedioGeneral, $total, $ganadas, $perdidas, $prefijos);
            $promedioGeneral += $otrasAreas['promedioGeneral'];
            $total += $otrasAreas['total'];

            // Calcular el promedio final
            $nprom = round($promedioGeneral / $total, 1);
            $desempeno = self::getDesempeno($nprom);

            // Guardar el puesto
            self::savePuesto($matricula, $nprom, $ganadas, $perdidas, $niveladas, $periodo);

            // Mostrar resultado en el PDF
            self::generateSummary($pdf, $sede, $grado, $nprom, $desempeno, $ganadas, $perdidas, $niveladas, $periodo, $matricula);
        }

        // Finalizar el PDF y devolver la cadena base64
        $base64String = chunk_split(base64_encode($pdf->Output('S')));
        return $base64String;
    }

    private static function generateHeader($pdf, $matricula)
    {
        Auxiliar::cabeceraFinal($pdf, $matricula, 'FINAL');
    }

    private static function processMatematicas($sede, $grado, $matricula, $pdf, &$ganadas, &$perdidas, &$matetemicas, &$ihsMat, $prefijos)
    {
        $calMatematicas = Calificacion::reportCalificaciones($matricula->id, 4, 1, 1.3);
        $total = 0;
        $ihsMat = 0;
        foreach ($calMatematicas as $cal) {
            $total = 1;
            $carga = CargaAcademica::getDocente($sede, $grado, $cal->asignatura_id);
            $ihsMat += $carga->ihs;
            $notas = Calificacion::calificacionesFinales($cal->matricula_id, $cal->asignatura_id);
            $por = ($carga->porcentaje / 100);
            $nt = round($notas['promedio'] * $por, 1);
            $matetemicas += $nt;
            self::addAsiganturaCalificacionToPDF($pdf, $carga, $notas['promedio'], $notas['promedio'], true);
        }
        self::addAreaCalificacionToPDF($pdf, $ihsMat, 'MATEMATICAS', $matetemicas, $matetemicas);
        self::addLogroToPDF($matetemicas, $ganadas, $perdidas, $prefijos, $pdf);

        return ['promedioGeneral' => $matetemicas, 'total' => $total];
    }

    private static function processOtrasAreas($sede, $grado, $matricula, $pdf, &$promedioGeneral, &$total, &$ganadas, &$perdidas, $prefijos)
    {
        $calificaciones = Calificacion::reportCalificaciones($matricula->id, 4, 2, '');
        foreach ($calificaciones as $cal) {
            $total++;
            $carga = CargaAcademica::getDocente($sede, $grado, $cal->asignatura_id);
            $notas = Calificacion::calificacionesFinales($cal->matricula_id, $cal->asignatura_id);
            $por = ($carga->porcentaje / 100);
            $nt = round($notas['promedio'] * $por, 1);
            $promedioGeneral += $nt;

            self::addAsiganturaCalificacionToPDF($pdf, $carga, $notas['promedio'], $nt);

            // Determinar si la calificación es ganada o perdida
            $idprefijo = self::getPrefijo($nt, $ganadas, $perdidas, $prefijos);
            $logro = $prefijos[$idprefijo];
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 9);
            $pdf->MultiCell('198', '7', utf8_decode($logro->prefijo), 1, 1, 'J', true);
        }

        return ['promedioGeneral' => $promedioGeneral, 'total' => $total];
    }

    private static function addAsiganturaCalificacionToPDF($pdf, $carga, $promedio, $nt, $filtro = false)
    {
        if ($filtro) {
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 9);
        } else {
            $pdf->SetFillColor(232, 232, 232);
            $pdf->SetFont('Arial', 'B', 9);
        }
        $pdf->Cell(8, 6, $carga->ihs, 1, 0, 'C', 1);
        $pdf->Cell(170, 6, utf8_decode($carga->asignatura->nombre . ' ' . $carga->porcentaje . '%'), 1, 0, 'J', 1);
        $pdf->SetX(188);
        $pdf->Cell(20, 6, $nt, 1, 1, 'C', 1);
    }

    private static function addAreaCalificacionToPDF($pdf, $ihs, $nombre, $promedio, $nt)
    {
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(8, 6, $ihs, 1, 0, 'C', 1);
        $pdf->Cell(170, 6, utf8_decode($nombre . ' '  . '100%'), 1, 0, 'J', 1);
        $pdf->SetX(188);
        $pdf->Cell(20, 6, $nt, 1, 1, 'C', 1);
    }

    private static function addLogroToPDF($nt, &$ganadas, &$perdidas, $prefijos, $pdf)
    {
        // Determinar si la calificación es ganada o perdida
        $idprefijo = self::getPrefijo($nt, $ganadas, $perdidas, $prefijos);
        $logro = $prefijos[$idprefijo];

        // Establecer color y fuente en el PDF
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 9);

        // Escribir el logro en el PDF
        $pdf->MultiCell('198', '7', utf8_decode($logro->prefijo), 1, 1, 'J', true);
    }

    private static function getPrefijo($nt, &$ganadas, &$perdidas, $prefijos)
    {
        if ($nt < 3) {
            $idprefijo = 8;
            $perdidas++;
        } else if ($nt >= 3 && $nt < 4) {
            $idprefijo = 7;
            $ganadas++;
        } else if ($nt >= 4 && $nt < 4.5) {
            $idprefijo = 6;
            $ganadas++;
        } else {
            $idprefijo = 5;
            $ganadas++;
        }
        return $idprefijo;
    }

    private static function getDesempeno($nprom)
    {
        if ($nprom < 3) {
            return "DESEMPEÑO BAJO";
        } else if ($nprom >= 3 && $nprom < 4) {
            return "DESEMPEÑO BASICO";
        } else if ($nprom >= 4 && $nprom < 4.5) {
            return "DESEMPEÑO ALTO";
        } else {
            return "DESEMPEÑO SUPERIOR";
        }
    }

    private static function savePuesto($matricula, $nprom, $ganadas, $perdidas, $niveladas, $periodo)
    {
        Puesto::updateOrCreate(
            ['matricula_id' => $matricula->id, 'periodo_id' => $periodo],
            [
                'matricula_id' => $matricula->id,
                'periodo_id' => $periodo,
                'promedio' => $nprom,
                'ganadas' => $ganadas,
                'perdidas' => $perdidas,
                'areasp' => $perdidas,
                'areasg' => $ganadas,
                'niveladas' => $niveladas
            ]
        );
    }

    private static function generateSummary($pdf, $sede, $grado, $nprom, $desempeno, $ganadas, $perdidas, $niveladas, $periodo, $matricula)
    {
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(138, 6, "PROMEDIO FINAL: " . round($nprom, 1), 1, 0, 'C', 1);
        $pdf->Cell(60, 6, utf8_decode($desempeno), 1, 1, 'C', 1);
        $cont = 0;
        $puestos = Puesto::getPuesto($sede, $grado, $periodo);
        $total = count($puestos);
        foreach ($puestos as $puesto) {
            $cont++;
            if ($puesto->matricula_id == $matricula->id) {
                $posicion = $cont;
                break;
            }
        }
        $pdf->Cell(198, 6, ' PUESTO: ' . $posicion . '  DE ' . $total, 1, 1, 'J', 1);
        $pdf->Cell(198, 6, utf8_decode(' CONCEPTO COMISIÓN EVALUACIÓN Y PROMOCIÓN'), 1, 1, 'J', 1);

        if ($perdidas >= 1) {
            $pdf->Cell(198, 6, utf8_decode(' REPROBO EL AÑO LECTIVO '), 1, 1, 'C');
            $pdf->Cell(198, 5, utf8_decode(' N° ARÉAS PERDIDAS: ' . $perdidas), 1, 1, 'J');
        } else {
            $pdf->SetFillColor(255, 255, 255);
            $nuevoGrado = Auxiliar::siguienteGrado($grado);
            $pdf->Cell(198, 6, utf8_decode(' PROMOVIDO AL  GRADO ' . $nuevoGrado), 1, 1, 'C');
        }
        $pdf->Ln(15);
        $direccion = DireccionGrado::getByGrado($grado, $sede);
        $nom_ac = $direccion->docente->nombres . ' ' . $direccion->docente->apellidos;


        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(190, 4, utf8_decode($nom_ac), 0, 1, 'J');
        $pdf->Cell(40, 6, 'Director de Grupo', 0, 1, 'J');
    }
}

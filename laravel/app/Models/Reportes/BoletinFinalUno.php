<?php

namespace App\Models\Reportes;

use App\Models\Calificacion;
use App\Models\CargaAcademica;
use App\Models\DireccionGrado;
use App\Models\Matricula;
use App\Models\Nivelacion;
use App\Models\Prefijo;
use App\Models\Puesto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletinFinalUno extends Model
{


    public static function reporte($sede, $grado, $pdf)
    {

        $matriculas = Matricula::listado($sede, $grado);
        $num1 = count($matriculas);
        $i    = 1;
        $pdf = app('Fpdf');
        $periodo = 5;
        $des="";
        while ($i <= $num1) {
            foreach ($matriculas as $matricula) {
                Auxiliar::cabeceraFinal($pdf, $matricula, 'FINAL');
                $ganadas = 0;
                $perdidas = 0;
                $niveladas = 0;
                $promedioGeneral = 0;
                $total = 0;
                $calificaciones = Calificacion::reportCalificaciones($matricula->id, 4, 1, '');
                foreach ($calificaciones as $cal) {
                    $total++;
                    $carga = CargaAcademica::getDocente($sede, $grado, $cal->asignatura_id);
                    $notas = Calificacion::calificacionesFinales($cal->matricula_id, $cal->asignatura_id);
                    $por = ($carga->porcentaje / 100);
                    $nt = round($notas['promedio'] * $por, 1);
                    $promedioGeneral += $nt;
                    $pdf->SetFillColor(232, 232, 232);
                    $pdf->SetFont('Arial', '', 9);
                    $pdf->Cell(8, 6, $carga->ihs, 1, 0, 'C', 1);
                    $pdf->Cell(170, 6, utf8_decode($carga->asignatura->nombre . ' ' . $carga->porcentaje . '%'), 1, 0, 'J', 1);
                    $pdf->SetX(188);
                    $pdf->Cell(20, 6, $nt, 1, 1, 'C', 1);
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
                    $logro = Prefijo::find($idprefijo);
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->MultiCell('198', '7', utf8_decode($logro->prefijo), 1, 1, 'J', true);

                }
                Auxiliar::disciplina($pdf, $matricula, $periodo);
                    $nprom = round(($promedioGeneral / $total), 1);

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
                    if($nprom<3){
                        $des="DESEMPEÑO BAJO";
                    } else if ($nprom >= 3 && $nprom < 4) {
                        $des="DESEMPEÑO BASICO";
                    } else if ($nprom >= 4 && $nprom < 4.5) {
                        $des="DESEMPEÑO ALTO";
                    } else {
                        $des="DESEMPEÑO SUPERIOR";
                    }
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(198, 6, ' PROMEDIO GENERAL: ' . $nprom . '   ' . utf8_decode($des), 1, 1, 'J', 1);
                    $pdf->Cell(198, 6, ' PUESTO: ' . $posicion . '  DE ' . $total, 1, 1, 'J', 1);
                    $pdf->Cell(198, 6, utf8_decode(' CONCEPTO COMISIÓN EVALUACIÓN Y PROMOCIÓN'), 1, 1, 'J', 1);
                    if ($perdidas >= 1) {
                      $pdf->Cell(198, 6, utf8_decode(' REPROBO EL AÑO LECTIVO '), 1, 1, 'C');
                      $pdf->Cell(198, 5, utf8_decode(' N° ARÉAS PERDIDAS: ' . $perdidas), 1, 1, 'J');
                    }else{
                        $nuevoGrado=Auxiliar::siguienteGrado($grado);
                        $pdf->Cell(198, 6, utf8_decode(' PROMOVIDO AL  GRADO '.$nuevoGrado), 1, 1, 'C');
                    }
                    $pdf->Ln(15);
                    $direccion=DireccionGrado::getByGrado($grado, $sede);
                    $nom_ac=$direccion->docente->nombres.' '.$direccion->docente->apellidos;
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(190, 4,utf8_decode($nom_ac) , 0, 1, 'J');
                    $pdf->Cell(40, 6, 'Director de Grupo', 0, 1, 'J');


            }
            $base64String = chunk_split(base64_encode($pdf->Output('S')));
                return $base64String;
                exit;
                $i++;
        }
    }
}

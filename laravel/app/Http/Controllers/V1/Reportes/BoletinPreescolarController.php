<?php
namespace App\Http\Controllers\V1\Reportes;

use App\Http\Controllers\Controller;
use App\Models\CargaAcademica;
use App\Models\DireccionGrado;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\LogroPreescolar;
use App\Models\Matricula;
use App\Models\ObservacionFinal;
use App\Models\Periodo;
use App\Models\Preescolar;
use App\Models\Sede;

use Illuminate\Http\Request;


class BoletinPreescolarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sede='';
        $sedes=[];
        $periodos=Periodo::active();
        $docente=auth()->user()->usable_id;
        if(auth()->user()->tipo==1){
            $doc=Docente::find($docente);
            $sede=$doc->sede_id;
            $grados=CargaAcademica::gradosDocente($docente, $sede);
        }else{
            $sedes=Sede::active();
            $grados=Grado::preescolarActive();
        }
        return view('reportes.boletines-preescolar', compact('sede','grados', 'sedes', 'periodos'));
    }

    public function boletines(Request $request)
    {
        $validated = $request->validate(['sede' => 'required', 'grado' => 'required', 'periodo' => 'required', ]);
        $path=public_path().'/logo.png';
        $path2=public_path().'/bandera.jpg';
        $grado = $request->grado;
        $sede = $request->sede;
        $periodo = $request->periodo;
        $pdf = app('Fpdf');
        $institucion = "INSTITUCION EDUCATIVA DON ALONSO";
        $codIcfes = '092908 - 128413';
        $jornada = 'MATINAL';

        $matriculas = Matricula::listado($sede, $grado);
        $num1 = count($matriculas);
        $i = 1;
        $pdf = app('Fpdf');
            foreach($matriculas as $matricula)
            {
                $pdf->AddPage();;
                $pdf->SetFillColor(232, 232, 232);
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->Cell(190, 6, utf8_decode($institucion) , 0, 1, 'C');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(190, 4, utf8_decode(' Plantel de Carácter Oficial') , 0, 1, 'C');
                $pdf->Cell(190, 4, utf8_decode(' Resolución N° 1072 Mayo 31/04 y 1566 Agosto 06/04') , 0, 1, 'C');
                $pdf->Cell(190, 6, utf8_decode(' Código Icfes ' . $codIcfes) , 0, 1, 'C');
                $pdf->Cell(190, 6, utf8_decode('https://iedonalonso.co') , 0, 1, 'C');
                $pdf->Image($path, 8, 6, 20);
                $pdf->Image($path2, 180, 8, 20);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(190, 6, utf8_decode(' INFORME DE DESEMPEÑOS') , 1, 1, 'C', 1);
                $pdf->SetFont('Arial', '', 10);
                $nom = $matricula->apellidos . ' ' . $matricula->nombres;
                $pdf->Cell(113, 6, 'Nombres: ' . utf8_decode($nom) , 1, 0, 'J');
                $pdf->Cell(47, 6, 'Grado: ' . utf8_decode($matricula->grado) , 1, 0, 'J');
                $pdf->Cell(30, 6, 'Periodo: ' . $periodo, 1, 1, 'J');
                $pdf->Cell(40, 6, 'Sede: ' . $matricula->sede, 1, 0, 'J');
                $pdf->Cell(40, 6, utf8_decode(' N° Doc: ') . $matricula->num_doc, 1, 0, 'J');
                $pdf->Cell(33, 6, utf8_decode(' N° Folio: ') . $matricula->folio, 1, 0, 'J');
                $pdf->Cell(47, 6, utf8_decode(' Jornada: MATINAL') , 1, 0, 'J');
                $pdf->Cell(30, 6, utf8_decode(' Año: 2024 ') , 1, 1, 'J');
                $pdf->Ln();
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(8, 8, 'IHS ', 1, 0, 'C', 1);
                $pdf->SetFillColor(232, 232, 232);
                $pdf->Cell(183, 8, 'DIMENSIONES ', 1, 1, 'C', 1);
                $pdf->Ln(2);
               $calificacionesP=Preescolar::where('matricula_id', $matricula->id)
               ->where('periodo_id', $periodo)->get();
               foreach($calificacionesP as $item)
                {
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->Cell(8, 8, $item->carga->ihs, 1, 0, 'C', 0);
                    $pdf->SetFont('Arial', 'B', 8.5);
                    $pdf->SetFillColor(232, 232, 232);
                    $pdf->Cell(183, 8, $item->asignatura->nombre, 1, 1, 'J', 1);
                    $pdf->Ln(0.5);
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetFillColor(255, 255, 255);
                    $logro1=LogroPreescolar::find($item->logro_a);
                    $logro2=LogroPreescolar::find($item->logro_b);
                    $logro3=LogroPreescolar::find($item->logro_c);
                    $logro4=LogroPreescolar::find($item->logro_d);
                    if(!empty($logro1)){
                        $pdf->MultiCell(191, 7, '* ' . utf8_decode($logro1->descripcion) , 0, 1, 'J', 1);
                    }
                    if(!empty($logro2)){
                        $pdf->MultiCell(191, 7, '* ' . utf8_decode($logro2->descripcion) , 0, 1, 'J', 1);
                    }
                    if(!empty($logro3)){
                        $pdf->MultiCell(191, 7, '* ' . utf8_decode($logro3->descripcion) , 0, 1, 'J', 1);
                    }

                    if(!empty($logro4)){
                    $pdf->MultiCell(191, 7, '* ' . utf8_decode($logro4->descripcion) , 0, 1, 'J', 1);
                    }

                }
                $pdf->SetFillColor(232, 232, 232);
                $pdf->SetFont('Arial', 'B', 8);

                $pdf->SetFillColor(232, 232, 232);
                if($periodo<4){
                     $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(191, 5, ' OBSERVACIONES', 1, 1, 'J', 1);
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->Cell(191, 7, ' ', 1, 1, 'J', 1);
                    $pdf->Ln();
                    $pdf->Cell(80, 0, ' ', 1, 1, 'J');
                    $pdf->Ln(0.5);
                }else{
                    $pdf->Cell(191, 6, ' OBSERVACIONES FINALES', 1, 1, 'J', 1);
                    $pdf->SetFont('Arial', '', 9);
                    $observacion="";
                    //$observacion=ObservacionFinal::ObservacionByMatricula($matricula->id);
                  if(!empty($observacion)){
                       $pdf->SetFillColor(255, 255, 255);
                        //$pdf->MultiCell(191, 7, utf8_decode($observacion->descripcion) , 0, 1, 'J', 1);
                  }
                    $pdf->Ln();

                }

                $direccion=DireccionGrado::getByGrado($matricula->grado_id, $matricula->sede_id);
                $nom_ac = $direccion->docente->nombres.' '.$direccion->docente->apellidos;
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(190, 4, utf8_decode($nom_ac) , 0, 1, 'J');
                $pdf->Cell(40, 4, ' Directora de Grupo', 0, 1, 'J');
                // Footer
                //$pdf->SetY(262);
               // $pdf->SetFont('Arial','I',8);
                //$pdf->Cell(0,10,utf8_decode('Pagína N°: ').$pdf->PageNo(),0,0,'R');
                //$pdf->Ln(3);
                //$pdf->Cell(0,10,'Impreso por SISCA INEDA 2022',0,0,'R');

            }


        $pdf->Output();
        exit;

    }

}


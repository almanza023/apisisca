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
use App\Models\TipoAsignatura;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class BoletinPreescolarController extends Controller
{
   
    protected $user;   

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');       
        if($token != '')
            //En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos en una variable, nosotros no lo utilizaremos.
            $this->user = JWTAuth::parseToken()->authenticate();
    }

    
   
    public function boletines(Request $request)
    {
        $base64String ="";
        $validated = $request->validate(['sede_id' => 'required', 'grado_id' => 'required', 'periodo_id' => 'required', ]);
        $path=public_path().'/logo.png';
        $path2=public_path().'/bandera.jpg';
        $grado = $request->grado_id;
        $sede = $request->sede_id;
        $periodo = $request->periodo_id;
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
                $pdf->Cell(190, 6, utf8_decode('https://iedonalonso.com.co') , 0, 1, 'C');
                $pdf->Image($path, 8, 6, 20);
                $pdf->Image($path2, 180, 8, 20);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(190, 6, utf8_decode('  INFORME DE DESARROLLOS Y APRENDIZAJES ') , 1, 1, 'C', 1);
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
                
                $tipoAsignatura=TipoAsignatura::where('preescolar', '1')->get();
                 $con=0;     
                foreach ($tipoAsignatura as $tipo) {
                    $pdf->SetFont('Arial', '', 8);
                    $con++;
                    if($con==1){
                        $pdf->SetFillColor(222, 234, 246);
                        $pdf->MultiCell(190, 7, utf8_decode($tipo->descripcion) , 0, 1, 'J', 1);
                    }
                    if($con==2){
                        $pdf->SetFillColor(226, 239, 217);
                        $pdf->MultiCell(190, 7, utf8_decode($tipo->descripcion) , 0, 1, 'J', 1);
                    }
                    if($con==3){
                        $pdf->SetFillColor(255, 255, 204);
                        $pdf->MultiCell(190, 7, utf8_decode($tipo->descripcion) , 0, 1, 'J', 1);
                    }
                    $pdf->SetFillColor(232, 232, 232);
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->Cell(65, 8, utf8_decode($tipo->nombre), 1, 0, 'J', 1);                    
                    $pdf->Cell(125, 8, 'VALORACION ', 1, 1, 'J', 1);                  
                    $data=Preescolar::getDatosByMatriculaPeriodo($matricula->id, $periodo, $tipo->id);
                    if(!empty($data)){
                       foreach ($data as $cal) {
                        $pdf->SetFont('Arial', '', 9);
                        $pdf->Cell(65, 8, $cal->nombre, 1, 0, 'J', 0);                           
                        $logro1=LogroPreescolar::find($cal->logro_a);                      
                        if(!empty($logro1)){
                            $pdf->SetFillColor(255, 255, 255);
                            $pdf->MultiCell(125, 8, utf8_decode($logro1->descripcion) , 0, 1, 'J', 0);
                        }
                       }
                       $pdf->Ln(0.7);
                    }
                    
                   }
                    

                   $pdf->Ln();
                $pdf->SetFillColor(232, 232, 232);
                $pdf->SetFont('Arial', 'B', 8);

                $pdf->SetFillColor(232, 232, 232);
                if($periodo<4){
                     $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(191, 5, ' OBSERVACIONES', 1, 1, 'J', 1);
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->Cell(191, 10, ' ', 1, 1, 'J', 1);
                    $pdf->Ln();
                    $pdf->Cell(80, 0, ' ', 1, 1, 'J');
                    $pdf->Ln(0.5);
                }else{
                    $pdf->Cell(191, 6, ' OBSERVACIONES FINALES', 1, 1, 'J', 1);
                    $pdf->SetFont('Arial', '', 9);
                    $observacion="";
                    $observacion=ObservacionFinal::ObservacionByMatricula($matricula->id);
                  if(!empty($observacion)){
                       $pdf->SetFillColor(255, 255, 255);
                        $pdf->MultiCell(191, 7, utf8_decode($observacion->descripcion) , 0, 1, 'J', 1);
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


        //$pdf->Output();
        $base64String = chunk_split(base64_encode($pdf->Output('S')));
        return response()->json([
            'code'=>200,
            'pdf' => $base64String
        ], Response::HTTP_OK);
        exit;

    }

}


<?php

namespace App\Http\Controllers\V1\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\CargaAcademica;
use App\Models\DireccionGrado;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Nivelacion;
use App\Models\Periodo;
use App\Models\Preescolar;
use App\Models\Puesto;
use App\Models\Reportes\ConsolidadoCuatro;
use App\Models\Reportes\ConsolidadoDos;
use App\Models\Reportes\ConsolidadoUno;
use App\Models\Reportes\ConsolidadoTres;
use App\Models\Reportes\EstadisticaPeriodo;
use App\Models\Reportes\ReporteArea;
use App\Models\Reportes\ReporteDimensiones;
use App\Models\Reportes\ReporteNotas;
use App\Models\Reportes\ReporteSubidaDocente;
use App\Models\Sede;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReporteController extends Controller
{
    protected $user;   
    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');       
        if($token != '')
            //En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos en una variable, nosotros no lo utilizaremos.
            $this->user = JWTAuth::parseToken()->authenticate();
    }


    

    

    
    public function ReportEstadisticas(Request $request)
    {
        $validated = $request->validate([
            'sede_id' => 'required',
            'grado_id' => 'required',
            'periodo_id' => 'required',
        ]);

        $grado=$request->grado_id;
        $sede=$request->sede_id;
        $periodo=$request->periodo_id;
        $pdf = app('Fpdf');
        $data=Puesto::getPuestos($grado, $periodo);
        if(count($data)>0){
           
            $base64String= EstadisticaPeriodo::reporte($pdf, $data, $periodo);
            return response()->json([
                'code'=>200,
                'pdf' => $base64String
            ], Response::HTTP_OK);
        }else{
            return response()->json([
                'code'=>400,
                'message' => 'Debe Generar Boletines para Obtener las Estadisticas'
            ], Response::HTTP_OK);
        }


    }

    public function ReporteDimensiones(Request $request)
    {
        $data=[];
        $cabecera=[];
        $validated = $request->validate([
            'sede' => 'required',
            'asignatura' => 'required',
            'grado' => 'required',
            'periodo' => 'required',
        ]);
        $partes = explode("-", $request->asignatura);
        $grado=Grado::find($request->grado);
        $sede=Sede::find($request->sede);
        $asignatura=$partes[1];
        $director=DireccionGrado::getByGrado($request->grado, $request->sede);

        $cabecera=[
            'sede'=>$sede->nombre,
            'grado'=>$grado->descripcion,
            'asignatura'=>$asignatura,
            'docente'=>$director->docente->nombres.' '.$director->docente->apellidos
        ];
        $data=Preescolar::calificacionesPeriodo($request->sede, $request->grado, $request->asignatura, $request->periodo);
        $pdf = app('Fpdf');
        ReporteDimensiones::reporte($pdf, $cabecera, $data, $request->periodo);
    }

    public function reporteConsolidados(Request $request){
        $validated = $request->validate([
            'sede' => 'required',
            'grado' => 'required',
            'periodo' => 'required',

        ]);

        $pdf = app('Fpdf');
        $pdf->SetFillColor(232, 232, 232);
        if($request->tipoReporte=="1"){
            $matriculas=Matricula::listado($request->sede, $request->grado);
            if($request->grado>=3 &&$request->grado<=4){
                ConsolidadoUno::reporte($request->sede, $request->grado, $request->periodo, $pdf, $matriculas);
            } else if($request->grado>=5 &&$request->grado<=7){
                ConsolidadoDos::reporte($request->sede, $request->grado, $request->periodo, $pdf, $matriculas);

            }else if($request->grado>=8 &&$request->grado<=11){
                ConsolidadoTres::reporte($request->sede, $request->grado, $request->periodo, $pdf, $matriculas);
            }
            else {
                ConsolidadoCuatro::reporte($request->sede, $request->grado, $request->periodo, $pdf, $matriculas);
            }
        }else{
            $sede=Sede::find($request->sede);
            $grado=Grado::find($request->grado);
            $cabecera=[
                'sede'=>$sede->nombre,
                'grado'=>$grado->descripcion,
            ];
            $carga=CargaAcademica::asignaturasGrado($request->grado, $request->sede);
            $data=[];
            foreach ($carga as $item) {
               $docente=$item->docente->nombres.' '.$item->docente->apellidos;
               $nombreAsignatura=$item->asignatura->nombre;
               $asignatura=$item->asignatura_id;
               $registros=DB::select('SELECT COUNT(c.nota) as total FROM calificaciones c
               INNER JOIN matriculas m ON (c.matricula_id=m.id)
               INNER JOIN carga_academicas ca ON (ca.asignatura_id=c.asignatura_id AND ca.grado_id=?)
               WHERE m.grado_id=? and c.asignatura_id=? and ca.docente_id=? and m.sede_id=?
               and c.periodo_id=?
                ORDER BY c.id ASC ',
                [$request->grado, $request->grado, $asignatura, $item->docente_id, $item->sede_id, $request->periodo]);
              $array=[
                'docente'=>$docente,
                'asignatura'=>$nombreAsignatura,
                'total'=>$registros[0]->total
               ];
               array_push($data, $array);
            }
            ReporteSubidaDocente::reporte($pdf, $cabecera, $data, $request->periodo );
        }


    }

    public function ReporteNotas(Request $request)
    {
        $base64String="";
        $validated = $request->validate([
            'sede_id' => 'required',
            'grado_id' => 'required',
            'asignatura_id' => 'required',
            'periodo_id' => 'required',
        ]);
        $sede_id=$request->sede_id;
        $grado_id=$request->grado_id;
        $asignatura_id=$request->asignatura_id;
        $periodo=$request->periodo_id;

        $data=[];
        $cabecera=[];
        $grado=Grado::find($grado_id);
        $sede=Sede::find($sede_id);
        $asiganatura=Asignatura::find($asignatura_id);      
        $docente=CargaAcademica::getDocente($sede_id, $grado_id, $asignatura_id);
        $data=Calificacion::calificacionesPeriodo($sede_id, $grado_id, $asignatura_id, $periodo);
      
        $cabecera=[
            'sede'=>$sede->nombre,
            'grado'=>$grado->descripcion,
            'asignatura'=>$asiganatura->nombre,
            'periodo'=>$periodo,
            'docente'=>$docente->docente->nombres.' '.$docente->docente->apellidos,
        ];
        $pdf = app('Fpdf');
        $base64String=ReporteNotas::reporte($pdf, $cabecera, $data, $periodo);
        return response()->json([
            'code'=>200,
            'pdf' => $base64String
        ], Response::HTTP_OK);

        
    }

    public function ReporteMatriculas(Request $request)
    {
        $base64String="";
        $validated = $request->validate([
            'sede_id' => 'required',
            'grado_id' => 'required',          
        ]);
        $sede_id=$request->sede_id;
        $grado_id=$request->grado_id;
       

        $data=[];
        $cabecera=[];
        $grado=Grado::find($grado_id);
        $sede=Sede::find($sede_id);      
        $data=Matricula::listado($sede_id, $grado_id);
      
        $cabecera=[
            'sede'=>$sede->nombre,
            'grado'=>$grado->descripcion,
        ];
        $pdf = app('Fpdf');
        $base64String=ReporteNotas::reporteMatricula($pdf, $cabecera, $data);
        return response()->json([
            'code'=>200,
            'pdf' => $base64String
        ], Response::HTTP_OK);

        
    }

    public function ReportAreas(Request $request)
    {
        $validated = $request->validate([
            'sede_id' => 'required',
            'grado_id' => 'required',
            'periodo_id' => 'required',
            'asignatura_id' => 'required',
        ]);

        $grado=$request->grado_id;
        $sede=$request->sede_id;
        $periodo=$request->periodo_id;       
        $pdf = app('Fpdf');
        $data=Calificacion::getAreaMat($grado, $periodo);
        if(count($data)>0){
           
            $base64String= ReporteArea::reporte($pdf, $data, $periodo);
            return response()->json([
                'code'=>200,
                'pdf' => $base64String
            ], Response::HTTP_OK);
        }else{
            return response()->json([
                'code'=>400,
                'message' => 'No se encontrarón calificaciones'
            ], Response::HTTP_OK);
        }


    }



}

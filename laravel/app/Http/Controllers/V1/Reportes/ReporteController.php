<?php

namespace App\Http\Controllers\V1\Reportes;

use App\Exports\CalificacionesExport;
use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\CargaAcademica;
use App\Models\DireccionGrado;
use App\Models\Grado;
use App\Models\LogroPreescolar;
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
use Maatwebsite\Excel\Facades\Excel;
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
        $area="";
        if($request->asignatura_id=='MAT' ||
        $request->asignatura_id=='CAS'||
        $request->asignatura_id=='CNA' ||
        $request->asignatura_id=='CSOC'){
            switch ($request->asignatura_id) {
                case 'MAT':
                    $area=1;
                    break;
                    case 'CAS':
                        $area=2;
                        break;
                        case 'CNA':
                            $area=3;
                            break;
                            case 'CSOC':
                                $area=4;
                                break;

                default:
                    # code...
                    break;
            }
        }else{
            $tipo=CargaAcademica::getDocente($sede, $grado, $request->asignatura_id);
            if(empty($tipo)){
                return response()->json([
                    'code'=>400,
                    'message' => 'No se existen asignaturas compartidas'
                ], Response::HTTP_OK);
            }
            switch ($tipo->area) {
                case 'MAT':
                    $area=1;
                    break;
                    case 'CAS':
                        $area=2;
                        break;
                        case 'CNA':
                            $area=3;
                            break;
                            case 'CSOC':
                                $area=4;
                                break;

                default:
                    # code...
                    break;
            }
        }


        $pdf = app('Fpdf');
        $data=[];
        //MAT
        if($area==1){
            $data=Calificacion::getAreaMat($sede, $grado, $periodo );
        }
        //CAS
        if($area==2){
            $data=Calificacion::getAreaLen($sede, $grado, $periodo );
        }
        //MAT
        if($area==3){
            $data=Calificacion::getAreaNat($sede, $grado, $periodo );
        }
        //CAS
        if($area==4){
            $data=Calificacion::getAreasSoc($sede, $grado, $periodo );
        }

        if(count($data)>0){

            $base64String= "";
            //MAT
            if($area==1){
                $base64String= ReporteArea::reporte($pdf, $data, $periodo);
            }
            //CAS
            if($area==2){
                $base64String= ReporteArea::reporteLenguaje($pdf, $data, $periodo);
            }
            //CNAT
            if($area==3){
                $base64String= ReporteArea::reporteNaturales($pdf, $data, $periodo, $grado);
            }
            //CSOC
            if($area==4){
                $base64String= ReporteArea::reporteSociales($pdf, $data, $periodo, $grado);
            }
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

    public function ReporteValoraciones(Request $request)
    {
        $base64String="";
        $validated = $request->validate([
            'sede_id' => 'required',
            'grado_id' => 'required',
            'asignatura_id' => 'required',
        ]);
        $sede_id=$request->sede_id;
        $grado_id=$request->grado_id;
        $asignatura_id=$request->asignatura_id;


        $data=[];
        $cabecera=[];
        $grado=Grado::find($grado_id);
        $sede=Sede::find($sede_id);
        $asiganatura=Asignatura::find($asignatura_id);
        $docente=CargaAcademica::getDocente($sede_id, $grado_id, $asignatura_id);
        $data=LogroPreescolar::getFiltro($sede_id, $grado_id, $asignatura_id);

        $cabecera=[
            'sede'=>$sede->nombre,
            'grado'=>$grado->descripcion,
            'asignatura'=>$asiganatura->nombre,
            'docente'=>$docente->docente->nombres.' '.$docente->docente->apellidos,
        ];
        $pdf = app('Fpdf');
        $base64String=ReporteNotas::reporteValoraciones($pdf, $cabecera, $data);
        return response()->json([
            'code'=>200,
            'pdf' => $base64String
        ], Response::HTTP_OK);


    }

    public function ReporteNivelaciones(Request $request)
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
        $data=Nivelacion::nivelacionesPeriodo($sede_id, $grado_id, $asignatura_id, $periodo);

        $cabecera=[
            'sede'=>$sede->nombre,
            'grado'=>$grado->descripcion,
            'asignatura'=>$asiganatura->nombre,
            'periodo'=>$periodo,
            'docente'=>$docente->docente->nombres.' '.$docente->docente->apellidos,
        ];
        $pdf = app('Fpdf');
        $base64String=ReporteNotas::reporteNivelacion($pdf, $cabecera, $data, $periodo);
        return response()->json([
            'code'=>200,
            'pdf' => $base64String
        ], Response::HTTP_OK);


    }

    public function ReporteNotasAcumuladas(Request $request)
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


        $matriculas=Matricula::estudiantesCalificacion($sede_id, $grado_id);
         $data=[];

        foreach ($matriculas as $mat) {
            $promg=0;
            $cal1=Calificacion::notaAnteriorEst($mat->id, $asignatura_id, 1);
            if($cal1==''){
                $cal1=0;
            }
            $cal2=Calificacion::notaAnteriorEst($mat->id, $asignatura_id, 2);
            if($cal2==''){
                $cal2=0;
            }
            $cal3=Calificacion::notaAnteriorEst($mat->id, $asignatura_id, 3);
            if($cal3==''){
                $cal3=0;
            }
            $promg=($cal1+$cal2+$cal3)/3;
            $rpromg=round($promg, 2);
            $temp=[
                'nombre'=>utf8_decode($mat->apellidos.' '.$mat->nombres),
                'notap1'=>$cal1,
                'notap2'=>$cal2,
                'notap3'=>$cal3,
                'promedio'=>$rpromg
            ];
            array_push($data, $temp);
        }


        $cabecera=[
            'sede'=>$sede->nombre,
            'grado'=>$grado->descripcion,
            'asignatura'=>$asiganatura->nombre,
            'periodo'=>$periodo,
            'docente'=>$docente->docente->nombres.' '.$docente->docente->apellidos,
        ];
        $pdf = app('Fpdf');
        $base64String=ReporteNotas::reporteAcumulativos($pdf, $cabecera, $data, $periodo);
        return response()->json([
            'code'=>200,
            'pdf' => $base64String
        ], Response::HTTP_OK);


    }

    public function exportarConsolidado(Request $request)
    {
        // Parámetros para el procedimiento
        $gradoId = $request->grado_id; // Cambia estos valores según tus necesidades
        $sedeId = $request->sede_id;
        $asignaturas=CargaAcademica::where('sede_id', $sedeId)
        ->where('grado_id', $gradoId)->orderBy('asignatura_id', 'asc')->get();
        $asignaturaId="";
        $totalAsignaturas=count($asignaturas);
        if($totalAsignaturas>0){
            foreach ($asignaturas as $item) {
               $asignaturaId.=$item->asignatura_id.',';
            }
            $asignaturaId = "'".rtrim($asignaturaId, ',')."'";
        }

        $estado = 1;
        // Llamada al procedimiento almacenado
        $sql="CALL obtener_consolidado(".$asignaturaId.",".$gradoId.",".$sedeId.",".$estado.")";
        try {

            $query = DB::select(DB::raw($sql));
            $resultados= DB::select($query[0]->sql_query);

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        // Aquí puedes proceder a exportar a Excel
        return $this->exportarAExcel($resultados, $totalAsignaturas);
    }

    public function exportarAExcel($resultados, $totalAsignaturas)
    {
        $excelFile = Excel::raw(new CalificacionesExport($resultados, $totalAsignaturas), \Maatwebsite\Excel\Excel::XLSX);

        // Codifica el archivo en Base64
        $base64File = base64_encode($excelFile);
        return response()->json([
            'code'=>200,
            'pdf' => $base64File
        ], Response::HTTP_OK);


    }


}

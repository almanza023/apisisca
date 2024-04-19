<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\AperturaPeriodo;
use App\Models\Calificacion;
use App\Models\CargaAcademica;
use App\Models\DireccionGrado;
use App\Models\Docente;
use App\Models\Matricula;
use App\Models\Sede;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class EstadisticaController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model=Sede::class;
        if($token != '')
            //En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos en una variable, nosotros no lo utilizaremos.
            $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function getContadores()
    {
        //Listamos todos las sedes
       $totalSede=Sede::count();
       $totaldocentes=Docente::count();
       $totalmatriculas=Matricula::count();
       $totalAsignacion=CargaAcademica::where('grado_id','>=','3')->count();
       $aperturaPerido=AperturaPeriodo::getActivado();
       $porcentaje=0;
       $calificadas=0;
       if(!empty($aperturaPerido)){
        $periodo_id=$aperturaPerido->periodo_id;      
        $totalCal=Calificacion::getTotalCalificadas($periodo_id);
        $calificadas=count($totalCal);
        $porcentaje=round(($calificadas/$totalAsignacion)*100,2);
       }
    
      

       $contadores=[
        'sedes'=>$totalSede,
        'docentes'=>$totaldocentes,
        'matriculas'=>$totalmatriculas,
        'asignadas'=>$totalAsignacion,
        'calificadas'=>$calificadas,
        'porcentaje'=>$porcentaje,
       ];
       if($contadores){
        return response()->json([
            'code'=>200,
            'data' => $contadores
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_OK);
       }
    }

    public function getContadoresDocente($id)
    {
        //Listamos todos las sedes
       $sede=Docente::find($id)->sede->nombre;
       $totalAsignacion=CargaAcademica::where('docente_id', $id)->count();
      
       $grados="";
       $direccionGrupo=DireccionGrado::where('docente_id', $id)->get();
        if(count(($direccionGrupo))){
            foreach ($direccionGrupo as $item) {
                $grados.=$item->grado->descripcion."-";
        }
        }
        $aperturaPerido=AperturaPeriodo::getActivado();
        $periodo="";
        $calificadas=0;
        $avance=0;
       if(!empty($aperturaPerido)){
        $periodo=$aperturaPerido->fecha_apertura." - ".$aperturaPerido->fecha_cierre;
        $objCal=CargaAcademica::getTotalAsigaCalDoc($id, $aperturaPerido->periodo_id);
        $calificadas=count($objCal);
       }
      if($totalAsignacion>0){
        $avance=round(($calificadas/$totalAsignacion)*100,2);
      }

       
        

       $contadores=[
        'sede'=>$sede,
        'direcciongrado'=>$grados,
        'asignaciones'=>$totalAsignacion,
        'periodo'=>$periodo,
        'calificadas'=>$calificadas,
        'avance'=>$avance,
       ];
       if($contadores){
        return response()->json([
            'code'=>200,
            'data' => $contadores
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_OK);
       }
    }

    public function getTotalEstudianteByGrado()
    {
        //Listamos todos las sedes
       $objeto=$this->model::getTotalMatriculaGrado();
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_OK);
       }
    }

    public function getTotalEstudianteBySede()
    {
        //Listamos todos las sedes
       $objeto=$this->model::getTotalMatriculaSede();
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_OK);
       }
    }




}

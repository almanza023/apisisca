<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
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

       $contadores=[
        'sedes'=>$totalSede,
        'docentes'=>$totaldocentes,
        'matriculas'=>$totalmatriculas,
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
        

       $contadores=[
        'sede'=>$sede,
        'direcciongrado'=>$grados,
        'asignaciones'=>$totalAsignacion,
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

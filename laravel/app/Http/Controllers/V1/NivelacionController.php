<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\LogroAcademico;
use App\Models\Nivelacion;
use App\Models\PromedioAsignatura;
use App\Models\Repositorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class NivelacionController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model=Nivelacion::class;
        if($token != '')
            //En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos en una variable, nosotros no lo utilizaremos.
            $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Listamos todos los productos
       $objeto=$this->model::getAll();
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>400,
            'data' => []
        ], Response::HTTP_OK);
       }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validamos los datos
        $data = $request->only('matriculas', 'asignatura_id', 'periodo_id', 'notas');
        $validator = Validator::make($data, [
            'matriculas' => 'required',
            'asignatura_id' => 'required',
            'periodo_id' => 'required',
            'notas' => 'required',
            ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        DB::beginTransaction();
        try {
            $data=[];

            $data=$this->model::nivelacionesPeriodo($request->sede_id, $request->grado_id, $request->asignatura_id, $request->periodo_id);
            if(count($data)>0){
                return response()->json([
                    'code'=>400,
                    'message' => 'Ya existen Nivelaciones para la Sede, Grado y Asignatura Seleccionada',
                    'data' => $data
                ], Response::HTTP_OK);
            }
            $matriculas=$request->input('matriculas');
            $notas=$request->input('notas');
            //$notasanteriores=$request->input('notasanterior');
            $notasperiodo=$request->input('notasperiodo');
            //$promedios=$request->input('promedio');


            for ($i=0; $i < count($matriculas) ; $i++) {


                $matricula_id=$matriculas[$i];
                $nota=$notas[$i];
                //$notaanterior=$notasanteriores[$i];
                $notaperiodo=$notasperiodo[$i];
                //$promedio=$promedios[$i];

                //$calificaciones=[];
                // if($request->periodo_id==1){
                //     $calificaciones=Calificacion::where('matricula_id', $matricula_id)
                //     ->where('asignatura_id', $request->asignatura_id)
                //     ->where('periodo_id', '<=',2)->get();
                // }else{
                //     $calificaciones=Calificacion::where('matricula_id', $matricula_id)
                //     ->where('asignatura_id', $request->asignatura_id)
                //     ->where('periodo_id', '>=',3)->get();
                // }

                // foreach ($calificaciones as $calificacion) {
                //     $calificacion->nota=$nota;
                //     $calificacion->save();
                // }

                $nivelacion=Nivelacion::updateOrCreate(
                    ['matricula_id'=>$matricula_id,
                    'asignatura_id'=>$request->asignatura_id,
                    'periodo_id'=>$request->periodo_id],
                    [
                        'matricula_id'=>$matricula_id,
                        'asignatura_id'=>$request->asignatura_id,
                        'periodo_id'=>$request->periodo_id,
                        'nota'=>$nota,
                        'notaanterior'=>'',
                        'notaperiodo'=>$notaperiodo,
                        'promedio'=>'',
                ]);

            }
            $data=$this->model::nivelacionesPeriodo($request->sede_id, $request->grado_id, $request->asignatura_id, $request->periodo_id);

            DB::commit();
            return response()->json([
                'code'=>200,
                'message' => 'Registros Guardados Exitosamente',
                'data' => $data
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'code'=>400,
                'message' => 'Ha ocurrido un error al crear el registro: ' . $e->getMessage(),
                'data' => $data
            ], Response::HTTP_OK);
        }
     }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Bucamos el producto
        $objeto = $this->model::byId($id);

        //Si el producto no existe devolvemos error no encontrado
        if (!$objeto) {
            return response()->json([
                'code'=>400,
                'data' => 'Registro no encontrado en la base de datos.'
            ], 404);
        }
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Buscamos el producto
        $objeto = $this->model::findOrfail($id);

        //Eliminamos el producto
        $objeto->delete();

        //Devolvemos la respuesta
        return response()->json([
            'code'=>200,
            'message' => 'Registro Eliminado Exitosamente'
        ], Response::HTTP_OK);
    }

    public function getByEstudiantesCalificacion(Request $request)
    {
       //Validación de datos
       $data = $request->only('grado_id', 'sede_id', 'asignatura_id', 'periodo_id');
       $validator = Validator::make($data, [
         'sede_id' => 'required',
           'grado_id' => 'required',
           'asignatura_id' => 'required',
           'periodo_id' => 'required',

         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }


       $objeto=[];
       $objeto=$this->model::nivelacionesPeriodo($request->sede_id, $request->grado_id, $request->asignatura_id, $request->periodo_id);
       if(count($objeto)>0){
           return response()->json([
               'code'=>300,
               'data' => [],
               'message' => 'Ya existen Nivelaciones  Para la Sede, Grado, Asignatura y Periodo seleccionado '
           ], Response::HTTP_OK);
       }
    //    $periodoActual=0;
    //    $PeriodoAnterior=0;
    //    if($request->periodo_id==1){
    //         $PeriodoAnterior=1;
    //         $periodoActual=2;
    //    }else{
    //         $PeriodoAnterior=3;
    //         $periodoActual=4;
    //    }

    $data=$this->model::getEstudiantesPerdidos($request->sede_id, $request->grado_id, $request->asignatura_id, $request->periodo_id);
    //$data=$this->model::getEstudiantesConPromedioBajo($request->sede_id, $request->grado_id, $request->asignatura_id, $periodoActual, $PeriodoAnterior);
       //$data=$this->model::getEstudiantesBajo($request->sede_id, $request->grado_id, $request->asignatura_id, $periodoActual, $PeriodoAnterior );
       if($data){
        return response()->json([
            'code'=>200,
            'data' => $data
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>300,
            'data' => [],
            'message' => 'No están completas las notas del semestre. Por favor verificar'
        ], Response::HTTP_OK);
       }
    }

    public function getNivelacionesPeriodo(Request $request)
    {
       //Validación de datos
       $data = $request->only('sede_id', 'grado_id','asignatura_id','periodo_id' );
       $validator = Validator::make($data, [
           'grado_id' => 'required',
           'sede_id' => 'required',
           'asignatura_id' => 'required',
           'periodo_id' => 'required'
         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }

       //Buscamos el producto
       $data=$this->model::nivelacionesPeriodo($request->sede_id, $request->grado_id, $request->asignatura_id, $request->periodo_id);
       if(count($data)==0){
        return response()->json([
            'code'=>300,
            'data' => [],
            'message' => 'No Existen Nivelaciones Para la Sede, Grado, Asignatura y Periodo seleccionado. Debe Primero registrar las notas '
        ], Response::HTTP_OK);
       }
       if($data){
        return response()->json([
            'code'=>200,
            'data' => $data
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>400,
            'data' => []
        ], Response::HTTP_OK);
       }
    }

    public function getByEstudiantes(Request $request)
    {
       //Validación de datos
       $data = $request->only('grado_id', 'sede_id');
       $validator = Validator::make($data, [
           'sede_id' => 'required',
           'grado_id' => 'required',
           'asignatura_id' => 'required',
           'periodo_id' => 'required',

         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }

       $periodoanterior=1;
       if($request->periodo_id==4){
        $periodoanterior=3;
       }
       $objeto=$this->model::getEstudiantesBajo($request->sede_id, $request->grado_id,
       $request->asignatura_id, $request->periodo_id, $periodoanterior);

       if($data){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>400,
            'data' => []
        ], Response::HTTP_OK);
       }
    }


     public function getNotaByMatricula(Request $request)
     {
        //Validación de datos
        $data = $request->only('matricula_id', 'periodo_id');
        $validator = Validator::make($data, [
            'matricula_id' => 'required',
            'periodo_id' => 'required',

          ]);

        //Si falla la validación error.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $objeto=$this->model::getNotasMatricula($request->periodo_id, $request->matricula_id);

        if($data){
         return response()->json([
             'code'=>200,
             'data' => $objeto
         ], Response::HTTP_OK);
        }else{
         return response()->json([
             'code'=>400,
             'data' => []
         ], Response::HTTP_OK);
        }
     }





}

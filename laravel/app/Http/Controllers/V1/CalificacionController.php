<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\LogroAcademico;
use App\Models\PromedioAsignatura;
use App\Models\Repositorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class CalificacionController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model=Calificacion::class;
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
        $data = $request->only('matriculas', 'asignatura_id', 'periodo_id', 'logro_cog', 'notas', 'logro_afe');
        $validator = Validator::make($data, [
            'matriculas' => 'required',
            'asignatura_id' => 'required',
            'periodo_id' => 'required',
            'notas' => 'required',
            'logro_afe' => 'required',
            'logro_cog' => 'required',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        DB::beginTransaction();
        try {
            $data=[];
            $matriculas=$request->input('matriculas');
            $notas=$request->input('notas');
            $logros_afe=$request->input('logro_afe');
            $bajo=0;
            $alto=0;
            $basico=0;
            $superior=0;
            $ganados=0;
            $perdidos=0;
            $promedio=0;
            $sumatoria=0;

            for ($i=0; $i < count($matriculas) ; $i++) {


                $matricula_id=$matriculas[$i];
                $nota=$notas[$i];
                $logro_afe=$logros_afe[$i];
                $orden=Repositorio::orden($request->asignatura_id, $request->grado_id);

                $calificacion=Calificacion::updateOrCreate(
                    ['matricula_id'=>$matricula_id,
                    'asignatura_id'=>$request->asignatura_id,
                    'periodo_id'=>$request->periodo_id],
                    [
                        'matricula_id'=>$matricula_id,
                        'asignatura_id'=>$request->asignatura_id,
                        'periodo_id'=>$request->periodo_id,
                        'nota'=>$nota,
                        'logro_cognitivo'=>$request->logro_cog,
                        'logro_afectivo'=>$logro_afe,
                        'orden'=>$orden,
                ]);
                $sumatoria=$sumatoria+$nota;

                if($nota>=1 && $nota<3){
                    $bajo++;
                }
                else if($nota>=3 && $nota<4){
                    $basico++;
                }
                else if($nota>=4 && $nota<4.49){
                  $alto++;
                }
                else if($nota>=4.5 && $nota<=5){
                    $superior++;
                }

            }
            $totalEst=$bajo+$basico+$alto+$superior;
            if($totalEst>0){
                $promedio=round(($sumatoria/$totalEst), 2);
                $perdidos=$bajo;
                $ganados=$basico+$alto+$superior;
                $promedioAsignatura=PromedioAsignatura::updateOrCreate([
                    'sede_id'=>$request->sede_id,
                    'grado_id'=>$request->grado_id,
                    'asignatura_id'=>$request->asignatura_id,
                    'periodo_id'=>$request->periodo_id,
                ],
                [
                    'sede_id'=>$request->sede_id,
                    'grado_id'=>$request->grado_id,
                    'asignatura_id'=>$request->asignatura_id,
                    'periodo_id'=>$request->periodo_id,
                    'valor'=>$promedio,
                    'ganados'=>$ganados,
                    'perdidos'=>$perdidos,
                    'bajo'=>$bajo,
                    'basico'=>$basico,
                    'alto'=>$alto,
                    'superior'=>$superior,
                ]);
            }

            $data=$this->model::calificacionesPeriodo($request->sede_id, $request->grado_id, $request->asignatura_id, $request->periodo_id);

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

       $data=$this->model::calificacionesPeriodo($request->sede_id, $request->grado_id, $request->asignatura_id, $request->periodo_id);
       if(count($data)>0){
        return response()->json([
            'code'=>300,
            'data' => $objeto,
            'message' => 'Ya existen Calificaciones Para el Grado, Asignatura y periodo seleccionado '
        ], Response::HTTP_OK);
       }else{
           //Buscamos el producto
            $data=$this->model::estudiantesCalificaciones($request->sede_id, $request->grado_id, $request->asignatura_id, $request->periodo_id);
            $objeto=$data;
        }

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

    public function getCalificacionesPeriodo(Request $request)
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
       $data=$this->model::calificacionesPeriodo($request->sede_id, $request->grado_id, $request->asignatura_id, $request->periodo_id);
       if(count($data)==0){
        return response()->json([
            'code'=>300,
            'data' => [],
            'message' => 'No Existen Calificaciones Para el Grado, Asignatura y periodo seleccionado. Debe Primero registrar las notas '
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

         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }

       $objeto=$this->model::estudiantesCalificacion($request->sede_id, $request->grado_id);

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

    public function storeIndividual(Request $request)
    {
        //Validamos los datos
        $data = $request->only('matricula_id', 'asignaturas', 'periodo_id',  'notas', 'grado_id', 'sede_id');
        $validator = Validator::make($data, [
            'matricula_id' => 'required',
            'periodo_id' => 'required',
            'notas' => 'required',
            'grado_id' => 'required',
            'sede_id' => 'required',
            'asignaturas' => 'required',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        DB::beginTransaction();
        try {
            $data=[];
            $notas=$request->input('notas');
            $asignaturas=$request->input('asignaturas');
            for ($i=0; $i < count($notas) ; $i++) {
                $logro_cog="";
                $logro_afe="";
                $matricula_id=$request->matricula_id;
                $nota=$notas[$i];
                $asignatura_id=$asignaturas[$i];
                $orden=Repositorio::orden($asignatura_id, $request->grado_id);
                $datLogroCog=LogroAcademico::filtro2($request->sede_id, $request->grado_id, $asignatura_id, $request->periodo_id, 2);
                $datLogroAfe=LogroAcademico::filtro2($request->sede_id, $request->grado_id, $asignatura_id, $request->periodo_id, 3);
                if($datLogroCog){
                    $logro_cog=$datLogroCog->id;
                }
                if($datLogroCog){
                    $logro_afe=$datLogroAfe->id;
                }

                $calificacion=Calificacion::updateOrCreate(
                    ['matricula_id'=>$matricula_id,
                    'asignatura_id'=>$asignatura_id,
                    'periodo_id'=>$request->periodo_id],
                    [
                        'matricula_id'=>$matricula_id,
                        'asignatura_id'=>$asignatura_id,
                        'periodo_id'=>$request->periodo_id,
                        'nota'=>$nota,
                        'logro_cognitivo'=>$logro_cog,
                        'logro_afectivo'=>$logro_afe,
                        'orden'=>$orden,
                ]);
            }

            $data=[];

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

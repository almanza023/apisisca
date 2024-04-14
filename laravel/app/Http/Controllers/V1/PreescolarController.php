<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\LogroAcademico;
use App\Models\Preescolar;
use App\Models\PromedioAsignatura;
use App\Models\Repositorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class PreescolarController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model=Preescolar::class;
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
        $data = $request->only('matricula_id', 'asignaturas', 'periodo_id', 'logros', 'logro_b');
        $validator = Validator::make($data, [
            'matricula_id' => 'required',
            'asignaturas' => 'required',
            'periodo_id' => 'required',
            'logros' => 'required',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        $asignaturas=$request->input('asignaturas');
        $logros=$request->input('logros');
        DB::beginTransaction();
        try {
            $data=[];
            for ($i=0; $i < count($asignaturas) ; $i++) {
                $asignatura_id=$asignaturas[$i];
                $logro_a=$logros[$i];
            $preescolar=Preescolar::updateOrCreate(
                ['matricula_id'=>$request->matricula_id,
                'asignatura_id'=>$asignatura_id,
                'periodo_id'=>$request->periodo_id
            ],
                [
                    'matricula_id'=>$request->matricula_id,
                    'asignatura_id'=>$asignatura_id,
                    'periodo_id'=>$request->periodo_id,
                    'logro_a'=>$logro_a,
                    'logro_b'=>$request->logro_b,
            ]);
        }

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

    public function getListadoEstudiantes(Request $request)
    {
       //Validación de datos
       $data = $request->only('grado_id', 'sede_id', 'periodo_id');
       $validator = Validator::make($data, [
           'sede_id' => 'required',
           'grado_id' => 'required',
           'periodo_id' => 'required',

         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }
      $objeto=$this->model::estudiantesListado($request->periodo_id,$request->sede_id, $request->grado_id);

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














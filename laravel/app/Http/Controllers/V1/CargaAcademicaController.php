<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\CargaAcademica;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class CargaAcademicaController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model=CargaAcademica::class;
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
        //Listamos todos las sedes
       $objeto=$this->model::getAll();
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


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validamos los datos
        $data = $request->only('sede_id', 'docente_id', 'grado_id', 'asignatura_id', 'ihs', 'porcentaje');
        $validator = Validator::make($data, [
            'sede_id' => 'required',
            'docente_id' => 'required',
            'grado_id' => 'required',
            'asignatura_id' => 'required',
            'ihs' => 'required',
            'porcentaje' => 'required',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //validar Duplicados
        //$duplicados=$this->model::validarDuplicados($request->sede_id, $request->grado_id, $request->asignatura_id);
        //if(count($duplicados)>0){
          //  return response()->json([
           //     'code'=>400,
           //     'message' => 'Ya existe un registro para la Sede, Grado y Aignatura Seleccionada',
           // ], Response::HTTP_OK);
        //}


        //Creamos el producto en la BD
        $objeto = $this->model::create([
            'sede_id'=>($request->sede_id),
            'docente_id'=>($request->docente_id),
            'grado_id'=>($request->grado_id),
            'asignatura_id'=>($request->asignatura_id),
            'ihs'=>($request->ihs),
            'porcentaje'=>($request->porcentaje),
            'estado'=>-1
        ]);

        //Respuesta en caso de que todo vaya bien.
        return response()->json([
            'code'=>200,
            'message' => 'Registro Creado Exitosamente',
        ], Response::HTTP_OK);
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
        $objeto = $this->model::find($id);

        //Si el producto no existe devolvemos error no encontrado
        if (!$objeto) {
            return response()->json([
                'code'=>200,
                'message' => 'Registro no encontrado en la base de datos.'
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
        //Validación de datos
        $data = $request->only('sede_id', 'docente_id', 'grado_id', 'asignatura_id', 'ihs', 'porcentaje');
        $validator = Validator::make($data, [
            'sede_id' => 'required',
            'docente_id' => 'required',
            'grado_id' => 'required',
            'asignatura_id' => 'required',
            'ihs' => 'required',
            'porcentaje' => 'required',
        ]);

        //Si falla la validación error.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Buscamos la Sede
        $objeto = $this->model::findOrfail($id);

        //Actualizamos la sede.
        $objeto->update([
            'sede_id'=>($request->sede_id),
            'docente_id'=>($request->docente_id),
            'grado_id'=>($request->grado_id),
            'asignatura_id'=>($request->asignatura_id),
            'ihs'=>($request->ihs),
            'porcentaje'=>($request->porcentaje)
        ]);

        //Devolvemos los datos actualizados.
        return response()->json([
            'code'=>200,
            'message' => 'Registro Actualizado Exitosamente',
        ], Response::HTTP_OK);
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

        //Eliminamos la Sede
        $objeto->delete();

        //Devolvemos la respuesta
        return response()->json([
            'code'=>200,
            'message' => 'Registro Eliminado Exitosamente'
        ], Response::HTTP_OK);
    }

    public function cambiarEstado(Request $request)
    {
        //Validación de datos
        $data = $request->only('id');
        $validator = Validator::make($data, [
            'id' => 'required'           ]);

        //Si falla la validación error.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Buscamos el producto
        $objeto = $this->model::findOrfail($request->id);

        if($objeto->estado==1){
            $objeto->estado=2;
            $objeto->save();
        }else{
            $objeto->estado=1;
            $objeto->save();
        }

        //Devolvemos los datos actualizados.
        return response()->json([
            'code'=>200,
            'message' => 'Estado Actualizado Extiosamente',
        ], Response::HTTP_OK);
    }
    public function activos()
    {
        //Listamos todos los registros activos
        $objeto=$this->model::active();
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

    public function getByAsignaturasDocente(Request $request)
    {
       //Validación de datos
       $data = $request->only('docente_id', 'grado_id', 'sede_id');
       $validator = Validator::make($data, [
           'sede_id' => 'required',
           'grado_id' => 'required',
           'docente_id' => 'required'
         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }

       //Buscamos el producto
       $objeto = $this->model::asignaturasDocente($request->docente_id, $request->sede_id, $request->grado_id );

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

    public function getByGradosDocente(Request $request)
    {
       //Validación de datos
       $data = $request->only('docente_id, sede_id');
       $validator = Validator::make($data, [
           'asignatura_id' => 'required',
           'sede_id' => 'required'
         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }

       //Buscamos el producto
       $objeto = $this->model::gradosDocente($request->docente_id, $request->sede_id );

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

    public function getByAsignaturasGrado(Request $request)
    {
               //Buscamos el producto
       $objeto = $this->model::asignaturasGrado($request->sede_id, $request->grado_id  );

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

    public function filtrar(Request $request)
    {
        //Validación de datos
        $data = $request->only('sede_id', 'grado_id', 'asignatura_id', 'docente_id');
        //Buscamos el producto
        $objeto = $this->model::filtrar($request->sede_id, $request->grado_id, $request->asignatura_id, $request->docente_id);

        //Devolvemos los datos actualizados.
        return response()->json([
            'code'=>200,
            'message' => '',
            'data' => $objeto
        ], Response::HTTP_OK);
    }

    public function agregados()
    {
        //Listamos todos las sedes
       $objeto=$this->model::getAgregados();
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

    public function agregadosActualizar()
    {
        //Listamos todos las sedes
       $objeto=$this->model::getAgregados();
       if($objeto){
        $objeto->each(function ($obj) {
            $obj->update([
                'estado' => '1'
            ]);
        });

        return response()->json([
            'code'=>200,
            'message' => 'Registros Agregados Exitosamente',
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'message' => 'Erro al Agregar Registros',
        ], Response::HTTP_OK);
       }
    }

    public function getByGrados(Request $request)
    {
       //Validación de datos
       $data = $request->only('sede_id');
       $validator = Validator::make($data, [
           'sede_id' => 'required'
         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }

       //Buscamos el producto
       $objeto = $this->model::getGradosBySede($request->sede_id );

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





}

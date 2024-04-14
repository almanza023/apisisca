<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class DocenteController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model=Docente::class;
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
        $data = $request->only(
            'sede_id','nombres', 'apellidos', 'documento', 'correo', 'telefono', 'escalafon',
            'especialidad', 'nivel', 'tipo',
        );
        $validator = Validator::make($data, [
            'sede_id' => 'required',
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'documento' => 'required',
            'correo' => 'required',
            'telefono' => 'required',
            'escalafon' => 'required',
            'especialidad' => 'required',
            'nivel' => 'required',
            'tipo' => 'required',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        //Creamos el producto en la BD
        $objeto = $this->model::create([
            'sede_id'=>($request->sede_id),
            'nombres'=>strtoupper($request->nombres),
            'apellidos'=>strtoupper($request->apellidos),
            'documento'=>($request->documento),
            'correo'=>($request->correo),
            'telefono'=>($request->telefono),
            'escalafon'=>($request->escalafon),
            'especialidad'=>($request->especialidad),
            'nivel'=>($request->nivel),
            'tipo'=>($request->tipo),
        ]);

        //Respuesta en caso de que todo vaya bien.
        return response()->json([
            'code'=>200,
            'message' => 'Docente Creado Exitosamente',
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
        $data = $request->only(
            'sede_id','nombres', 'apellidos', 'documento', 'correo', 'telefono', 'escalafon',
            'especialidad', 'nivel', 'tipo',
        );
        $validator = Validator::make($data, [
            'sede_id' => 'required',
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'documento' => 'required',
            'correo' => 'required',
            'telefono' => 'required',
            'escalafon' => 'required',
            'especialidad' => 'required',
            'nivel' => 'required',
            'tipo' => 'required',
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
            'nombres'=>strtoupper($request->nombres),
            'apellidos'=>strtoupper($request->apellidos),
            'documento'=>($request->documento),
            'correo'=>($request->correo),
            'telefono'=>($request->telefono),
            'escalafon'=>($request->escalafon),
            'especialidad'=>($request->especialidad),
            'nivel'=>($request->nivel),
            'tipo'=>($request->tipo),
        ]);

        //Devolvemos los datos actualizados.
        return response()->json([
            'code'=>200,
            'message' => 'Docente Actualizado Exitosamente',
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
        $objeto=$this->model::getDocente();
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

    public function getAsignaturasDocentes(Request $request)
    {
        //Listamos todos los registros activos
        $data = $request->only('docente_id', 'grado_id');
        $validator = Validator::make($data, [
            'docente_id' => 'required',
            'grado_id' => 'required',
           ]);
       $objeto=$this->model::asignaturasDocente($request->docente_id, $request->grado_id);
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

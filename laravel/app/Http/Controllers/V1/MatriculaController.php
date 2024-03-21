<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class MatriculaController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model=Matricula::class;
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
        $data = $request->only('nombres', 'apellidos', 'tipo_doc', 'num_doc', 'fecha_nac', 'lugar_nac',
        'estrato', 'direccion', 'eps', 'zona', 'tipo_sangre', 'desplazado', 'nombre_madre', 'nombre_padre', 'nombre_acudiente',
        'telefono_acudiente', 'grado_id', 'sede_id', 'nivel', 'folio', 'repitente', 'matricula_id' );
        $validator = Validator::make($data, [
            'nombres' => 'required|max:200|string',
            'apellidos' => 'required|max:200|string',
            'tipo_doc' => 'required',
            'num_doc' => 'required',
            'sede_id' => 'required',
            'grado_id' => 'required',
            'folio' => 'required',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        DB::beginTransaction();
        try {
            $estudiante=Estudiante::updateOrCreate(
                ['num_doc'=>$request->num_doc],
                [
                'nombres'=>strtoupper($request->nombres),
                'apellidos'=>strtoupper($request->apellidos),
                'tipo_doc'=>strtoupper($request->tipo_doc),
                'num_doc'=>($request->num_doc),
                'fecha_nac'=>($request->fecha_nac),
                'lugar_nac'=>($request->lugar_nac),
                'estrato'=>($request->estrato),
                'direccion'=>strtoupper($request->direccion),
                'eps'=>strtoupper($request->eps),
                'zona'=>strtoupper($request->zona),
                'tipo_sangre'=>strtoupper($request->tipo_sangre),
                'desplazado'=>strtoupper($request->desplazado),
                'nombre_madre'=>strtoupper($request->nombre_madre),
                'nombre_padre'=>strtoupper($request->nombre_padre),
                'nombre_acudiente'=>strtoupper($request->nombre_acudiente),
                'telefono_acudiente'=>($request->telefono_acudiente),
            ]);
            $nivel="";
            if($request->grado_id>=1 && $request->grado_id<=2){
                $nivel="PREESCOLAR";
            }
            if($request->grado_id>=3 && $request->grado_id<=7){
                $nivel="PRIMARIA";
            }
            if($request->grado_id>=8 && $request->grado_id<=11){
                $nivel="SECUNDARIA";
            }
            if($request->grado_id>=12 && $request->grado_id<=13){
                $nivel="MEDIA ACADEMICA";
            }

            //Creamos el registro en la BD
            $objeto = $this->model::updateOrCreate(
                ['id'=>$request->matricula_id],
                [
                'estudiante_id'=>$estudiante->id,
                'grado_id'=>$request->grado_id,
                'sede_id'=>$request->sede_id,
                'nivel'=>$nivel,
                'folio'=>$request->folio,
                'periodo'=>'2024',
                'repitente'=>$request->repitente,
            ]);
            DB::commit();
            $mensaje=$request->matricula_id?'Actualizado':'Creado';
            return response()->json([
                'code'=>200,
                'message' => 'Registro '.$mensaje.' Exitosamente',
                'data' => $objeto
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'code'=>400,
                'message' => 'Ha ocurrido un error al crear el registro: ' . $e->getMessage(),
                'data' => $objeto
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
       //Validación de datos
       $data = $request->only('grado_id', 'sede_id', 'repitente', 'folio', 'cambio_sede');
       $validator = Validator::make($data, [
           'grado_id' => 'required',
           'sede_id' => 'required',
           'repitente' => 'required',
           'folio' => 'required',
           'cambio_sede' => 'required',
       ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }

       //Buscamos el producto
       $objeto = $this->model::findOrfail($id);
       $nivel="";
            if($request->grado_id>=1 && $request->grado_id<=2){
                $nivel="PREESCOLAR";
            }
            if($request->grado_id>=3 && $request->grado_id<=7){
                $nivel="PRIMARIA";
            }
            if($request->grado_id>=8 && $request->grado_id<=11){
                $nivel="SECUNDARIA";
            }
            if($request->grado_id>=12 && $request->grado_id<=13){
                $nivel="MEDIA ACADEMICA";
            }

       //Actualizamos el producto.
       $objeto->update([
           'grado_id' => ($request->grado_id),
           'sede_id' => ($request->sede_id),
           'repitente' => ($request->repitente),
           'folio' => ($request->folio),
           'nivel' => ($nivel),
           'cambio_sede' => ($request->cambio_sede),
       ]);

       //Devolvemos los datos actualizados.
       return response()->json([
           'code'=>200,
           'message' => 'Matricula Actualizada Exitosamente',
           'data' => $objeto
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
       $data = $request->only('grado_id, sede_id');
       $validator = Validator::make($data, [
           'grado_id' => 'required',
           'sede_id' => 'required'
         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }

       //Buscamos el producto
       $objeto = $this->model::estudiantesCalificacion($request->sede_id, $request->grado_id );

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

    public function getBySedeGrado(Request $request)
    {
       //Validación de datos
       $data = $request->only('grado_id, sede_id');
       $validator = Validator::make($data, [
           'grado_id' => 'required',
           'sede_id' => 'required'
         ]);

       //Si falla la validación error.
       if ($validator->fails()) {
           return response()->json(['error' => $validator->messages()], 400);
       }

       //Buscamos el producto
       $objeto = $this->model::listado($request->sede_id, $request->grado_id );

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
        $data = $request->only('sede_id', 'grado_id', );
        //Buscamos el producto
        $objeto = $this->model::filtrar($request->sede_id, $request->grado_id);

        //Devolvemos los datos actualizados.
        return response()->json([
            'code'=>200,
            'message' => '',
            'data' => $objeto
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




}

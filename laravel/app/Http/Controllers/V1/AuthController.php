<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\CargaAcademica;
use App\Models\DireccionGrado;
use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    //Función que utilizaremos para registrar al usuario
    public function register(Request $request)
    {
        //Indicamos que solo queremos recibir name, email y password de la request
        $data = $request->only('name', 'username', 'email', 'password', 'rol', 'numerodocumento');

        //Realizamos las validaciones
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'username' => 'required|string',
            'numerodocumento' => 'required|numeric|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50',
            'rol' => 'required|numeric',
        ]);

        //Devolvemos un error si fallan las validaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Creamos el nuevo usuario
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'numerodocumento' => $request->numerodocumento,
            'email' => $request->email,
            'rol' => $request->tipo,
            'password' => bcrypt($request->password)
        ]);

        //Nos guardamos el usuario y la contraseña para realizar la petición de token a JWTAuth
        $credentials = $request->only('email', 'password');

        //Devolvemos la respuesta con el token del usuario
        return response()->json([
            'code'=>200,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'numerodocumento' => $user->numerodocumento,
            'message' => 'Usuario Creado',
            'token' => JWTAuth::attempt($credentials),

        ], Response::HTTP_OK);
    }

    //Funcion que utilizaremos para hacer login
    public function authenticate(Request $request)
    {
        //Indicamos que solo queremos recibir email y password de la request
        $credentials = $request->only('documento', 'password');

        //Validaciones
        $validator = Validator::make($credentials, [
            'documento' => 'required',
            'password' => 'required'
        ]);

        //Devolvemos un error de validación en caso de fallo en las verificaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Intentamos hacer login
        try {
            if (!$token = JWTAuth::attempt($credentials)) {

                //Credenciales incorrectas.
                return response()->json([
                    'message' => 'Login failed',
                ], 401);

            }
        } catch (JWTException $e) {

            //Error chungo
            return response()->json([
                'message' => 'Error',
            ], 500);

        }
        $user=Auth::user();
        if($user->estado==1){

            if($user->tipo==1){
                $sede_id=$user->docente->sede_id;
                $docentesGrados=CargaAcademica::gradosDocente($user->usable_id, $sede_id);
                $grados=[];
                if(count($docentesGrados)>0){
                    $grados=$docentesGrados;
                }
                $direccionesGrado=DireccionGrado::getByDocente($user->usable_id, $sede_id);
                $direcciones=[];
                if(count($direccionesGrado)>0){
                    $direcciones=$direccionesGrado;
                }

            //Devolvemos el token
            return response()->json([
                'code'=>200,
                "name"=>$user->name,
                "email"=>$user->email,
                "user_id"=>$user->id,
                "docente_id"=>$user->usable_id,
                "tipo"=>$user->tipo,
                "sede_id"=>$user->docente->sede_id,
                "grados"=>$grados,
                "direcciones"=>$direcciones,
                'token' => $token,
            ]);
            }else{
                //Devolvemos el token
                return response()->json([
                    'code'=>200,
                    "name"=>$user->name,
                    "email"=>$user->email,
                    "user_id"=>$user->id,
                    "docente_id"=>$user->usable_id,
                    "tipo"=>$user->tipo,
                    'token' => $token,
                ]);
            }


        }else{
            return response()->json([
                'message' => 'Login failed',
            ], 401);
        }

    }

    //Función que utilizaremos para eliminar el token y desconectar al usuario
    public function logout(Request $request)
    {
        //Validamos que se nos envie el token
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }


        try {
            //Si el token es valido eliminamos el token desconectando al usuario.
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'Usuario desconectado'
            ]);

        } catch (JWTException $exception) {

            //Error chungo

            return response()->json([
                'success' => false,
                'message' => 'Error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    //Función que utilizaremos para obtener los datos del usuario y validar si el token a expirado.
    public function getUser(Request $request)
    {
        //Validamos que la request tenga el token
        $this->validate($request, [
            'token' => 'required'
        ]);

        //Realizamos la autentificación
        $user = JWTAuth::authenticate($request->token);

        //Si no hay usuario es que el token no es valido o que ha expirado
        if(!$user)
            return response()->json([
                'message' => 'Invalid token / token expired',
            ], 401);

        //Devolvemos los datos del usuario si todo va bien.
        return response()->json(['user' => $user]);
    }
}

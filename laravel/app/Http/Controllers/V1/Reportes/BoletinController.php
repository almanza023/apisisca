<?php

namespace App\Http\Controllers\V1\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Matricula;
use App\Models\Reportes\BoletinDos;
use App\Models\Reportes\BoletinFinalDos;
use App\Models\Reportes\BoletinFinalUno;
use App\Models\Reportes\BoletinTres;
use App\Models\Reportes\BoletinUno;
use App\Models\Reportes\ConsolidadoDos;
use App\Models\Reportes\ConsolidadoTres;
use App\Models\Reportes\ConsolidadoUno;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class BoletinController extends Controller
{
    protected $user;   

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');       
        if($token != '')
            //En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos en una variable, nosotros no lo utilizaremos.
            $this->user = JWTAuth::parseToken()->authenticate();
    }



    public function boletines(Request $request)
    {
        $validated = $request->validate([
            'sede_id' => 'required',
            'grado_id' => 'required',
            'periodo_id' => 'required',
        ]);

        $grado=$request->grado_id;
        $sede=$request->sede_id;
        $periodo=$request->periodo_id;
        $base64String="";
        $pdf = app('Fpdf');
        if($grado>=3 && $grado<=4){
            $base64String=BoletinUno::reporte($sede, $grado, $periodo, $pdf);
        }else if($grado>=5 && $grado<=7){
            $base64String=BoletinDos::reporte($sede, $grado, $periodo, $pdf);
        }else if($grado>=8 && $grado<=13){
            $base64String=BoletinTres::reporte($sede, $grado, $periodo, $pdf);
        }


        return response()->json([
            'code'=>200,
            'pdf' => $base64String
        ], Response::HTTP_OK);
    }


    public function boletinesFinales(Request $request)
    {
        $validated = $request->validate([
            'sede' => 'required',
            'grado' => 'required',
        ]);
        $grado=$request->grado;
        $sede=$request->sede;
        $pdf = app('Fpdf');
        if($grado>=3 && $grado<=4){
            BoletinFinalUno::reporte($sede, $grado, $pdf);
        }else if($grado>=5 && $grado<=7){
            BoletinFinalDos::reporte($sede, $grado, $pdf);
        }else if($grado>=8 && $grado<=13){
            //BoletinTres::reporte($sede, $grado, $pdf);
        }
    }

    public function consolidados(Request $request)
    {
        $validated = $request->validate([
            'sede_id' => 'required',
            'grado_id' => 'required',
            'periodo_id' => 'required',
        ]);

        $grado=$request->grado_id;
        $sede=$request->sede_id;
        $periodo=$request->periodo_id;
        $matriculas=Matricula::listado($sede, $grado);
        $base64String="";
        $pdf = app('Fpdf');
        if($grado>=3 && $grado<=4){
            $base64String=ConsolidadoUno::reporte($sede, $grado, $periodo, $pdf, $matriculas);
        }else if($grado>=5 && $grado<=7){
            $base64String=ConsolidadoDos::reporte($sede, $grado, $periodo, $pdf, $matriculas);
        }else if($grado>=8 && $grado<=13){
            $base64String=ConsolidadoTres::reporte($sede, $grado, $periodo, $pdf, $matriculas);
        }


        return response()->json([
            'code'=>200,
            'pdf' => $base64String
        ], Response::HTTP_OK);
    }



}

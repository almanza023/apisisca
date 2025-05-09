<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ObservacionFinal extends Model
{
    protected $table = 'observaciones';
    protected $fillable = [ 'matricula_id','logro',  'estado'];


    public function matricula()
    {
        return $this->belongsTo('App\Models\Matricula', 'matricula_id', );
    }


    public function logro()
    {
        return $this->belongsTo('App\Models\LogroObservacion', 'logro' );
    }

    public static function ObservacionByMatricula($matricula_id){
        $obj= DB::table('observaciones as ob')
        ->join('logros_observaciones as lg', 'lg.id', '=', 'ob.logro')
        ->select('lg.descripcion')
        ->where('ob.matricula_id', $matricula_id)
        ->first();
        if(empty($obj)){
            return '';
        }else{
            return $obj;
        }
    }


    public static function observacionesPeriodo($sede, $grado){
        return DB::table('observaciones as ob')
        ->join('matriculas as m', 'm.id', '=', 'ob.matricula_id')
        ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
        ->join('grados as g', 'g.id', '=', 'm.grado_id')
        ->join('sedes as s', 's.id', '=', 'm.sede_id')
        ->select('ob.id', 'm.id as matricula_id', 'ob.logro',  'e.apellidos',
        'e.nombres', 'g.descripcion as grado', 's.nombre as sede', 'ob.created_at')
        ->where('m.grado_id', $grado)
        ->where('m.sede_id', $sede)
        ->orderBy('e.apellidos', 'asc')
        ->get();
    }

    public static function estudiantesListado($sede, $grado){
        return DB::table('matriculas as m')
        ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
        ->where('m.sede_id', $sede)
        ->where('m.grado_id', $grado)
        ->where('m.situacion', 'ACTIVO')
        ->select('m.id', 'm.estudiante_id', 'e.apellidos', 'e.nombres')
        ->orderBy('e.apellidos', 'asc')
        ->get();
    }






}

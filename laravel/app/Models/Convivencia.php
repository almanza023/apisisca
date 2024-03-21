<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Convivencia extends Model
{


    protected $table = 'convivencia';
    protected $fillable = [ 'matricula_id','asignatura_id', 'periodo_id', 'logro',  'estado'];


    public function matricula()
    {
        return $this->belongsTo('App\Models\Matricula', 'matricula_id', );
    }

    public function asignatura()
    {
        return $this->belongsTo('App\Models\Asignatura', 'asignatura_id', );
    }

    public function periodo()
    {
        return $this->belongsTo('App\Models\Periodo', 'periodo_id', );
    }

    public function logro()
    {
        return $this->belongsTo('App\Models\LogroDisciplinario', 'logro', );
    }

    public static function getAtll(){
        return Convivencia::with(['matericula','asignatura', 'logro'])->get();
    }

    public static function byPeriodo($grado, $asignatura, $periodo){
        return Convivencia::where('grado_id', $grado)->where('asignatura_id', $asignatura)
        ->where('periodo_id', $periodo)->get();
    }

    public static function convivenciaPeriodo($sede, $grado, $periodo){
        return DB::table('convivencia as c')
        ->join('matriculas as m', 'm.id', '=', 'c.matricula_id')
        ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
        ->join('sedes as se', 'se.id', '=', 'm.sede_id')
        ->join('asignaturas as a', 'a.id', '=', 'c.asignatura_id')
        ->join('grados as g', 'g.id', '=', 'm.grado_id')
        ->select('c.id', 'm.id as matricula_id', 'c.logro',  'e.apellidos', 'e.nombres', 'c.periodo_id',
         'a.nombre as asignatura', 'g.descripcion as grado', 'se.nombre as sede', 'c.estado', 'c.created_at')
        ->where('m.grado_id', $grado)
        ->where('c.periodo_id', $periodo)
        ->where('m.sede_id', $sede)
        ->orderBy('e.apellidos', 'asc')
        ->get();
    }

    public static function reportConvivencia($matricula, $periodo){
        return Convivencia::where('matricula_id', $matricula)
            ->where('periodo_id', $periodo)
            ->first();
    }

     public static function byMatriculaPeriodo($matricula, $periodo){
        return Convivencia::where('matricula_id', $matricula)
        ->where('periodo_id', $periodo)->first();
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

    public static function getConvivenciaMatricula($periodo_id, $matricula_id){
        return DB::select('SELECT
        (SELECT id FROM asignaturas ga WHERE ga.id=29) AS id,
        (SELECT nombre FROM asignaturas ga WHERE ga.id=29) AS descripcion,
        (SELECT logro FROM convivencia ca WHERE ca.matricula_id=m.id
         AND ca.periodo_id=? LIMIT 1) AS logro
        FROM matriculas m
        WHERE m.id=? ', [$periodo_id, $matricula_id]);
    }






}

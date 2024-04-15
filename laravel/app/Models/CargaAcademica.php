<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CargaAcademica extends Model
{

    protected $table = 'carga_academicas';
    protected $fillable = ['sede_id', 'docente_id', 'grado_id', 'asignatura_id', 'ihs', 'porcentaje', 'estado'];

    public function docente()
    {
        return $this->belongsTo('App\Models\Docente', 'docente_id');
    }

    public function grado()
    {
        return $this->belongsTo('App\Models\Grado', 'grado_id');
    }

    public function sede()
    {
        return $this->belongsTo('App\Models\Sede', 'sede_id');
    }

    public function asignatura()
    {
        return $this->belongsTo('App\Models\Asignatura', 'asignatura_id');
    }



    public static function asignaturasDocente($docente, $sede, $grado){

        return DB::table('carga_academicas as c')
        ->join('asignaturas as asg', 'asg.id', '=', 'c.asignatura_id')
        ->select('asg.id', 'asg.nombre')
        ->where('c.docente_id', $docente)
        ->where('c.grado_id', $grado)
        ->where('c.sede_id', $sede)
        ->orderBy('c.asignatura_id', 'asc')
        ->get();
    }

    public static function asignaturasGrado($sede, $grado)
    {
        return DB::select('SELECT distinct ca.asignatura_id as id,
        (SELECT nombre FROM asignaturas ga WHERE ga.id=ca.asignatura_id) AS nombre
         FROM carga_academicas ca
        WHERE ca.sede_id=? AND ca.grado_id=?
        ORDER BY ca.asignatura_id ASC', [$sede, $grado]);
    }

    public static function gradosDocente($docente, $sede){

        return DB::table('carga_academicas as c')
        ->join('grados as g', 'g.id', '=', 'c.grado_id')
        ->select('g.id', 'g.descripcion')
        ->where('c.docente_id', $docente)
        ->where('c.sede_id', $sede)
        ->groupBy('g.id', 'g.descripcion')
        ->get();
    }

    public static function validarAsignacion($sede, $docente, $grado, $asignatura){
        return CargaAcademica::where('docente_id', $docente)
        ->where('sede_id', $sede)
        ->where('grado_id', $grado)
        ->where('asignatura_id', $asignatura)
        ->get();
    }

    public static function getIhs($grado, $asignatura){
        return CargaAcademica::where('grado_id', $grado)
        ->where('asignatura_id', $asignatura)
        ->first();
    }

    public static function getDocente($sede, $grado, $asignatura){
        return CargaAcademica::where('sede_id', $sede)
        ->where('grado_id', $grado)
        ->where('asignatura_id', $asignatura)
        ->first();
    }

     public static function gradosDocente2($docente, $sede){

        return CargaAcademica::where('docente_id', $docente)
        ->where('sede_id', $sede)
        ->groupBy('grado_id')->get();
    }

    public static function getAll(){

        return CargaAcademica::with(['sede', 'grado', 'asignatura', 'docente'])
        ->where('estado','>=', '1')->get();
    }

    public static function getAgregados(){

        return CargaAcademica::with(['sede', 'grado', 'asignatura', 'docente'])
        ->where('estado', '-1')->get();
    }

    public static function filtrar($sede, $grado, $asignatura, $docente){

        $objeto = CargaAcademica::query()
        ->when($sede, fn($query, $sede_id) => $query->where('sede_id', $sede_id))
        ->when($grado, fn($query, $grado_id) => $query->where('grado_id', $grado_id))
        ->when($asignatura, fn($query, $asignatura_id) => $query->where('asignatura_id', $asignatura_id))
        ->when($docente, fn($query, $docente_id) => $query->where('docente_id', $docente_id))
        ->with(['sede', 'grado', 'asignatura', 'docente'])
        ->get();
        return $objeto;

    }

    public static function validarDuplicados($sede, $grado, $asignatura){
        return CargaAcademica::where('sede_id', $sede)
        ->where('grado_id', $grado)
        ->where('asignatura_id', $asignatura)
        ->get();
    }

    public static function getGradosBySede($sede){
        return DB::select('SELECT distinct ca.grado_id as id,
        (SELECT descripcion FROM grados ga WHERE ga.id=ca.grado_id) AS descripcion
         FROM carga_academicas ca
        WHERE ca.sede_id=?  order by ca.grado_id asc', [$sede]);
    }

    public static function getBySedeGrado($sede, $grado){

        return DB::table('carga_academicas as ca')
        ->join('asignaturas as ga', 'a.id', '=', 'ca.asignatura_id')
        ->join('tipo_asignaturas as ta', 'ta.id', '=', 'a.tipo_asignatura_id')
        ->select('a.nombre', 'ta.descripcion', 'a.id')
        ->where('ca.sede_id', $sede)
        ->where('ca.grado_id', $grado)        
        ->get();

    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogroAcademico extends Model
{


    protected $table = 'logro_academicos';
    protected $fillable = ['sede_id', 'grado_id', 'asignatura_id', 'periodo_id',
    'tipo_logro_id', 'descripcion'];

    public function tipo()
    {
        return $this->belongsTo('App\Models\TipoLogro', 'tipo_logro_id');
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

    public function periodo()
    {
        return $this->belongsTo('App\Models\Periodo', 'periodo_id');
    }

    public static function filtro($sede, $grado, $asignatura, $periodo, $tipo){

        return LogroAcademico::where('asignatura_id', $asignatura)
        ->where('grado_id', $grado)
        ->where('sede_id', $sede)
        ->where('periodo_id', $periodo)
        ->where('tipo_logro_id', $tipo)
        ->orderBy('periodo_id', 'asc')
        ->get();
    }

    public static function filtro2($sede, $grado, $asignatura, $periodo, $tipo){

        return LogroAcademico::where('asignatura_id', $asignatura)
        ->where('grado_id', $grado)
        ->where('sede_id', $sede)
        ->where('periodo_id', $periodo)
        ->where('tipo_logro_id', $tipo)
        ->orderBy('periodo_id', 'asc')->first();
    }


    public static function bySedeGrado($sede, $grado, $asignatura, $periodo, $tipo){

        return LogroAcademico::where('asignatura_id', $asignatura)
        ->where('grado_id', $grado)
        ->where('sede_id', $sede)
        ->where('periodo_id', $periodo)
        ->where('tipo_logro_id', $tipo)
        ->orderBy('periodo_id', 'asc')->first();
    }

    public static function getAll(){

        return LogroAcademico::with(['sede', 'grado', 'asignatura', 'periodo', 'tipo'])
        ->where('estado','>=', '1')->get();
    }



    public static function filtrar($sede, $grado, $asignatura, $periodo, $tipo){

        $objeto = LogroAcademico::query()
        ->when($sede, fn($query, $sede_id) => $query->where('sede_id', $sede_id))
        ->when($grado, fn($query, $grado_id) => $query->where('grado_id', $grado_id))
        ->when($asignatura, fn($query, $asignatura_id) => $query->where('asignatura_id', $asignatura_id))
        ->when($periodo, fn($query, $periodo_id) => $query->where('periodo_id', $periodo_id))
        ->with(['sede', 'grado', 'asignatura', 'periodo', 'tipo'])
        ->orderBy('id', 'desc')
        ->get();
        return $objeto;

    }

    public static function getFiltro($sede, $grado, $asignatura, $periodo){

        return LogroAcademico::where('asignatura_id', $asignatura)
        ->where('grado_id', $grado)
        ->where('sede_id', $sede)
        ->where('periodo_id', $periodo)
        ->orderBy('periodo_id', 'asc')
        ->with(['tipo'])
        ->get();
    }




}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogroPreescolar extends Model
{


    protected $table = 'logros_preescolar';
    protected $fillable = ['sede_id', 'grado_id', 'asignatura_id', 'descripcion', 'tipo','estado'];


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

    public static function filtro($sede, $grado, $asignatura){

        return LogroPreescolar::where('asignatura_id', $asignatura)
        ->where('grado_id', $grado)
        ->where('sede_id', $sede)
        ->orderBy('tipo', 'asc')->get();
    }

    public static function getAll(){

        return LogroPreescolar::with(['sede', 'grado', 'asignatura'])
        ->where('estado','>=', '1')->get();
    }

    public static function filtrar($sede, $grado, $asignatura){

        $objeto = LogroPreescolar::query()
        ->when($sede, fn($query, $sede_id) => $query->where('sede_id', $sede_id))
        ->when($grado, fn($query, $grado_id) => $query->where('grado_id', $grado_id))
        ->when($asignatura, fn($query, $asignatura_id) => $query->where('asignatura_id', $asignatura_id))
        ->with(['sede', 'grado', 'asignatura'])
        ->orderBy('id', 'desc')
        ->get();
        return $objeto;

    }

    public static function getFiltro($sede, $grado, $asignatura){

        return LogroPreescolar::where('asignatura_id', $asignatura)
        ->where('grado_id', $grado)
        ->where('sede_id', $sede)
        ->orderBy('id', 'desc')
        ->get();
    }





}

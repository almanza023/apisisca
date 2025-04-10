<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LogroObservacion extends Model
{


    protected $table = 'logros_observaciones';
    protected $fillable = ['sede_id', 'grado_id', 'descripcion','estado'];


    public function grado()
    {
        return $this->belongsTo('App\Models\Grado', 'grado_id');
    }

    public function sede()
    {
        return $this->belongsTo('App\Models\Sede', 'sede_id');
    }


    public static function active()
    {
        return LogroObservacion::where('estado', 1)->get();
    }

    public static function filtrar($sede, $grado){

        $objeto = LogroObservacion::query()
        ->when($sede, fn($query, $sede_id) => $query->where('sede_id', $sede_id))
        ->when($grado, fn($query, $grado_id) => $query->where('grado_id', $grado_id))
        ->with(['sede', 'grado'])
        ->orderBy('id', 'desc')
        ->get();
        return $objeto;

    }

    public static function getFiltro($sede, $grado){

        return LogroObservacion::where('grado_id', $grado)
        ->where('sede_id', $sede)
        ->orderBy('id', 'asc')
        ->get();
    }



}

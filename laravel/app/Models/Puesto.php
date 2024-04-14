<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Puesto extends Model
{
    protected $table = 'puestos';
    protected $fillable = ['matricula_id', 'periodo_id',
     'promedio', 'ganadas', 'perdidas',
      'niveladas', 'puesto', 'areasp', 'areasg'];

    public function matricula()
    {
        return $this->belongsTo('App\Models\Matricula', 'matricula_id' );
    }

    public static function getPuesto($sede, $grado, $periodo){

        return DB::table('puestos as p')
        ->join('matriculas as m', 'm.id', '=', 'p.matricula_id')
        ->where('m.sede_id', $sede)
        ->where('m.grado_id', $grado)
        ->where('periodo_id', $periodo)
        ->orderBy('promedio', 'desc')
        ->get();
    }
    public static function getPuestos($grado, $periodo){
        return Puesto::join('matriculas as m', 'm.id', '=', 'matricula_id')
        ->where('m.grado_id', $grado)
        ->where('periodo_id', $periodo)
        ->orderBy('promedio', 'desc')
        ->get();
    }



}

<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Nivelacion extends Model
    {


        protected $table = 'nivelaciones';
        protected $fillable = [ 'matricula_id','asignatura_id', 'periodo_id',
         'nota', 'estado'];


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

        public function setNotaAttribute($value)
        {
            $this->attributes['nota'] = number_format($value, 2);
        }

        public static function byPeriodo($grado, $asignatura, $periodo){
            return Nivelacion::where('grado_id', $grado)->where('asignatura_id', $asignatura)
            ->where('periodo_id', $periodo)->get();
        }

        public static function nivelacionesPeriodo($sede, $grado, $asignatura, $periodo){
            return DB::table('nivelaciones as n')
            ->join('matriculas as m', 'm.id', '=', 'n.matricula_id')
            ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
            ->join('asignaturas as a', 'a.id', '=', 'n.asignatura_id')
            ->join('grados as g', 'g.id', '=', 'm.grado_id')
            ->select('n.id', 'm.id as matricula_id', 'n.nota',
             'e.apellidos', 'e.nombres', 'a.nombre', 'g.descripcion')
            ->where('m.grado_id', $grado)
            ->where('n.asignatura_id', $asignatura)
            ->where('n.periodo_id', $periodo)
            ->where('m.sede_id', $sede)
            ->orderBy('e.apellidos', 'asc')
            ->get();
        }

        public static function getNivelacion($matricula, $asignatura, $periodo){
            return Nivelacion::where('matricula_id', $matricula)->where('asignatura_id', $asignatura)
            ->where('periodo_id', $periodo)->first();
        }







    }




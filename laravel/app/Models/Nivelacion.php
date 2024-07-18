<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Nivelacion extends Model
    {


        protected $table = 'nivelaciones';
        protected $fillable = [ 'matricula_id','asignatura_id', 'periodo_id',
         'nota', 'notaanterior', 'notaperiodo', 'promedio', 'estado'];


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
            ->select('n.id', 'm.sede_id', 'm.grado_id', 'n.asignatura_id', 'm.id as matricula_id',
            'n.nota', 'n.notaanterior', 'n.notaperiodo', 'n.promedio',
             'e.apellidos', 'e.nombres', 'a.nombre', 'g.descripcion as grado',
              'a.nombre as asignatura', 'n.periodo_id', 'n.created_at')
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

        public static function getEstudiantesConPromedioBajo($sedeId, $gradoId, $asignaturaId, $periodoActualId,  $periodoAnteriorId )
        {

            $promedioMinimo = 3;

            $query = "
                SELECT m.id,
                    CONCAT(e.apellidos, ' ', e.nombres) AS estudiante,
                    COALESCE(
                        (SELECT c2.nota
                         FROM calificaciones c2
                         WHERE c2.matricula_id = c.matricula_id
                           AND c2.asignatura_id = c.asignatura_id
                           AND c2.periodo_id = ?), 0) AS notaanterior,
                    c.nota AS notaperiodo,
                    ROUND(
                        (
                            COALESCE(
                                (SELECT c2.nota
                                 FROM calificaciones c2
                                 WHERE c2.matricula_id = c.matricula_id
                                   AND c2.asignatura_id = c.asignatura_id
                                   AND c2.periodo_id = ?), 0) + c.nota
                        ) / 2, 2) AS promedio
                FROM calificaciones c
                INNER JOIN matriculas m ON m.id = c.matricula_id
                INNER JOIN estudiantes e ON e.id = m.estudiante_id
                WHERE  m.grado_id = ?
                  AND c.asignatura_id = ?
                  AND c.periodo_id = ?
                  AND m.sede_id = ?
                HAVING promedio < ?;
            ";

            $results = DB::select($query, [
                $periodoAnteriorId,
                $periodoAnteriorId,
                $gradoId,
                $asignaturaId,
                $periodoActualId,
                $sedeId,
                $promedioMinimo
            ]);

            return $results;
        }





    }




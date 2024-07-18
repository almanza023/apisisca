<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Calificacion extends Model
{


    protected $table = 'calificaciones';
    protected $fillable = [ 'matricula_id','asignatura_id', 'periodo_id', 'logro_cognitivo', 'logro_afectivo',
     'nota', 'orden', 'estado'];


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


    public function carga()
    {
        return $this->belongsTo('App\Models\CargaAcademica', 'asignatura_id', );
    }

    public function logroCognitivo()
    {
        return $this->belongsTo('App\Models\LogroAcademico', 'logro_cognitivo', );
    }

    public function logroAfectivo()
    {
        return $this->belongsTo('App\Models\LogroAcademico', 'logro_afectivo', );
    }


    public function setNotaAttribute($value)
    {
        $this->attributes['nota'] = number_format($value, 2);
    }

    public static function byPeriodo($grado, $asignatura, $periodo){
        return Calificacion::where('grado_id', $grado)->where('asignatura_id', $asignatura)
        ->where('periodo_id', $periodo)->get();
    }

     public static function notaAnteriorEst($matricula, $asignatura, $periodo){

        $cal=Calificacion::where('matricula_id', $matricula)->where('asignatura_id', $asignatura)
        ->where('periodo_id', $periodo)->first();
        if(empty($cal)){
            return '';
        }else{
            if(!empty($cal) && $cal->nota<3){
            $niv=Nivelacion::getNivelacion($matricula, $asignatura, $periodo);
            if(empty($niv)){
                return $cal->nota;
            }else{
                return $niv->nota;
            }
        }else{
            return $cal->nota;
        }
        }
    }

    public static function calificacionesPeriodo($sede, $grado, $asignatura, $periodo){
        $data= DB::table('calificaciones as c')
        ->join('matriculas as m', 'm.id', '=', 'c.matricula_id')
        ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
        ->join('sedes as se', 'se.id', '=', 'm.sede_id')
        ->join('asignaturas as a', 'a.id', '=', 'c.asignatura_id')
        ->join('grados as g', 'g.id', '=', 'm.grado_id')
        ->select('c.id', 'm.id as matricula_id', 'c.nota',
         'c.logro_cognitivo', 'c.logro_afectivo', 'e.apellidos', 'e.nombres', 'se.nombre as sede',
         'c.estado','a.nombre as asignatura', 'g.descripcion as grado', 'se.id as sede_id', 'g.id as grado_id', 'a.id as asignatura_id',
          'c.periodo_id', 'c.created_at')
        ->where('m.grado_id', $grado)
        ->where('c.asignatura_id', $asignatura)
        ->where('c.periodo_id', $periodo)
        ->where('m.sede_id', $sede)
        ->orderBy('e.apellidos', 'asc')
        ->get();
        return $data;
    }

    public static function calificacionesBajo($sede, $grado, $asignatura, $periodo){
        return DB::table('calificaciones as c')
        ->join('matriculas as m', 'm.id', '=', 'c.matricula_id')
        ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
        ->join('asignaturas as a', 'a.id', '=', 'c.asignatura_id')
        ->join('grados as g', 'g.id', '=', 'm.grado_id')
        ->select('m.id', 'c.nota',
         'e.apellidos', 'e.nombres', 'a.nombre', 'g.descripcion')
        ->where('m.grado_id', $grado)
        ->where('c.asignatura_id', $asignatura)
        ->where('c.periodo_id', $periodo)
        ->where('m.sede_id', $sede)
        ->where('c.nota', '<', '3')
        ->orderBy('e.apellidos', 'asc')
        ->get();
    }

    public static function calificacionesPeriodoAnterior($sede, $grado, $asignatura, $periodo){
       $periodo=$periodo-1;
       $calificaciones=[];
        $calif= DB::table('calificaciones as c')
        ->join('matriculas as m', 'm.id', '=', 'c.matricula_id')
        ->where('m.sede_id', $sede)
        ->where('m.grado_id', $grado)
        ->where('c.asignatura_id', $asignatura)
        ->where('c.periodo_id', $periodo)->get(['c.matricula_id', 'c.nota']);
        foreach ($calif as $cal) {
           $niv=Nivelacion::getNivelacion($cal->matricula_id, $asignatura, $periodo);
           if(empty($niv)){
            $temp=[
                'matricula_id'=>$cal->matricula_id,
                'nota'=>$cal->nota
            ];
           }else{
            $temp=[
                'matricula_id'=>$cal->matricula_id,
                'nota'=>$niv->nota
            ];
           }
           array_push($calificaciones, $temp);

        }
        return collect($calificaciones);
    }

    public static function matriculaAsignatura($matricula, $asignatura, $periodo){
        return DB::table('calificaciones')
        ->where('matricula_id', $matricula)
        ->where('asignatura_id', $asignatura)
        ->where('periodo_id', $periodo)->get(['c.matricula_id', 'c.nota']);
    }

    public static function reportCalificaciones($matricula, $periodo, $ordenI, $ordenF){

        if(empty($ordenF)){
            return Calificacion::where('matricula_id', $matricula)
            ->where('periodo_id', $periodo)
            ->where('orden','>=', $ordenI)
            ->orderBy('orden', 'asc')
            ->get();
        }else{
            return Calificacion::where('matricula_id', $matricula)
            ->where('matricula_id', $matricula)
            ->where('periodo_id', $periodo)
            ->where('orden','>=', $ordenI)
            ->where('orden','<=', $ordenF)
            ->orderBy('orden', 'asc')
            ->get();
        }
     }

     public static function matriculaCalificaciones($matricula, $periodo){
        return Calificacion::where('matricula_id', $matricula)
        ->where('periodo_id', $periodo)->get();
    }

    public static function asignaturaCalificacion($grado, $periodo, $orden1, $orden2){
        return DB::table('calificaciones as c')
        ->join('matriculas as m', 'm.id', '=', 'c.matricula_id')
        ->join('asignaturas as a', 'a.id', '=', 'c.asignatura_id')
          ->join('carga_academicas as ca', 'a.id', '=', 'ca.asignatura_id')
        ->where('m.grado_id', $grado)
        ->where('c.periodo_id', $periodo)
        ->where('c.orden', '>', $orden1)
        ->where('c.orden', '<', $orden2)
        ->select('a.acronimo', 'ca.porcentaje', 'c.orden' )
        ->groupBy('a.acronimo', 'ca.porcentaje', 'c.orden')
        ->orderBy('c.orden', 'asc')
        ->distinct()
        ->get();
    }

    public static function codigoAsignatura($grado, $periodo){
        return DB::table('calificaciones as c')
        ->join('matriculas as m', 'm.id', '=', 'c.matricula_id')
        ->join('asignaturas as a', 'a.id', '=', 'c.asignatura_id')
        ->where('m.grado_id', $grado)
        ->where('c.periodo_id', $periodo)
        ->select('a.id', )
        ->orderBy('c.orden', 'asc')
        ->distinct()
        ->get();
    }

    public static function calificacionOrden($matricula, $periodo, $orden1, $orden2){
        return Calificacion::where('matricula_id', $matricula)
        ->where('periodo_id', $periodo)
        ->where('orden','>', $orden1)
        ->where('orden','<', $orden2)
        ->orderBy('orden', 'asc')
        ->get();
    }

    public static function calificacionesFinales($sede, $grado, $asignatura){
        $calificaciones=[];

         $calif= DB::table('calificaciones as c')
         ->join('matriculas as m', 'm.id', '=', 'c.matricula_id')
         ->join('estudiantes as es', 'es.id', '=', 'm.estudiante_id')
         ->where('m.sede_id', $sede)
         ->where('m.grado_id', $grado)
         ->where('c.asignatura_id', $asignatura)
         ->where('c.periodo_id', 4)
         ->select('c.matricula_id', 'c.asignatura_id', 'c.nota', 'es.nombres', 'es.apellidos')
         ->get();
         foreach ($calif as $cal) {
            $p1=0;
            $p2=0;
            $p3=0;
            $p4=0;
            $periodo1=Calificacion::notaAnteriorEst($cal->matricula_id, $cal->asignatura_id, 1);
            if(!empty($periodo1)){
                $p1=$periodo1->nota;
            }
            $periodo2=Calificacion::notaAnteriorEst($cal->matricula_id, $cal->asignatura_id, 2);
            if(!empty($periodo2)){
                $p2=$periodo2->nota;
            }
            $periodo3=Calificacion::notaAnteriorEst($cal->matricula_id, $cal->asignatura_id, 3);
            if(!empty($periodo3)){
                $p3=$periodo3->nota;
            }
            if(!empty($cal)){
                $p4=$cal->nota;
            }
            $nivFinal=Nivelacion::getNivelacion($cal->matricula_id, $cal->asignatura_id, 5);
            if(!empty($nivFinal)){
                $notaNiv=$nivFinal->nota;
            }else{
                $notaNiv='';
            }

            $promedio=round(($p1+$p2+$p3+$p4)/4, 1);

             $temp=[
                 'matricula_id'=>$cal->matricula_id,
                 'estudiante'=>$cal->apellidos.' '.$cal->nombres,
                 'periodo1'=>$p1,
                 'periodo2'=>$p2,
                 'periodo3'=>$p3,
                 'periodo4'=>$p4,
                 'promedio'=>$promedio,
                 'nivelacion'=>$notaNiv,
             ];
            array_push($calificaciones, $temp);

         }
         return ($calificaciones);
     }

     public static function notasFinalesEstudiante($matricula, $asignatura){
        $calificaciones=[];
         $calif= DB::table('calificaciones as c')
         ->join('matriculas as m', 'm.id', '=', 'c.matricula_id')
         ->join('estudiantes as es', 'es.id', '=', 'm.estudiante_id')
         ->where('c.matricula_id', $matricula)
         ->where('c.asignatura_id', $asignatura)
         ->where('c.periodo_id', 4)
         ->select('c.matricula_id', 'c.asignatura_id', 'c.nota')
         ->get();
         foreach ($calif as $cal) {

            $periodo1=Calificacion::notaAnteriorEst($cal->matricula_id, $cal->asignatura_id, 1);
            $periodo2=Calificacion::notaAnteriorEst($cal->matricula_id, $cal->asignatura_id, 1);
            $periodo3=Calificacion::notaAnteriorEst($cal->matricula_id, $cal->asignatura_id, 1);
            $nivFinal=Nivelacion::getNivelacion($cal->matricula_id, $cal->asignatura_id, 5);
            if(!empty($nivFinal)){
                $notaNiv=$nivFinal->nota;
            }else{
                $notaNiv='';
            }

            $promedio=round(($periodo1->nota+$periodo2->nota+$periodo3->nota+$cal->nota)/4, 1);

             $temp=[
                 'matricula_id'=>$cal->matricula_id,
                 'periodo1'=>$periodo1->nota,
                 'periodo2'=>$periodo2->nota,
                 'periodo3'=>$periodo3->nota,
                 'periodo4'=>$cal->nota,
                 'promedio'=>$promedio,
                 'nivelacion'=>$notaNiv,
             ];
            array_push($calificaciones, $temp);

         }
         return ($calificaciones);
     }

     public static function estudiantesCalificaciones($sede, $grado, $asignatura, $periodo){
        $resultado=[];
        $data= DB::table('matriculas as m')
        ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
        ->where('m.sede_id', $sede)
        ->where('m.grado_id', $grado)
        ->where('m.situacion', 'ACTIVO')
        ->select('m.id', 'm.estudiante_id', 'e.apellidos', 'e.nombres')
        ->orderBy('e.apellidos', 'asc')
        ->get();
        $periodo=$periodo-1;
        foreach ($data as $item) {
            $notapanterior=Calificacion::notaAnteriorEst($item->id, $asignatura, $periodo);

           $temp=[
            'id'=>$item->id,
            'estudiante_id'=>$item->estudiante_id,
            'apellidos'=>$item->apellidos,
            'nombres'=>$item->nombres,
            'notapanterior'=>$notapanterior."-".Calificacion::obtenerDesempeno($notapanterior),
           ];
           array_push($resultado, $temp);
        }
        return $resultado;
    }

    public static function estudiantesCalificacion($sede, $grado){

        $data= DB::table('matriculas as m')
        ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
        ->where('m.sede_id', $sede)
        ->where('m.grado_id', $grado)
        ->where('m.situacion', 'ACTIVO')
        ->select('m.id', 'm.estudiante_id', 'e.apellidos', 'e.nombres')
        ->orderBy('e.apellidos', 'asc')
        ->get();
        return $data;
    }

    public static function getNotasMatricula($periodo_id, $matricula_id){
        return DB::select('SELECT
        (SELECT id FROM asignaturas ga WHERE ga.id=car.asignatura_id) AS id,
        (SELECT nombre FROM asignaturas ga WHERE ga.id=car.asignatura_id) AS descripcion,
        (SELECT nota FROM calificaciones ca WHERE ca.matricula_id=m.id
        and ca.asignatura_id=car.asignatura_id AND ca.periodo_id=? LIMIT 1) AS nota
        FROM matriculas m
        INNER JOIN carga_academicas car ON (car.grado_id=m.grado_id AND car.sede_id=m.sede_id)
        WHERE m.id=? ORDER BY car.asignatura_id ASC', [$periodo_id, $matricula_id]);
    }

    private static  function obtenerDesempeno($nota){
        $des="";
        if($nota>=1 && $nota<3){
            $des="DESEMPEÑO BAJO";
        }
        else if($nota>=3 && $nota<4){
            $des="DESEMPEÑO BASICO";
        }
        else if($nota>=4 && $nota<4.49){
            $des="DESEMPEÑO ALTO";
        }
        else if($nota>=4.5 && $nota<=5){
            $des="DESEMPEÑO SUPERIOR";
        }
        return $des;
    }

    public static function getAreaMat($sede, $grado, $periodo){
        return DB::select("SELECT
            m.id AS matricula_id, concat(e.apellidos,' ', e.nombres) as estudiante, s.nombre as sede, g.descripcion as grado,
            SUM(CASE WHEN a.nombre = 'ESTADISTICA' THEN (c.nota) ELSE NULL END) as notaasig1,
            AVG(CASE WHEN a.nombre = 'ESTADISTICA' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) as asig1 ,
            SUM(CASE WHEN a.nombre = 'MATEMATICA' THEN (c.nota) ELSE NULL END) as notasig2,
            AVG(CASE WHEN a.nombre = 'MATEMATICA' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) AS asig2,
            SUM(CASE WHEN a.nombre IN ('ESTADISTICA', 'MATEMATICA') THEN c.nota * ca.porcentaje / 100 ELSE 0 END) AS definitiva
        FROM
            calificaciones c
        INNER JOIN
            matriculas m ON m.id = c.matricula_id
        INNER JOIN
            estudiantes e ON e.id = m.estudiante_id
        INNER JOIN
            sedes s ON s.id = m.sede_id
        INNER JOIN
            grados g ON g.id = m.grado_id
        INNER JOIN
            asignaturas a ON a.id = c.asignatura_id
        INNER JOIN
            carga_academicas ca ON ca.sede_id = m.sede_id
                                AND ca.grado_id = m.grado_id
                                AND ca.asignatura_id = c.asignatura_id
        WHERE
            m.sede_id = ?
            and m.grado_id = ?
            and c.periodo_id = ?
            AND ca.area = 'MAT'
        GROUP BY
            m.id", [ $sede,$grado, $periodo ]);
    }

    public static function getAreaLen($sede, $grado, $periodo){
        return DB::select("SELECT
            m.id AS matricula_id, concat(e.apellidos,' ', e.nombres) as estudiante, s.nombre as sede, g.descripcion as grado,
            SUM(CASE WHEN a.nombre = 'LECTURA CRITICA' THEN (c.nota) ELSE NULL END) as notaasig1,
            AVG(CASE WHEN a.nombre = 'LECTURA CRITICA' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) as asig1 ,
            SUM(CASE WHEN a.nombre = 'CASTELLANO' THEN (c.nota) ELSE NULL END) as notasig2,
            AVG(CASE WHEN a.nombre = 'CASTELLANO' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) AS asig2,
            SUM(CASE WHEN a.nombre IN ('LECTURA CRITICA', 'CASTELLANO') THEN c.nota * ca.porcentaje / 100 ELSE 0 END) AS definitiva
        FROM
            calificaciones c
        INNER JOIN
            matriculas m ON m.id = c.matricula_id
        INNER JOIN
            estudiantes e ON e.id = m.estudiante_id
        INNER JOIN
            sedes s ON s.id = m.sede_id
        INNER JOIN
            grados g ON g.id = m.grado_id
        INNER JOIN
            asignaturas a ON a.id = c.asignatura_id
        INNER JOIN
            carga_academicas ca ON ca.sede_id = m.sede_id
                                AND ca.grado_id = m.grado_id
                                AND ca.asignatura_id = c.asignatura_id
        WHERE
            m.sede_id = ?
            and m.grado_id = ?
            and c.periodo_id = ?
            AND ca.area = 'CAS'
        GROUP BY
            m.id", [ $sede,$grado, $periodo ]);
    }
    public static function getAreaNat($sede, $grado, $periodo){
        return DB::select("SELECT
            m.id AS matricula_id, concat(e.apellidos,' ', e.nombres) as estudiante, s.nombre as sede, g.descripcion as grado,
            SUM(CASE WHEN a.nombre = 'QUIMICA' THEN (c.nota) ELSE NULL END) as notaasig1,
            AVG(CASE WHEN a.nombre = 'QUIMICA' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) as asig1 ,
            SUM(CASE WHEN a.nombre = 'FISICA' THEN (c.nota) ELSE NULL END) as notasig2,
            AVG(CASE WHEN a.nombre = 'FISICA' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) AS asig2,
            SUM(CASE WHEN a.nombre = 'BIOLOGIA' THEN (c.nota) ELSE NULL END) as notasig3,
            AVG(CASE WHEN a.nombre = 'BIOLOGIA' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) AS asig3,
            SUM(CASE WHEN a.nombre IN ('QUIMICA', 'FISICA', 'BIOLOGIA') THEN c.nota * ca.porcentaje / 100 ELSE 0 END) AS definitiva
        FROM
            calificaciones c
        INNER JOIN
            matriculas m ON m.id = c.matricula_id
        INNER JOIN
            estudiantes e ON e.id = m.estudiante_id
        INNER JOIN
            sedes s ON s.id = m.sede_id
        INNER JOIN
            grados g ON g.id = m.grado_id
        INNER JOIN
            asignaturas a ON a.id = c.asignatura_id
        INNER JOIN
            carga_academicas ca ON ca.sede_id = m.sede_id
                                AND ca.grado_id = m.grado_id
                                AND ca.asignatura_id = c.asignatura_id
        WHERE
            m.sede_id = ?
            and m.grado_id = ?
            and c.periodo_id = ?
            AND ca.area = 'CNA'
        GROUP BY
            m.id", [ $sede,$grado, $periodo ]);
    }

    public static function getAreasSoc($sede, $grado, $periodo){
        return DB::select("SELECT
            m.id AS matricula_id, concat(e.apellidos,' ', e.nombres) as estudiante, s.nombre as sede, g.descripcion as grado,
            SUM(CASE WHEN a.nombre = 'CONSTITUCION NACIONAL' THEN (c.nota) ELSE NULL END) as notaasig1,
            AVG(CASE WHEN a.nombre = 'CONSTITUCION NACIONAL' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) as asig1 ,
            SUM(CASE WHEN a.nombre = 'CATEDRA DE PAZ' THEN (c.nota) ELSE NULL END) as notasig2,
            AVG(CASE WHEN a.nombre = 'CATEDRA DE PAZ' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) AS asig2,
            SUM(CASE WHEN a.nombre = 'SOCIALES INTEGRADAS' THEN (c.nota) ELSE NULL END) as notasig3,
            AVG(CASE WHEN a.nombre = 'SOCIALES INTEGRADAS' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) AS asig3,
            SUM(CASE WHEN a.nombre = 'CIENCIAS POLITICAS' THEN (c.nota) ELSE NULL END) as notasig4,
            AVG(CASE WHEN a.nombre = 'CIENCIAS POLITICAS' THEN (c.nota*(ca.porcentaje/100)) ELSE NULL END) AS asig4,
            SUM(CASE WHEN a.nombre IN ('CONSTITUCION NACIONAL', 'CATEDRA DE PAZ', 'SOCIALES INTEGRADAS', 'CIENCIAS POLITICAS') THEN c.nota * ca.porcentaje / 100 ELSE 0 END) AS definitiva
        FROM
            calificaciones c
        INNER JOIN
            matriculas m ON m.id = c.matricula_id
        INNER JOIN
            estudiantes e ON e.id = m.estudiante_id
        INNER JOIN
            sedes s ON s.id = m.sede_id
        INNER JOIN
            grados g ON g.id = m.grado_id
        INNER JOIN
            asignaturas a ON a.id = c.asignatura_id
        INNER JOIN
            carga_academicas ca ON ca.sede_id = m.sede_id
                                AND ca.grado_id = m.grado_id
                                AND ca.asignatura_id = c.asignatura_id
        WHERE
            m.sede_id = ?
            and m.grado_id = ?
            and c.periodo_id = ?
            AND ca.area = 'CSOC'
        GROUP BY
            m.id", [ $sede,$grado, $periodo ]);
    }

    public static function getTotalCalificadas($periodo_id,){
        return DB::select('SELECT distinct c.asignatura_id, m.grado_id  from calificaciones c
        inner join matriculas m on m.id=c.matricula_id
        inner join carga_academicas ca on  ca.asignatura_id=c.asignatura_id
        where periodo_id =?', [$periodo_id]);
    }










}

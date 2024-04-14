<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Matricula extends Model
{


    protected $table = 'matriculas';
    protected $fillable = [
        'estudiante_id', 'grado_id', 'sede_id', 'nivel', 'folio', 'periodo', 'repitente',
        'cambio_sede', 'situacion'
    ];

    public static function active()
    {
        return Matricula::where('estado', 1)->get();
    }

    public function estudiante()
    {
        return $this->belongsTo('App\Models\Estudiante', 'estudiante_id')->orderBy('apellidos', 'desc');
    }

    public function grado()
    {
        return $this->belongsTo('App\Models\Grado', 'grado_id');
    }

    public function sede()
    {
        return $this->belongsTo('App\Models\Sede', 'sede_id');
    }

    public static function estudiantesCalificacion($sede, $grado)
    {

        return DB::table('matriculas as m')
            ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
            ->select('m.id', 'm.estudiante_id', 'e.apellidos', 'e.nombres')
            ->where('m.sede_id', $sede)
            ->where('m.grado_id', $grado)
            ->where('m.situacion', 'ACTIVO')
            ->orderBy('e.apellidos', 'asc')
            ->get();
    }

    public static function getAll()
    {

        return Matricula::with(['sede', 'grado', 'estudiante'])
        ->where('estado','>=', '1')->get();

    }

    public static function listado($sede, $grado)
    {

        return DB::table('matriculas as m')
            ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
            ->join('sedes as s', 's.id', '=', 'm.sede_id')
            ->join('grados as g', 'g.id', '=', 'm.grado_id')
            ->select(
                'm.id',
                'm.estudiante_id',
                'e.apellidos',
                'e.nombres',
                'e.num_doc',
                'e.fecha_nac',
                'm.folio',
                'g.descripcion as grado',
                's.nombre as sede',
                'g.id as grado_id',
                's.id as sede_id'
            )
            ->where('m.sede_id', $sede)
            ->where('m.grado_id', $grado)
            ->where('m.situacion', 'ACTIVO')
            ->orderBy('e.apellidos', 'asc')
            ->get();
    }

    public static function listado2($sede, $grado)
    {

        return DB::table('matriculas as m')
            ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
            ->join('sedes as s', 's.id', '=', 'm.sede_id')
            ->join('grados as g', 'g.id', '=', 'm.grado_id')
            ->select(
                'm.id',
                'm.estudiante_id',
                'e.apellidos',
                'e.nombres',
                'e.num_doc',
                'm.folio',
                'g.descripcion as grado',
                's.nombre as sede'
            )
            ->where('m.sede_id', $sede)
            ->where('m.grado_id', $grado)
            ->whereNotExists(function ($query) {
                $query->select('id')
                    ->from('entregas')
                    ->whereRaw('entregas.matricula_id = m.id');
            })
            ->orderBy('e.apellidos', 'asc')
            ->get();
    }



    public static function byId($id)
    {
        return Matricula::with(['sede', 'grado', 'estudiante'])
        ->find($id);

    }

    public static function filtrar($sede, $grado){

        $objeto = Matricula::query()
        ->when($sede, fn($query, $sede_id) => $query->where('sede_id', $sede_id))
        ->when($grado, fn($query, $grado_id) => $query->where('grado_id', $grado_id))
        ->with(['sede', 'grado', 'estudiante'])
        ->get();
        return $objeto;

    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogroDisciplinario extends Model
{


    protected $table = 'logros_disciplinarios';
    protected $fillable = ['sede_id', 'grado_id', 'asignatura_id', 'periodo_id', 'descripcion'];



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

    public static function getAll(){

        return LogroDisciplinario::with(['sede', 'grado', 'asignatura', 'periodo'])
        ->where('estado','>=', '1')->get();
    }



    public static function filtrar($sede, $grado, $periodo){

        $objeto = LogroDisciplinario::query()
        ->when($sede, fn($query, $sede_id) => $query->where('sede_id', $sede_id))
        ->when($grado, fn($query, $grado_id) => $query->where('grado_id', $grado_id))
        ->when($periodo, fn($query, $periodo_id) => $query->where('periodo_id', $periodo_id))
        ->with(['sede', 'grado', 'periodo'])
        ->orderBy('id', 'desc')
        ->get();
        return $objeto;

    }

    public static function getDataFiltro($sede, $grado, $periodo){

        return LogroDisciplinario::where('grado_id', $grado)
        ->where('sede_id', $sede)
        ->where('periodo_id', $periodo)
        ->orderBy('periodo_id', 'asc')
        ->get();
    }



}

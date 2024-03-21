<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DireccionGrado extends Model
{

    protected $table = 'direcciones_grados';
    protected $fillable = [ 'sede_id','grado_id', 'docente_id', 'estado'];




    public function sede()
    {
        return $this->belongsTo('App\Models\Sede', 'sede_id', );
    }


    public function grado()
    {
        return $this->belongsTo('App\Models\Grado');
    }

    public function docente()
    {
        return $this->belongsTo('App\Models\Docente');
    }

    public static function getAll(){
        return DireccionGrado::with(['sede', 'grado', 'docente'])
        ->get();
    }

    public static function validarDuplicado($sede, $grado, $docente){
        return DireccionGrado::where('sede_id', $sede)
        ->where('grado_id', $grado)
        ->where('docente_id', $docente)
        ->where('estado', 1)
        ->get();
    }

    public static function getByDocente($docente, $sede){

            $data= DB::table('direcciones_grados as dr')
            ->join('grados as g', 'g.id', '=', 'dr.grado_id')
            ->where('dr.sede_id', $sede)
            ->where('dr.docente_id', $docente)
            ->where('dr.estado', '1')
            ->select('g.id', 'g.descripcion')
            ->orderBy('g.id', 'asc')
            ->get();
            return $data;

    }

    public static function getByGrado($grado, $sede){
        return DireccionGrado::where('grado_id', $grado)
        ->where('sede_id', $sede)
        ->where('estado', 1)
        ->first();
    }

}

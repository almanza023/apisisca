<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{

    protected $table = 'docentes';
    protected $fillable = [ 'sede_id','nombres', 'apellidos', 'documento', 'correo', 'telefono', 'escalafon',
     'especialidad', 'nivel', 'tipo', 'estado'];

     protected $hidden=['created_at','updated_at'];

     public function sede()
    {
        return $this->belongsTo('App\Models\Sede', 'sede_id', );
    }

    public static function getAll(){
        return Docente::
        with('sede')->
        orderBy('apellidos', 'asc')->get();
    }

    public static function getDocente(){
        return Docente::where('tipo', 1)->where('estado', 1)->get();
    }

    public static function getDocentePorSede($sede){
        return Docente::where('tipo', 1)
        ->where('sede_id', $sede)
        ->where('estado', 1)->get();
    }

    public static function active(){
        return Docente::where('estado', 1)->get();
    }


    public static function getByTipo($id){
        return Docente::
        where('tipo', $id)
        ->where('estado', 1)->get();
    }

}

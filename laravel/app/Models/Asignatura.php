<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignatura extends Model
{

    protected $table = 'asignaturas';
    protected $fillable = ['nombre', 'acronimo', 'tipo_asignatura_id'];
    protected $hidden=['created_at','updated_at'];


    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] =strtoupper($value);
    }

    public function setAcronimoAttribute($value)
    {
        $this->attributes['acronimo'] =strtoupper($value);
    }

    public function tipo()
    {
        return $this->belongsTo('App\Models\TipoAsignatura', 'tipo_asignatura_id', );
    }

    public static function getAll(){
        return Asignatura::with('tipo')->
        orderBy('nombre', 'asc')->get();
    }

    public static function active(){
        return Asignatura::where('estado', 1)->get();
    }

    public static function getByTipoAsignatura($id){
        return Asignatura::
        where('tipo_asignatura_id', $id)
        ->where('estado', 1)->get();
    }



}

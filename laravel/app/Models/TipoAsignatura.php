<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoAsignatura extends Model
{

    protected $table = 'tipo_asignaturas';
    protected $fillable = ['nombre', 'descripcion', 'preescolar'];
    protected $hidden=['created_at','updated_at'];

    public static function active(){
        return TipoAsignatura::where('estado', 1)->get();
    }

}

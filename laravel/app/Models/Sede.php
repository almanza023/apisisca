<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sede extends Model
{

    protected $table = 'sedes';
    protected $fillable = ['nombre', 'direccion', 'telefono', 'dane', 'estado'];
    protected $hidden=['created_at','updated_at'];

    public static function active(){
        return Sede::where('estado', 1)->get();
    }

    public static function getTotalMatriculaGrado(){
        return DB::select('SELECT g.descripcion,
        (select count(*) from matriculas m 
        where m.grado_id=g.id 
        ) as total
        from grados g');
    }

    public static function getTotalMatriculaSede(){
        return DB::select('SELECT s.nombre ,
        (select count(*) from matriculas m 
        where m.sede_id=s.id
        ) as total
        from sedes s');
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{

    protected $table = 'sedes';
    protected $fillable = ['nombre', 'direccion', 'telefono', 'dane', 'estado'];
    protected $hidden=['created_at','updated_at'];

    public static function active(){
        return Sede::where('estado', 1)->get();
    }
}

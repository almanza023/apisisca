<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{


    protected $table = 'periodos';
    protected $fillable = ['descripcion', 'numero', 'porcentaje'];
    protected $hidden=['created_at','updated_at'];

    public static function active(){
        return Periodo::where('estado', 1)->get();
    }

}

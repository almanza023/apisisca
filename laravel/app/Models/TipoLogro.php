<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoLogro extends Model
{


    protected $table = 'tipo_logros';
    protected $fillable = ['nombre', 'nivel'];
    protected $hidden=['created_at','updated_at'];

    public static function active(){
        return TipoLogro::where('estado', 1)
        ->whereBetween('id', [2,3])
        ->get();
    }

     public static function secundaria(){
        return TipoLogro::where('estado', 1)->where('id',2)->orWhere('id',3)->get();
    }
}

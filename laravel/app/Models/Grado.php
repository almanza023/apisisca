<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    protected $table = 'grados';
    protected $fillable = ['descripcion', 'numero'];
    protected $hidden=['created_at','updated_at'];

    public static function active(){
        return Grado::where('estado', 1)->orderBy('id', 'asc')->get();
    }

    public static function preescolarActive(){
        return Grado::where('estado', 1)->where('id', '<','3')->orderBy('id', 'asc')->get();
    }
    public static function secundariosActive(){
        return Grado::where('estado', 1)->where('id', '>=','3')
        ->where('id', '<=','13')->orderBy('id', 'asc')->get();
    }
}

<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Prefijo extends Model
{
   protected $table = 'prefijos';
    protected $fillable = ['prefijo', 'subfijo', 'estado'];




}

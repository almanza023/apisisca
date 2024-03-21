<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{


    protected $table = 'estudiantes';
    protected $fillable = [ 'nombres', 'apellidos', 'tipo_doc', 'num_doc', 'fecha_nac', 'lugar_nac',
     'estrato', 'direccion', 'eps', 'zona', 'tipo_sangre', 'desplazado', 'nombre_madre', 'nombre_padre'
     , 'nombre_acudiente', 'telefono_acudiente', 'estado'];


}

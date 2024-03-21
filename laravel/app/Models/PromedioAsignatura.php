<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromedioAsignatura extends Model
{

    protected $table = 'promedios_asignaturas';
    protected $fillable = ['sede_id', 'grado_id', 'asignatura_id', 'periodo_id',
     'valor', 'ganados', 'perdidos', 'bajo', 'basico', 'alto', 'superior'];



    public function sede()
    {
        return $this->belongsTo('App\Models\Sede', 'sede_id', );
    }

    public function grado()
    {
        return $this->belongsTo('App\Models\Grado', 'grado_id', );
    }


    public function asignatura()
    {
        return $this->belongsTo('App\Models\Asignatura', 'asignatura_id', );
    }

    public function periodo()
    {
        return $this->belongsTo('App\Models\Periodo', 'periodo_id', );
    }


}

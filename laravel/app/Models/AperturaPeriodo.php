<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AperturaPeriodo extends Model
{
    protected $table = 'aperturas_periodos';
    protected $fillable = ['fecha_apertura', 'fecha_cierre', 'periodo_id'];

    public static function getAll(){
        return AperturaPeriodo::with(['periodo'])
        ->get();
    }

    public static function active(){
        return AperturaPeriodo::where('estado', 1)->get();
    }

    public static function validarPeriodo($id){
        return AperturaPeriodo::where('estado', 1)
        ->where('periodo_id', $id)->get();
    }

    public static function getFecha($fecha){
        return AperturaPeriodo::where('estado', 1)
        ->where('fecha_apertura','>=', $fecha)
        ->where('fecha_cierre','<=', $fecha)
        ->orderBy('periodo_id','asc')
        ->get();
    }

    public static function getFechaPeriodo($fecha, $periodo){
        return AperturaPeriodo::where('estado', 1)
        ->where('fecha_apertura','<=', $fecha)
        ->where('fecha_cierre','<=', $fecha)
        ->where('periodo_id', $periodo)
        ->first();
    }


    public function periodo()
    {
        return $this->belongsTo('App\Models\Periodo');
    }

}

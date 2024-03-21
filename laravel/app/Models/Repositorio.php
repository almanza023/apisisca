<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repositorio extends Model
{
    public static function orden($asignatura, $grado){
        $ord='';
        switch ($asignatura) {
            case '6':
            $ord="1.1";
            break;
          case '7':
            $ord="1.2";
            break;
          case '8':
            $ord="1.3";
            break;
            case '9':
            $ord="1";
            break;
            case '10':
            $ord="2.2";
            break;
            case '11':
            $ord="2.1";
            break;
            case '12':
            if($grado>=8){
                $ord="5.9";
                break;
            }else {
                $ord="3";
                break;
            }
            break;
            case '13':
            $ord="2";
            break;
            case '14':
            $ord="4.1";
            break;
            case '15':
            $ord="4.2";
            break;
            case '16':
            $ord="4.3";
            break;
            case '17':
            $ord="4";
            break;
            case '18':
            $ord="5.4";
            break;
             case '19':
            $ord="5.3";
            break;
            case '20':
            $ord="5.2";
            break;
             case '21':
             $ord="5.1";
             break;
              case '22':
            $ord="6";
            break;
             case '23':
            $ord="5";
            break;
             case '24':
            $ord="7";
            break;
            case '25':
            $ord="8";
            break;
            case '26':
            $ord="9";
            break;
            case '27':
            $ord="10";
            break;
            case '28':
            $ord="11";
            break;
          default:
            $ord=0;
            break;
        }
        return $ord;
        }
}

<?php

use App\Http\Controllers\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'authenticate']);
Route::post('register', [AuthController::class, 'register']);
// Agrega aquí otras rutas relacionadas con la autenticación...

?>

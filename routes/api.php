<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TareaController;
use App\Http\Controllers\Api\TareaReporteController;

// Opcional: ver el usuario autenticado
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// -------------------------
// LOGIN (público) / LOGOUT (protegido)
// -------------------------
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // -------------------------
    // USUARIOS (CRUD protegido)
    // -------------------------
    Route::prefix('usuarios')->group(function () {
        Route::get('/listUsers', [UsuarioController::class, 'index']);
        Route::post('/addUser', [UsuarioController::class, 'store']);
        Route::get('/getUser/{id}', [UsuarioController::class, 'show']);
        Route::put('/updateUser/{id}', [UsuarioController::class, 'update']);
        Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']);
    });

    // -------------------------
    // TAREAS (protegidas)
    // -------------------------
    Route::get('/tareas', [TareaController::class, 'index']);
    Route::post('/tareas', [TareaController::class, 'store']);
    Route::get('/tareas/reporte', [TareaReporteController::class, 'export']);
    Route::get('/tareas/reporte-csv', [TareaReporteController::class, 'exportCsv']);
});

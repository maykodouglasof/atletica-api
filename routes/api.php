<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BilletController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\FoundAndLostController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\WarningController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CourseController;

Route::get('/ping', function () {
    return ['pong'=>true];
});

Route::get("/401", [AuthController::class, "unauthorized"])->name("login");

Route::post("/auth/login", [AuthController::class, "login"]);
Route::post("/auth/register", [AuthController::class, "register"]);
    
Route::middleware("auth:api")->group(function(){
    Route::post("/auth/validate", [AuthController::class, "validateToken"]);
    Route::post("/auth/logout", [AuthController::class, "logout"]);

    // Mural de Avisos
    Route::get("/walls", [WallController::class, "getAll"]);
    Route::post("/wall/{id}/like", [WallController::class, "like"]);

    // Documentos
    Route::get("/docs", [DocController::class, "getAll"]);

    // Livros de ocorrências
    Route::get("/warnings", [WarningController::class, "getMyWarnings"]);
    Route::post("/warning", [WarningController::class, "setWarning"]);
    Route::post("/warning/file", [WarningController::class, "addWarningFile"]);

    // Boletos
    Route::get("/billets", [BilletController::class, "getAll"]);

    // Achados e Perdidos
    Route::get("/foundandlost", [FoundAndLostController::class, "getAll"]);
    Route::post("/foundandlost", [FoundAndLostController::class, "insert"]);
    Route::put("/foundandlost/{id}", [FoundAndLostController::class, "update"]);

    // Unidade
    Route::get("/units", [UnitController::class, "getAll"]);
    Route::get("/unit/{id}", [UnitController::class, "getInfo"]);
    Route::post("/unit/{id}/addperson", [UnitController::class, "addPerson"]);
    Route::post("/unit/{id}/addvehicle", [UnitController::class, "addVehicle"]);
    Route::post("/unit/{id}/addpet", [UnitController::class, "addPet"]);
    Route::delete("/unit/{id}/removeperson", [UnitController::class, "removePerson"]);
    Route::delete("/unit/{id}/removevehicle", [UnitController::class, "removeVehicle"]);
    Route::delete("/unit/{id}/removepet", [UnitController::class, "removePet"]);

     // Area
     Route::get("/areas", [AreaController::class, "getAll"]);

    // Reservas
    Route::get("/reservations", [ReservationController::class, "getReservations"]);
    Route::post("/reservation/{id}", [ReservationController::class, "setReservation"]);

    Route::get("/reservation/{id}/disableddates", [ReservationController::class, "getDisabledDates"]);
    Route::get("/reservation/{id}/times", [ReservationController::class, "getTimes"]);
    
    Route::get("/myreservations", [ReservationController::class, "getMyReservations"]);
    Route::delete("/myreservation/{id}", [ReservationController::class, "removeReservation"]);
    
    // Usuário
    Route::get('/user/{id}', [UserController::class, 'getInfo']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::put('/user/{id}/newpassword', [UserController::class, 'newPassword']);

    Route::get("/users", [UserController::class, "getAll"]);

});

// Cursos
Route::get("/courses", [CourseController::class, "getAll"]);

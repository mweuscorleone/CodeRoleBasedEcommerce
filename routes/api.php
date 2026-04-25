<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('create/user', [UserController::class, 'createUser']);
Route::put('update/user/details/{id}', [UserController::class, 'updateUserDetails']);
Route::delete('delete/user/{id}', [UserController::class, 'deleteUser']);
Route::get('user/list', [UserController::class, 'usersList']);
Route::post('user/login', [AuthController::class, 'login']);
Route::put('reset/user/password', [AuthController::class, 'resetPassword']);
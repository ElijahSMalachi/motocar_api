<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Cars\CarController;
use App\Http\Controllers\Api\Documnets\DocumentController;
use App\Http\Controllers\Api\Posts\RidesController;
use App\Http\Controllers\Api\Users\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);


Route::apiResource('/cars', CarController::class);
Route::apiResource('/documents', DocumentController::class);
Route::prefix('/users')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('/', UsersController::class);
});

Route::prefix('/rides')->middleware('auth:sanctum')->group(function () {
    Route::post('/get', [RidesController::class, 'searchRides']);
    Route::post('/create', [RidesController::class, 'store']);
});
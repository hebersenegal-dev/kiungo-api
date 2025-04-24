<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\StructureController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('experiences', ExperienceController::class)->except(['create', 'edit']);
    Route::apiResource('structures', StructureController::class)->except(['create', 'edit']);
});


Route::get('countries', [CountryController::class, 'index']);
Route::get('countries/{country}', [CountryController::class, 'show']);

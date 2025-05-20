<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TemporaryProjectController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\StructureMergeController;
use App\Http\Controllers\Api\StructureController;
use App\Http\Controllers\Api\ProjectMergeController;
use App\Http\Controllers\Api\TemporaryStructureController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/me', [UserController::class, 'updateMe']);
    Route::post('/{id}/verify', [UserController::class, 'verifyUser']);
});

Route::prefix('projects')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ProjectController::class, 'index']);
    Route::get('/{id}', [ProjectController::class, 'show']);
    Route::post('/', [ProjectController::class, 'store']);
    Route::put('/{id}', [ProjectController::class, 'update']);
    Route::delete('/{id}', [ProjectController::class, 'destroy']);
    Route::post('/{id}/verify', [ProjectController::class, 'verify']);
    Route::get('/mine', [ProjectController::class, 'myProjects']);
});

Route::prefix('temporary-projects')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [TemporaryProjectController::class, 'index']);
    Route::get('/{id}', [TemporaryProjectController::class, 'show']);
    Route::post('/', [TemporaryProjectController::class, 'store']);
    Route::post('/{id}/verify', [TemporaryProjectController::class, 'verify']);
    Route::get('/structure/linked', [TemporaryProjectController::class, 'structureProjects']);
});

Route::prefix('experiences')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ExperienceController::class, 'index']);
    Route::get('/{id}', [ExperienceController::class, 'show']);
    Route::post('/', [ExperienceController::class, 'store']);
    Route::put('/{id}', [ExperienceController::class, 'update']);
    Route::delete('/{id}', [ExperienceController::class, 'destroy']);
    Route::post('/with-temporaries', [ExperienceController::class, 'storeWithTemporaries']);
    Route::get('/my', [ExperienceController::class, 'myExperiences']);

});



Route::prefix('structure-merges')->middleware('auth:sanctum')->group(function () {
    Route::post('/{tempStructureId}/verify', [StructureMergeController::class, 'verify']);
});

Route::prefix('project-merges')->middleware('auth:sanctum')->group(function () {
    Route::post('/{tempProjectId}/verify', [ProjectMergeController::class, 'verify']);
});

Route::prefix('temporary-structures')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [TemporaryStructureController::class, 'index']);
    Route::post('/', [TemporaryStructureController::class, 'store']);
    Route::get('/{id}', [TemporaryStructureController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/countries/{id}', [CountryController::class, 'update']);
    Route::delete('/countries/{id}', [CountryController::class, 'destroy']);
});
Route::get('/countries', [CountryController::class, 'index']);
Route::post('/countries', [CountryController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/structures', [StructureController::class, 'index']);
    Route::get('/structures/{id}', [StructureController::class, 'show']);
    Route::put('/structures/{id}', [StructureController::class, 'update']);
    Route::delete('/structures/{id}', [StructureController::class, 'destroy']); // optionnel
});

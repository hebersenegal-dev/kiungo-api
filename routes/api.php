<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\PasswordResetController;
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
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\AwardController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::redirect('/login', env('LOGIN_TO_FRONT'))->name('login');

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'reset']);

Route::get('/featured-professionals', [UserController::class, 'getProfessionals']);

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return response()->json(['message' => 'Email verified successfully']);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{id}/awards', [AwardController::class, 'index']);
    Route::post('/awards', [AwardController::class, 'store']);
    Route::put('/awards/{awardId}', [AwardController::class, 'update']);
    Route::delete('/awards/{awardId}', [AwardController::class, 'destroy']);
});

Route::prefix('projects')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ProjectController::class, 'index']);
    Route::get('/mine', [ProjectController::class, 'myProjects']);
    Route::get('/{id}', [ProjectController::class, 'show']);
    Route::post('/', [ProjectController::class, 'store']);
    Route::put('/{id}', [ProjectController::class, 'update']);
    Route::delete('/{id}', [ProjectController::class, 'destroy']);
    Route::post('/{id}/verify', [ProjectController::class, 'verify']);
});

Route::prefix('temporary-projects')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [TemporaryProjectController::class, 'index']);
    Route::get('/structure/linked', [TemporaryProjectController::class, 'structureProjects']);
    Route::get('/{id}', [TemporaryProjectController::class, 'show']);
    Route::post('/', [TemporaryProjectController::class, 'store']);
    Route::post('/{id}/verify', [TemporaryProjectController::class, 'verify']);
    Route::put('/{id}', [TemporaryProjectController::class, 'update']);
});

Route::prefix('experiences')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ExperienceController::class, 'index']);
    Route::get('/my', [ExperienceController::class, 'myExperiences']);
    Route::post('/with-temporaries', [ExperienceController::class, 'storeWithTemporaries']);
    Route::get('/{id}', [ExperienceController::class, 'show']);
    Route::post('/', [ExperienceController::class, 'store']);
    Route::put('/{id}', [ExperienceController::class, 'update']);
    Route::delete('/{id}', [ExperienceController::class, 'destroy']);

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
    Route::put('/{id}', [TemporaryStructureController::class, 'update']);
});

Route::get('/countries', [CountryController::class, 'index']);
Route::post('/countries', [CountryController::class, 'store']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/countries', [CountryController::class, 'store']);
    Route::put('/countries/{id}', [CountryController::class, 'update']);
    Route::delete('/countries/{id}', [CountryController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/structures', [StructureController::class, 'index']);
    Route::get('/structures/{id}', [StructureController::class, 'show']);
    Route::put('/structures/{id}', [StructureController::class, 'update']);
    Route::delete('/structures/{id}', [StructureController::class, 'destroy']); // optionnel
});



Route::prefix('search')->middleware('auth:sanctum')->group(function () {
    Route::get('/professionals', [SearchController::class, 'search']);
    Route::get('/suggestProfessional', [SearchController::class, 'autocompleteName']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications/unread', function (Request $request) {
        return $request->user()->unreadNotifications;
    });

    Route::post('/notifications/{id}/mark-as-read', function ($id, Request $request) {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['message' => 'Notification marked as read.']);
    });

    Route::post('/notifications/mark-all-read', function (Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    });
});

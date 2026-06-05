<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\AuthController;

// Self-healing database migration run on-demand
try {
    if (Schema::hasTable('users') && !Schema::hasColumn('users', 'api_token')) {
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_token', 80)->unique()->nullable()->default(null)->after('password');
        });
    }
    if (Schema::hasTable('skills') && !Schema::hasColumn('skills', 'user_id')) {
        Schema::table('skills', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });
    }
} catch (\Exception $e) {
    // Fail silently if DB is not set up yet
}

// Public Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/google', [AuthController::class, 'loginWithGoogle']);


// Protected routes (Requires custom token authentication middleware)
Route::middleware('token.auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/skills', [SkillController::class, 'index']);
    Route::post('/skills', [SkillController::class, 'store']);
    Route::get('/skills/{id}', [SkillController::class, 'show']);
    Route::put('/skills/{id}', [SkillController::class, 'update']);
    Route::delete('/skills/{id}', [SkillController::class, 'destroy']);
});


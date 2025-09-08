<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\UserAchievementController;
use App\Http\Controllers\Api\AdminUserAchievementController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [RegisterController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('purchases', [PurchaseController::class, 'store']);
    Route::get('users/{user}/achievements', [UserAchievementController::class, 'index']);
    Route::get('admin/users/achievements', [AdminUserAchievementController::class, 'index']);
});

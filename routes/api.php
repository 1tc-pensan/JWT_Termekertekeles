<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;

// ==========================================
// NYILVÁNOS VÉGPONTOK (Public - NO AUTH)
// ==========================================

// Auth routes (CSAK ezek nyilvánosak)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// ==========================================
// VÉDETT VÉGPONTOK (AUTH REQUIRED)
// ==========================================

Route::middleware('auth:api')->group(function () {
    
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Products - olvasás (autentikált felhasználók)
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    
    // Termékhez tartozó értékelések (autentikált felhasználók)
    Route::get('products/{id}/reviews', function ($id) {
        $product = \App\Models\Products::with('reviews.user')->findOrFail($id);
        return response()->json($product->reviews);
    });

    // Reviews - olvasás (autentikált felhasználók)
    Route::get('reviews', [ReviewController::class, 'index']);
    Route::get('reviews/{id}', [ReviewController::class, 'show']);

    // Reviews - írás/módosítás/törlés (autentikált felhasználók)
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{id}', [ReviewController::class, 'update']);
    Route::patch('reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);
    
    // Soft Delete műveletek Reviews-hoz (autentikált felhasználók)
    Route::get('reviews/trashed', [ReviewController::class, 'trashed']);
    Route::post('reviews/{id}/restore', [ReviewController::class, 'restore']);

    // Products - írás/módosítás/törlés (CSAK admin)
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::patch('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
    
    // Soft Delete műveletek Products-hoz (CSAK admin)
    Route::get('products/trashed', [ProductController::class, 'trashed']);
    Route::post('products/{id}/restore', [ProductController::class, 'restore']);
    Route::delete('products/{id}/force', [ProductController::class, 'forceDestroy']);

    // ==========================================
    // ADMIN VÉGPONTOK (Admin Only)
    // ==========================================
    
    Route::prefix('admin')->group(function () {
        Route::apiResource('users', AdminUserController::class);
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('reviews', AdminReviewController::class);
        
        // Soft Delete műveletek (csak admin)
        Route::get('products/trashed', [AdminProductController::class, 'trashed']);
        Route::post('products/{id}/restore', [AdminProductController::class, 'restore']);
        Route::delete('products/{id}/force', [AdminProductController::class, 'forceDestroy']);
        
        Route::get('reviews/trashed', [AdminReviewController::class, 'trashed']);
        Route::post('reviews/{id}/restore', [AdminReviewController::class, 'restore']);
        Route::delete('reviews/{id}/force', [AdminReviewController::class, 'forceDestroy']);
    });
});

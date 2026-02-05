<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| C端API路由
|
*/

Route::middleware('api')->prefix('v1')->group(function () {
    
    // 分类API
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/tree', [CategoryController::class, 'tree']);
        Route::get('/{slug}', [CategoryController::class, 'show']);
        Route::get('/{slug}/posts', [CategoryController::class, 'posts']);
    });

    // 文章API
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/popular', [PostController::class, 'popular']);
        Route::get('/latest', [PostController::class, 'latest']);
        Route::get('/search', [PostController::class, 'search']);
        Route::get('/archive', [PostController::class, 'archive']);
        Route::get('/archive/{year}/{month}', [PostController::class, 'archiveByDate']);
        Route::get('/{slug}', [PostController::class, 'show']);
    });
});
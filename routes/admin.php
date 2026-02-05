<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PostController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| 后台管理路由
|
*/

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // 仪表板
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // 分类管理
    Route::resource('categories', CategoryController::class);
    Route::post('categories/bulk-action', [CategoryController::class, 'bulkAction'])->name('categories.bulk-action');
    Route::get('categories-tree', [CategoryController::class, 'tree'])->name('categories.tree');

    // 文章管理
    Route::resource('posts', PostController::class);
    Route::post('posts/bulk-action', [PostController::class, 'bulkAction'])->name('posts.bulk-action');
    Route::patch('posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
    Route::patch('posts/{post}/unpublish', [PostController::class, 'unpublish'])->name('posts.unpublish');
    Route::post('posts/upload-image', [PostController::class, 'uploadImage'])->name('posts.upload-image');
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// C端页面路由 - 使用主题系统
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/posts', [HomeController::class, 'posts'])->name('posts.index');
Route::get('/post/{slug}', [HomeController::class, 'post'])->name('posts.show');
Route::get('/category/{slug}', [HomeController::class, 'category'])->name('categories.show');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/archive', [HomeController::class, 'archive'])->name('archive');
Route::get('/archive/{year}/{month}', [HomeController::class, 'archiveByDate'])->name('archive.date');

// 主题切换路由（仅开发环境）
if (app()->environment('local')) {
    Route::get('/theme/switch/{theme}', function ($theme) {
        app('theme')->setCurrentTheme($theme);
        return redirect()->back()->with('success', '主题已切换到: ' . $theme);
    })->name('theme.switch');
    
    Route::get('/theme/list', function () {
        $themes = app('theme')->getAvailableThemes();
        return view('theme-list', compact('themes'));
    })->name('theme.list');
}

// 引入后台路由
require __DIR__.'/admin.php';
<?php

use Illuminate\Support\Facades\Route;
use Plugins\Post\Http\Controllers\PostController;

Route::get('/post',[PostController::class,'index'])->name('post.index');

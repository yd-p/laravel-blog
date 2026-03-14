<?php

use Illuminate\Support\Facades\Route;
use Plugins\SocialShare\Http\Controllers\ShareController;

// 微信二维码生成（用于桌面端微信分享）
Route::get('/social-share/wechat-qr', [ShareController::class, 'wechatQr'])
    ->name('social-share.wechat-qr');

<?php

namespace Plugins\SocialShare\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SocialShareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/social-share.php', 'social-share');
    }

    public function boot(): void
    {
        // 注册视图（含插件内 Filament 页面视图）
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'social-share');

        // 注册 Blade 组件
        Blade::component('social-share::components.share-buttons', 'social-share');

        // 注册路由
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }
}

<?php

namespace Plugins\Post\Providers;

use Illuminate\Support\ServiceProvider;
use Plugins\Post\Hooks\PostViewLifecycleHooks;

class PostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 注册视图生命周期钩子
        $this->app->singleton(PostViewLifecycleHooks::class);
    }

    public function boot(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', strtolower('Post'));
        
        // 注册插件视图生命周期钩子
        $this->registerViewLifecycleHooks();
    }
    
    /**
     * 注册视图生命周期钩子
     */
    protected function registerViewLifecycleHooks(): void
    {
        $hooks = $this->app->make(PostViewLifecycleHooks::class);
        $hooks->register();
        $hooks->registerCustomHooks();
    }
}

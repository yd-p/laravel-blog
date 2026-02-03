<?php

namespace Plugins\Post\Providers;

use Illuminate\Support\ServiceProvider;

class PostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', strtolower('Post'));
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 注册钩子服务提供者
        $this->app->register(\App\Hooks\HookServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 注册钩子命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\HookCommand::class,
                \App\Console\Commands\MakeHookCommand::class,
            ]);
        }
    }
}

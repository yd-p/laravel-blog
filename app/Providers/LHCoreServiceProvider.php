<?php

namespace App\Providers;


use Composer\Autoload\ClassLoader;
use Illuminate\Support\ServiceProvider;
use App\Plugins\PluginsManager;

class LHCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 注册 PluginsManager 为单例
        $this->app->singleton(PluginsManager::class, function () {
            return new PluginsManager();
        });
    }

    public function boot(): void
    {
        $loader = include base_path('vendor/autoload.php');
        if (!$loader instanceof ClassLoader) {
            throw new \RuntimeException('Composer autoloader is not a ClassLoader instance.');
        }
        app(PluginsManager::class)->loadPlugins($loader, $this->app);

        $this->app->register(RouteServiceProvider::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'lh');
    }
}

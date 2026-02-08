<?php

namespace App\Providers;


use Composer\Autoload\ClassLoader;
use Illuminate\Support\ServiceProvider;
use App\Plugins\PluginsManager;
use App\Plugins\ThemeManager;

class LHCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
        app(ThemeManager::class)->boot();
        $this->app->register(\App\Providers\ThemeServiceProvider::class);
        $this->registerThemeFinder();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'lh');
    }

    protected function registerThemeFinder(): void
    {
        $this->app->singleton('theme.finder', function ($app) {
            $themeFinder = new ThemeViewFinder(
                $app['files'],
                $app['config']['view.paths']
            );
            $themeFinder->setHints(
                $this->app->make('view')->getFinder()->getHints()
            );
            return $themeFinder;
        });
        $this->app->make('theme.finder')->setActiveTheme('blog', 'test');
    }
}

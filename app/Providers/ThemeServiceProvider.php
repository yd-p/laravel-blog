<?php

namespace App\Providers;

use App\Services\ThemeService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ThemeService::class, function ($app) {
            return new ThemeService();
        });

        $this->app->alias(ThemeService::class, 'theme');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $theme = $this->app->make(ThemeService::class);
        
        // 注册主题视图命名空间
        $theme->registerViewNamespace();

        // 注册 Blade 指令
        $this->registerBladeDirectives();
        
        // 注册主题生命周期钩子
        $this->registerThemeLifecycleHooks();

        // 编译主题资源
        if ($this->app->environment('local')) {
            $theme->compileAssets();
        }
    }

    /**
     * 注册 Blade 指令
     */
    protected function registerBladeDirectives(): void
    {
        // @theme_asset('css/style.css')
        Blade::directive('theme_asset', function ($expression) {
            return "<?php echo app('theme')->asset({$expression}); ?>";
        });

        // @theme_color('primary')
        Blade::directive('theme_color', function ($expression) {
            return "<?php echo app('theme')->getColors()[{$expression}] ?? ''; ?>";
        });

        // @theme_config('key', 'default')
        Blade::directive('theme_config', function ($expression) {
            return "<?php echo app('theme')->getThemeConfig({$expression}); ?>";
        });
    }
    
    /**
     * 注册主题生命周期钩子
     */
    protected function registerThemeLifecycleHooks(): void
    {
        $lifecycle = $this->app->make(\App\Services\ViewLifecycleService::class);
        
        // 主题视图渲染前钩子 - 注入主题配置
        $lifecycle->registerLifecycleHook('view.composing', '*', function ($viewName, $data) {
            $theme = app('theme');
            return [
                'data' => [
                    'theme_name' => $theme->getCurrentTheme(),
                    'theme_config' => $theme->getThemeConfig(),
                    'theme_colors' => $theme->getColors(),
                ]
            ];
        }, 5);
    }
}

<?php

namespace App\Hooks\View;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use App\Hooks\HookManager;

/**
 * 视图钩子服务提供者
 */
class ViewHookServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册视图钩子管理器
        $this->app->singleton(ViewHookManager::class, function ($app) {
            return new ViewHookManager($app->make(HookManager::class));
        });

        // 注册别名
        $this->app->alias(ViewHookManager::class, 'view.hooks');
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 注册Blade指令
        $this->registerBladeDirectives();

        // 注册视图宏
        $this->registerViewMacros();

        // 注册全局视图数据
        $this->registerGlobalViewData();
    }

    /**
     * 注册Blade指令
     */
    protected function registerBladeDirectives(): void
    {
        // @hook 指令 - 在模板中执行钩子
        Blade::directive('hook', function ($expression) {
            return "<?php echo app('view.hooks')->executeInlineHook({$expression}); ?>";
        });

        // @hookData 指令 - 注入钩子数据
        Blade::directive('hookData', function ($expression) {
            return "<?php app('view.hooks')->injectInlineData({$expression}); ?>";
        });

        // @hookBefore 指令 - 渲染前钩子
        Blade::directive('hookBefore', function ($expression) {
            return "<?php app('view.hooks')->executeBeforeHook({$expression}); ?>";
        });

        // @hookAfter 指令 - 渲染后钩子
        Blade::directive('hookAfter', function ($expression) {
            return "<?php app('view.hooks')->executeAfterHook({$expression}); ?>";
        });

        // @ifHook 指令 - 条件钩子
        Blade::if('hook', function ($hookName) {
            return app('view.hooks')->hasHook($hookName);
        });
    }

    /**
     * 注册视图宏
     */
    protected function registerViewMacros(): void
    {
        // 视图钩子宏
        View::macro('withHook', function ($hookName, $data = []) {
            $viewHookManager = app(ViewHookManager::class);
            $result = $viewHookManager->executeDataInjection($hookName, $data);
            
            if (!empty($result)) {
                foreach ($result as $hookResults) {
                    foreach ($hookResults as $hookResult) {
                        if (isset($hookResult['injected_data'])) {
                            $this->with($hookResult['injected_data']);
                        }
                    }
                }
            }
            
            return $this;
        });

        // 主题切换宏
        View::macro('withTheme', function ($theme) {
            $this->with('__theme', $theme);
            return $this;
        });

        // 布局切换宏
        View::macro('withLayout', function ($layout) {
            $this->with('__layout', $layout);
            return $this;
        });
    }

    /**
     * 注册全局视图数据
     */
    protected function registerGlobalViewData(): void
    {
        View::composer('*', function ($view) {
            $viewHookManager = app(ViewHookManager::class);
            
            // 执行全局数据注入钩子
            $result = $viewHookManager->executeDataInjection('*');
            
            if (!empty($result)) {
                foreach ($result as $hookResults) {
                    foreach ($hookResults as $hookResult) {
                        if (isset($hookResult['injected_data'])) {
                            $view->with($hookResult['injected_data']);
                        }
                    }
                }
            }
        });
    }
}
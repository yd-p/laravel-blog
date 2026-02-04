<?php

namespace App\Hooks;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

/**
 * 钩子服务提供者
 */
class HookServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册钩子管理器为单例
        $this->app->singleton(HookManager::class, function (Application $app) {
            return new HookManager($app);
        });

        // 注册钩子管理器别名
        $this->app->alias(HookManager::class, 'hooks');

        // 注册钩子发现器
        $this->app->singleton(HookDiscovery::class, function (Application $app) {
            return new HookDiscovery($app->make(HookManager::class));
        });
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 发布配置文件
        $this->publishes([
            __DIR__ . '/../../config/hooks.php' => config_path('hooks.php'),
        ], 'hooks-config');

        // 发布迁移文件
        $this->publishes([
            __DIR__ . '/../../database/migrations/create_hooks_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_hooks_table.php'),
        ], 'hooks-migrations');

        // 自动发现并注册钩子
        if ($this->app->runningInConsole() || config('hooks.auto_discovery', true)) {
            $this->app->make(HookDiscovery::class)->discover();
        }

        // 注册内置钩子
        $this->registerBuiltinHooks();
    }

    /**
     * 注册内置钩子（仅注册钩子点，不包含业务逻辑）
     */
    protected function registerBuiltinHooks(): void
    {
        // 系统只提供钩子管理框架，不注册任何具体的业务逻辑
        // 所有业务逻辑都由用户在 app/Hooks/Custom/ 目录下自定义
        
        // 可以在这里注册一些系统级的钩子点定义（可选）
        // 但不包含任何业务逻辑实现
    }

    /**
     * 获取提供的服务
     */
    public function provides(): array
    {
        return [
            HookManager::class,
            HookDiscovery::class,
            'hooks'
        ];
    }
}
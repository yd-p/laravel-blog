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

        // 注册视图生命周期服务
         $this->app->singleton(\App\Services\ViewLifecycleService::class);
         $this->app->alias(\App\Services\ViewLifecycleService::class, 'view.lifecycle');

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 初始化视图生命周期服务
         $this->app->make(\App\Services\ViewLifecycleService::class)->initialize();

        // 注册视图生命周期 Blade 指令
         $this->registerViewLifecycleDirectives();

        // 注册钩子命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\HookCommand::class,
                \App\Console\Commands\MakeHookCommand::class,
                \App\Console\Commands\ThemeCompileCommand::class,
                \App\Console\Commands\ThemeListCommand::class,
                \App\Console\Commands\ThemeSwitchCommand::class,
            ]);
        }
    }

    /**
     * 注册视图生命周期 Blade 指令
     */
    protected function registerViewLifecycleDirectives(): void
    {
        // @lifecycle('before_render', 'posts.*')
        \Illuminate\Support\Facades\Blade::directive('lifecycle', function ($expression) {
            return "<?php app('view.lifecycle')->executeLifecycleHooks({$expression}); ?>";
        });

        // @hook('view.custom_hook', ['data' => 'value'])
        \Illuminate\Support\Facades\Blade::directive('hook', function ($expression) {
            return "<?php \App\Hooks\Facades\Hook::execute({$expression}); ?>";
        });

        // @plugin_hook('post.before_content')
        \Illuminate\Support\Facades\Blade::directive('plugin_hook', function ($expression) {
            return "<?php echo \App\Hooks\Facades\Hook::execute('plugin.' . {$expression}, get_defined_vars())->getContent(); ?>";
        });
    }
}

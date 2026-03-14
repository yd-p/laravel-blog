<?php

namespace App\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use BezhanSalleh\LanguageSwitch\Events\LocaleChanged;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(\App\Hooks\HookServiceProvider::class);
        $this->app->singleton(\App\Services\ViewLifecycleService::class);
        $this->app->alias(\App\Services\ViewLifecycleService::class, 'view.lifecycle');
    }

    public function boot(): void
    {
        $this->app->make(\App\Services\ViewLifecycleService::class)->initialize();

        // 动态注册数据库中的定时任务
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $this->loadScheduledJobsFromDatabase($schedule);
        });

        // 后台 UI 语言切换（中文 / 英文）
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['zh_CN', 'en'])
                ->labels(['zh_CN' => '中文', 'en' => 'English'])
                ->nativeLabel()                                    // 用语言自身文字显示
                ->renderHook('panels::user-menu.before')           // 显示在顶部用户菜单左侧
                ->userPreferredLocale(fn () => auth()->user()?->locale) // 读取用户偏好
                ->visible(insidePanels: true, outsidePanels: false);
        });
        // 切换语言时自动保存用户偏好
        \Illuminate\Support\Facades\Event::listen(
            LocaleChanged::class,
            \App\Listeners\SaveUserLocalePreference::class
        );

        $this->registerViewLifecycleDirectives();

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\HookCommand::class,
                \App\Console\Commands\MakeHookCommand::class,
                \App\Console\Commands\ThemeCompileCommand::class,
                \App\Console\Commands\ThemeListCommand::class,
                \App\Console\Commands\ThemeSwitchCommand::class,
            ]);
        }

        // -------------------------------------------------------
        // Filament 5.x 全局配置（基于 configureUsing() 技巧）
        // 参考: https://filamentphp.com/docs/tables/overview
        // -------------------------------------------------------

        // 1. 所有 Table 自动追加 created_at / updated_at（默认隐藏）
        Table::configureUsing(function (Table $table): void {
            $table->pushColumns([
                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
        });

        // 2. 所有 Section 默认 2 列布局
        Section::configureUsing(function (Section $section): void {
            $section->columns(2);
        });

        // 3. 所有 Select 默认 native(false)
        Select::configureUsing(function (Select $select): void {
            $select->native(false);
        });
    }

    /**
     * 从数据库动态加载定时任务
     */
    protected function loadScheduledJobsFromDatabase(\Illuminate\Console\Scheduling\Schedule $schedule): void
    {
        try {
            \App\Models\ScheduledJob::where('is_active', true)->each(function ($job) use ($schedule) {
                $event = $schedule->command($job->command)->cron($job->cron)->name($job->name);

                if ($job->without_overlapping) {
                    $event->withoutOverlapping();
                }
                if ($job->run_in_background) {
                    $event->runInBackground();
                }

                // 记录上次运行时间
                $event->after(function () use ($job) {
                    $job->update(['last_run_at' => now()]);
                });
            });
        } catch (\Throwable) {
            // 数据库未就绪时（如首次迁移）静默跳过
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

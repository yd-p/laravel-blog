<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // 每分钟处理队列任务
        $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();

        // 每天凌晨清理过期缓存
        $schedule->command('cache:prune-stale-tags')->daily();

        // 每天凌晨清理过期 session
        $schedule->command('session:gc')->daily();

        // 每小时自动同步定时任务监控列表（新增任务后自动注册）
        $schedule->command('schedule-monitor:sync')->hourly()->runInBackground();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

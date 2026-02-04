<?php

namespace App\Hooks\Middleware;

use Illuminate\Support\Facades\Log;

/**
 * 钩子性能监控中间件
 * 监控钩子执行性能，记录慢查询
 */
class PerformanceMiddleware
{
    protected float $threshold;

    public function __construct()
    {
        $this->threshold = config('hooks.performance_threshold', 100) / 1000; // 转换为秒
    }

    /**
     * 处理钩子性能监控
     * 
     * @param string $hookName 钩子名称
     * @param string $hookId 钩子ID
     * @param array $args 钩子参数
     * @return bool 是否继续执行钩子
     */
    public function __invoke(string $hookName, string $hookId, array $args): bool
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // 注册执行后的回调（这里简化处理，实际需要更复杂的实现）
        register_shutdown_function(function() use ($hookName, $hookId, $startTime, $startMemory) {
            $executionTime = microtime(true) - $startTime;
            $memoryUsage = memory_get_usage(true) - $startMemory;

            // 如果执行时间超过阈值，记录警告
            if ($executionTime > $this->threshold) {
                Log::warning('钩子执行缓慢', [
                    'hook_name' => $hookName,
                    'hook_id' => $hookId,
                    'execution_time' => $executionTime,
                    'memory_usage' => $memoryUsage,
                    'threshold' => $this->threshold,
                ]);
            }

            // 记录性能数据
            if (config('hooks.performance_monitoring', false)) {
                Log::info('钩子性能数据', [
                    'hook_name' => $hookName,
                    'hook_id' => $hookId,
                    'execution_time' => $executionTime,
                    'memory_usage' => $memoryUsage,
                ]);
            }
        });

        return true; // 继续执行
    }
}
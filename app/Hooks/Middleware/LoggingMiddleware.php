<?php

namespace App\Hooks\Middleware;

use Illuminate\Support\Facades\Log;

/**
 * 钩子日志中间件
 * 记录钩子的执行日志
 */
class LoggingMiddleware
{
    /**
     * 处理钩子执行前的日志记录
     * 
     * @param string $hookName 钩子名称
     * @param string $hookId 钩子ID
     * @param array $args 钩子参数
     * @return bool 是否继续执行钩子
     */
    public function __invoke(string $hookName, string $hookId, array $args): bool
    {
        // 记录钩子执行开始
        Log::debug('钩子执行开始', [
            'hook_name' => $hookName,
            'hook_id' => $hookId,
            'args_count' => count($args),
            'timestamp' => now(),
            'memory_usage' => memory_get_usage(true),
        ]);

        return true; // 继续执行
    }
}
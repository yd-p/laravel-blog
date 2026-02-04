<?php

namespace App\Hooks\Templates;

/**
 * 闭包钩子模板
 * 
 * 展示如何使用闭包注册钩子
 */
class ClosureHookTemplate
{
    /**
     * 获取钩子注册示例
     * 
     * @return array
     */
    public static function getExamples(): array
    {
        return [
            // 简单闭包钩子
            'simple_closure' => function ($data) {
                // TODO: 实现你的逻辑
                logger()->info('Simple closure hook executed', ['data' => $data]);
                return ['status' => 'success'];
            },

            // 带参数验证的闭包钩子
            'validated_closure' => function (...$args) {
                // 参数验证
                if (empty($args)) {
                    throw new \InvalidArgumentException('参数不能为空');
                }

                // TODO: 实现你的逻辑
                return ['processed_args' => count($args)];
            },

            // 异步处理闭包钩子
            'async_closure' => function ($job) {
                // TODO: 将任务放入队列异步处理
                // dispatch(new ProcessHookJob($job));
                return ['queued' => true];
            },

            // 条件执行闭包钩子
            'conditional_closure' => function ($user, $action) {
                // 条件判断
                if (!$user || !$user->isActive()) {
                    return ['skipped' => true, 'reason' => 'User not active'];
                }

                // TODO: 实现你的逻辑
                return ['executed' => true, 'user_id' => $user->id];
            },
        ];
    }

    /**
     * 注册示例钩子
     * 
     * 在服务提供者中调用此方法来注册示例钩子
     */
    public static function registerExamples(): void
    {
        $hookManager = app(\App\Hooks\HookManager::class);
        
        foreach (self::getExamples() as $name => $callback) {
            $hookManager->register("example.{$name}", $callback, 10, 'example');
        }
    }
}
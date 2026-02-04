<?php

namespace App\Hooks\Templates;

use App\Hooks\AbstractHook;

/**
 * 钩子模板类
 * 
 * 复制此模板到 app/Hooks/Custom/ 目录下，并根据需要修改
 * 
 * @hook your.hook.name
 * @priority 10
 * @group your_group
 */
class HookTemplate extends AbstractHook
{
    protected string $description = '钩子描述';
    protected int $priority = 10;

    /**
     * 处理钩子逻辑
     * 
     * @param mixed ...$args 钩子参数
     * @return mixed 处理结果
     */
    public function handle(...$args)
    {
        // TODO: 在这里实现你的业务逻辑
        
        // 示例：获取参数
        // [$param1, $param2] = $args;
        
        // 示例：记录日志
        // logger()->info('钩子执行', ['args' => $args]);
        
        // 示例：返回结果
        return [
            'status' => 'success',
            'message' => '钩子执行成功',
            'data' => null,
            'timestamp' => now()
        ];
    }

    /**
     * 参数验证（可选）
     * 
     * @param mixed ...$args 钩子参数
     * @return bool 验证是否通过
     */
    protected function validateArgs(...$args): bool
    {
        // TODO: 在这里实现参数验证逻辑
        
        // 示例：检查参数数量
        // return count($args) >= 1;
        
        // 示例：检查参数类型
        // return isset($args[0]) && is_object($args[0]);
        
        return true;
    }

    /**
     * 钩子执行前的准备工作（可选）
     * 
     * @param mixed ...$args 钩子参数
     */
    protected function before(...$args): void
    {
        // TODO: 在这里实现执行前的准备工作
        
        // 示例：记录开始时间
        // $this->addMetadata('start_time', microtime(true));
    }

    /**
     * 钩子执行后的清理工作（可选）
     * 
     * @param mixed $result 执行结果
     * @param mixed ...$args 钩子参数
     */
    protected function after($result, ...$args): void
    {
        // TODO: 在这里实现执行后的清理工作
        
        // 示例：记录执行时间
        // $startTime = $this->getMetadataValue('start_time');
        // if ($startTime) {
        //     $executionTime = microtime(true) - $startTime;
        //     logger()->debug('钩子执行时间', ['time' => $executionTime]);
        // }
    }

    /**
     * 异常处理（可选）
     * 
     * @param \Throwable $e 异常对象
     * @param mixed ...$args 钩子参数
     */
    protected function handleException(\Throwable $e, ...$args): void
    {
        // TODO: 在这里实现异常处理逻辑
        
        // 示例：记录错误日志
        logger()->error('钩子执行异常', [
            'hook' => static::class,
            'error' => $e->getMessage(),
            'args' => $args
        ]);
        
        // 重新抛出异常或返回默认值
        throw $e;
    }
}
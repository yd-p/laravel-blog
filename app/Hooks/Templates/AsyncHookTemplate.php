<?php

namespace App\Hooks\Templates;

use App\Hooks\AbstractHook;
use Illuminate\Support\Facades\Queue;

/**
 * 异步钩子模板
 * 
 * 适用于需要异步处理的场景，避免阻塞主流程
 * 
 * @hook async.hook.name
 * @priority 10
 * @group async
 */
class AsyncHookTemplate extends AbstractHook
{
    protected string $description = '异步处理钩子模板';
    protected int $priority = 10;

    /**
     * 处理异步钩子逻辑
     * 
     * @param mixed ...$args 钩子参数
     * @return mixed 处理结果
     */
    public function handle(...$args)
    {
        // 获取参数
        [$data, $options] = $this->extractArgs($args);

        // 检查是否需要异步处理
        if ($this->shouldProcessAsync($data, $options)) {
            return $this->dispatchAsyncJob($data, $options);
        }

        // 同步处理
        return $this->processSynchronously($data, $options);
    }

    /**
     * 提取和验证参数
     */
    protected function extractArgs(array $args): array
    {
        $data = $args[0] ?? null;
        $options = $args[1] ?? [];

        if (!$data) {
            throw new \InvalidArgumentException('数据参数不能为空');
        }

        return [$data, $options];
    }

    /**
     * 判断是否需要异步处理
     */
    protected function shouldProcessAsync($data, array $options): bool
    {
        // TODO: 实现你的异步判断逻辑
        
        // 示例判断条件：
        // - 数据量大小
        // - 处理复杂度
        // - 用户配置
        // - 系统负载
        
        return $options['async'] ?? false;
    }

    /**
     * 分发异步任务
     */
    protected function dispatchAsyncJob($data, array $options): array
    {
        // TODO: 实现你的异步任务分发逻辑
        
        // 示例：
        // $job = new ProcessHookDataJob($data, $options);
        // $jobId = Queue::push($job);
        
        $jobId = 'job_' . uniqid();
        
        return [
            'status' => 'queued',
            'job_id' => $jobId,
            'message' => '任务已加入队列',
            'async' => true,
            'timestamp' => now()
        ];
    }

    /**
     * 同步处理
     */
    protected function processSynchronously($data, array $options): array
    {
        // TODO: 实现你的同步处理逻辑
        
        try {
            // 处理数据
            $result = $this->processData($data, $options);
            
            return [
                'status' => 'completed',
                'result' => $result,
                'message' => '同步处理完成',
                'async' => false,
                'timestamp' => now()
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'message' => '同步处理失败',
                'async' => false,
                'timestamp' => now()
            ];
        }
    }

    /**
     * 实际的数据处理逻辑
     */
    protected function processData($data, array $options)
    {
        // TODO: 在这里实现你的具体数据处理逻辑
        
        // 示例处理：
        // - 数据转换
        // - 外部API调用
        // - 文件处理
        // - 复杂计算
        
        return ['processed' => true, 'data' => $data];
    }

    /**
     * 参数验证
     */
    protected function validateArgs(...$args): bool
    {
        return count($args) >= 1;
    }

    /**
     * 执行前准备
     */
    protected function before(...$args): void
    {
        // 记录开始时间
        $this->addMetadata('start_time', microtime(true));
        
        // 记录内存使用
        $this->addMetadata('start_memory', memory_get_usage(true));
    }

    /**
     * 执行后清理
     */
    protected function after($result, ...$args): void
    {
        $executionTime = microtime(true) - $this->getMetadataValue('start_time', 0);
        $memoryUsage = memory_get_usage(true) - $this->getMetadataValue('start_memory', 0);
        
        // 记录性能数据
        logger()->debug('异步钩子执行完成', [
            'hook' => static::class,
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'result_status' => $result['status'] ?? 'unknown'
        ]);
    }
}
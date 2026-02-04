<?php

namespace App\Hooks\Templates;

use App\Hooks\AbstractHook;

/**
 * 批量处理钩子模板
 * 
 * 适用于需要批量处理数据的场景
 * 
 * @hook batch.processing.hook
 * @priority 10
 * @group batch
 */
class BatchProcessingHookTemplate extends AbstractHook
{
    protected string $description = '批量处理钩子模板';
    protected int $priority = 10;

    // 批量处理配置
    protected int $batchSize = 100;
    protected int $maxRetries = 3;
    protected int $retryDelay = 5; // 秒

    /**
     * 处理批量钩子逻辑
     * 
     * @param mixed ...$args 钩子参数
     * @return mixed 处理结果
     */
    public function handle(...$args)
    {
        [$items, $options] = $this->extractArgs($args);

        // 应用配置选项
        $this->applyOptions($options);

        // 验证数据
        $this->validateItems($items);

        // 分批处理
        return $this->processBatches($items, $options);
    }

    /**
     * 提取参数
     */
    protected function extractArgs(array $args): array
    {
        $items = $args[0] ?? [];
        $options = $args[1] ?? [];

        if (!is_array($items) && !is_iterable($items)) {
            throw new \InvalidArgumentException('第一个参数必须是数组或可迭代对象');
        }

        return [$items, $options];
    }

    /**
     * 应用配置选项
     */
    protected function applyOptions(array $options): void
    {
        $this->batchSize = $options['batch_size'] ?? $this->batchSize;
        $this->maxRetries = $options['max_retries'] ?? $this->maxRetries;
        $this->retryDelay = $options['retry_delay'] ?? $this->retryDelay;
    }

    /**
     * 验证数据项
     */
    protected function validateItems($items): void
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('数据项不能为空');
        }

        // TODO: 添加更多验证逻辑
        // - 数据格式验证
        // - 数据完整性检查
        // - 业务规则验证
    }

    /**
     * 分批处理
     */
    protected function processBatches($items, array $options): array
    {
        $batches = $this->createBatches($items);
        $results = [];
        $totalProcessed = 0;
        $totalFailed = 0;
        $errors = [];

        foreach ($batches as $batchIndex => $batch) {
            $batchResult = $this->processBatch($batch, $batchIndex, $options);
            
            $results[] = $batchResult;
            $totalProcessed += $batchResult['processed'];
            $totalFailed += $batchResult['failed'];
            
            if (!empty($batchResult['errors'])) {
                $errors = array_merge($errors, $batchResult['errors']);
            }

            // 批次间延迟（可选）
            if (isset($options['batch_delay']) && $options['batch_delay'] > 0) {
                sleep($options['batch_delay']);
            }
        }

        return [
            'status' => $totalFailed === 0 ? 'success' : 'partial_success',
            'total_items' => count($items),
            'total_batches' => count($batches),
            'total_processed' => $totalProcessed,
            'total_failed' => $totalFailed,
            'batch_results' => $results,
            'errors' => $errors,
            'timestamp' => now()
        ];
    }

    /**
     * 创建批次
     */
    protected function createBatches($items): array
    {
        $batches = [];
        $currentBatch = [];
        $count = 0;

        foreach ($items as $item) {
            $currentBatch[] = $item;
            $count++;

            if ($count >= $this->batchSize) {
                $batches[] = $currentBatch;
                $currentBatch = [];
                $count = 0;
            }
        }

        // 添加最后一个批次（如果有剩余项）
        if (!empty($currentBatch)) {
            $batches[] = $currentBatch;
        }

        return $batches;
    }

    /**
     * 处理单个批次
     */
    protected function processBatch(array $batch, int $batchIndex, array $options): array
    {
        $processed = 0;
        $failed = 0;
        $errors = [];
        $retryCount = 0;

        while ($retryCount <= $this->maxRetries) {
            try {
                $batchResult = $this->processBatchItems($batch, $batchIndex, $options);
                
                return [
                    'batch_index' => $batchIndex,
                    'batch_size' => count($batch),
                    'processed' => count($batch),
                    'failed' => 0,
                    'errors' => [],
                    'retry_count' => $retryCount,
                    'result' => $batchResult,
                    'timestamp' => now()
                ];

            } catch (\Exception $e) {
                $retryCount++;
                
                if ($retryCount > $this->maxRetries) {
                    return [
                        'batch_index' => $batchIndex,
                        'batch_size' => count($batch),
                        'processed' => 0,
                        'failed' => count($batch),
                        'errors' => [
                            [
                                'batch_index' => $batchIndex,
                                'error' => $e->getMessage(),
                                'retry_count' => $retryCount - 1
                            ]
                        ],
                        'retry_count' => $retryCount - 1,
                        'timestamp' => now()
                    ];
                }

                // 重试延迟
                if ($this->retryDelay > 0) {
                    sleep($this->retryDelay);
                }
            }
        }
    }

    /**
     * 处理批次中的数据项
     */
    protected function processBatchItems(array $batch, int $batchIndex, array $options): array
    {
        // TODO: 在这里实现你的批量处理逻辑
        
        $results = [];
        
        foreach ($batch as $index => $item) {
            try {
                $result = $this->processItem($item, $batchIndex, $index, $options);
                $results[] = $result;
                
            } catch (\Exception $e) {
                // 单项处理失败，记录错误但继续处理其他项
                $results[] = [
                    'item_index' => $index,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * 处理单个数据项
     */
    protected function processItem($item, int $batchIndex, int $itemIndex, array $options)
    {
        // TODO: 在这里实现单个数据项的处理逻辑
        
        // 示例处理：
        // - 数据转换
        // - 数据库操作
        // - API调用
        // - 文件处理
        // - 业务逻辑处理
        
        return [
            'item_index' => $itemIndex,
            'batch_index' => $batchIndex,
            'status' => 'processed',
            'result' => 'Item processed successfully',
            'timestamp' => now()
        ];
    }

    /**
     * 设置批次大小
     */
    public function setBatchSize(int $size): self
    {
        $this->batchSize = max(1, $size);
        return $this;
    }

    /**
     * 设置最大重试次数
     */
    public function setMaxRetries(int $retries): self
    {
        $this->maxRetries = max(0, $retries);
        return $this;
    }

    /**
     * 设置重试延迟
     */
    public function setRetryDelay(int $delay): self
    {
        $this->retryDelay = max(0, $delay);
        return $this;
    }

    /**
     * 获取批次配置
     */
    public function getBatchConfig(): array
    {
        return [
            'batch_size' => $this->batchSize,
            'max_retries' => $this->maxRetries,
            'retry_delay' => $this->retryDelay
        ];
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
        $this->addMetadata('start_time', microtime(true));
        $this->addMetadata('start_memory', memory_get_usage(true));
        
        logger()->info('开始批量处理', [
            'hook' => static::class,
            'batch_size' => $this->batchSize,
            'max_retries' => $this->maxRetries
        ]);
    }

    /**
     * 执行后清理
     */
    protected function after($result, ...$args): void
    {
        $executionTime = microtime(true) - $this->getMetadataValue('start_time', 0);
        $memoryUsage = memory_get_usage(true) - $this->getMetadataValue('start_memory', 0);
        
        logger()->info('批量处理完成', [
            'hook' => static::class,
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'total_processed' => $result['total_processed'] ?? 0,
            'total_failed' => $result['total_failed'] ?? 0
        ]);
    }
}
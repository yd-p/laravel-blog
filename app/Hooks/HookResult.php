<?php

namespace App\Hooks;

use Illuminate\Support\Collection;

/**
 * 钩子执行结果类
 */
class HookResult
{
    protected string $hookName;
    protected array $results;
    protected array $errors;
    protected int $executedCount;
    protected float $executionTime;

    public function __construct(
        string $hookName,
        array $results = [],
        array $errors = [],
        int $executedCount = 0,
        float $executionTime = 0.0
    ) {
        $this->hookName = $hookName;
        $this->results = $results;
        $this->errors = $errors;
        $this->executedCount = $executedCount;
        $this->executionTime = $executionTime;
    }

    /**
     * 获取钩子名称
     */
    public function getHookName(): string
    {
        return $this->hookName;
    }

    /**
     * 获取所有结果
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * 获取结果集合
     */
    public function getResultsCollection(): Collection
    {
        return collect($this->results);
    }

    /**
     * 获取第一个结果
     */
    public function getFirstResult()
    {
        return !empty($this->results) ? reset($this->results) : null;
    }

    /**
     * 获取最后一个结果
     */
    public function getLastResult()
    {
        return !empty($this->results) ? end($this->results) : null;
    }

    /**
     * 获取所有错误
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 检查是否有错误
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * 获取执行数量
     */
    public function getExecutedCount(): int
    {
        return $this->executedCount;
    }

    /**
     * 获取执行时间（秒）
     */
    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    /**
     * 检查是否成功执行
     */
    public function isSuccessful(): bool
    {
        return $this->executedCount > 0 && empty($this->errors);
    }

    /**
     * 检查是否部分成功
     */
    public function isPartiallySuccessful(): bool
    {
        return $this->executedCount > 0 && !empty($this->errors);
    }

    /**
     * 获取成功率
     */
    public function getSuccessRate(): float
    {
        if ($this->executedCount === 0) {
            return 0.0;
        }
        
        $successCount = $this->executedCount - count($this->errors);
        return ($successCount / $this->executedCount) * 100;
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'hook_name' => $this->hookName,
            'results' => $this->results,
            'errors' => $this->errors,
            'executed_count' => $this->executedCount,
            'execution_time' => $this->executionTime,
            'is_successful' => $this->isSuccessful(),
            'success_rate' => $this->getSuccessRate()
        ];
    }

    /**
     * 转换为JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * 魔术方法：转换为字符串
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
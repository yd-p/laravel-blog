<?php

namespace App\Hooks;

use App\Hooks\Contracts\HookInterface;

/**
 * 抽象钩子基类
 * 提供钩子的基础实现
 */
abstract class AbstractHook implements HookInterface
{
    protected string $description = '';
    protected int $priority = 10;
    protected bool $enabled = true;
    protected array $metadata = [];

    /**
     * 抽象方法：子类必须实现具体的处理逻辑
     */
    abstract public function handle(...$args);

    /**
     * 获取钩子描述
     */
    public function getDescription(): string
    {
        return $this->description ?: static::class;
    }

    /**
     * 设置钩子描述
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * 获取钩子优先级
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * 设置钩子优先级
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * 检查钩子是否启用
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * 启用/禁用钩子
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * 获取元数据
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * 设置元数据
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * 添加元数据
     */
    public function addMetadata(string $key, $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    /**
     * 获取特定元数据
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * 钩子执行前的准备工作
     * 子类可以重写此方法
     */
    protected function before(...$args): void
    {
        // 默认不做任何操作
    }

    /**
     * 钩子执行后的清理工作
     * 子类可以重写此方法
     */
    protected function after($result, ...$args): void
    {
        // 默认不做任何操作
    }

    /**
     * 验证参数
     * 子类可以重写此方法进行参数验证
     */
    protected function validateArgs(...$args): bool
    {
        return true;
    }

    /**
     * 处理异常
     * 子类可以重写此方法自定义异常处理
     */
    protected function handleException(\Throwable $e, ...$args): void
    {
        throw $e;
    }

    /**
     * 最终的执行方法（模板方法模式）
     */
    final public function execute(...$args)
    {
        if (!$this->enabled) {
            return null;
        }

        if (!$this->validateArgs(...$args)) {
            throw new \InvalidArgumentException('钩子参数验证失败');
        }

        try {
            $this->before(...$args);
            $result = $this->handle(...$args);
            $this->after($result, ...$args);
            return $result;
        } catch (\Throwable $e) {
            $this->handleException($e, ...$args);
        }
    }
}
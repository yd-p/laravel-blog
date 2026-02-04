<?php

namespace App\Hooks\Exceptions;

use Exception;

/**
 * 钩子异常类
 */
class HookException extends Exception
{
    protected string $hookName;
    protected ?string $hookId;

    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
        string $hookName = '',
        ?string $hookId = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->hookName = $hookName;
        $this->hookId = $hookId;
    }

    /**
     * 获取钩子名称
     */
    public function getHookName(): string
    {
        return $this->hookName;
    }

    /**
     * 获取钩子ID
     */
    public function getHookId(): ?string
    {
        return $this->hookId;
    }

    /**
     * 创建钩子注册异常
     */
    public static function registrationFailed(string $hookName, string $reason): self
    {
        return new self("钩子注册失败: {$reason}", 1001, null, $hookName);
    }

    /**
     * 创建钩子执行异常
     */
    public static function executionFailed(string $hookName, string $hookId, string $reason): self
    {
        return new self("钩子执行失败: {$reason}", 1002, null, $hookName, $hookId);
    }

    /**
     * 创建钩子不存在异常
     */
    public static function notFound(string $hookName, ?string $hookId = null): self
    {
        $message = $hookId 
            ? "钩子不存在: {$hookName}#{$hookId}"
            : "钩子不存在: {$hookName}";
            
        return new self($message, 1003, null, $hookName, $hookId);
    }
}
<?php

namespace App\Hooks\Templates;

use App\Hooks\Contracts\HookInterface;
use App\Hooks\Attributes\Hook;
use App\Hooks\Attributes\Middleware;
use App\Hooks\Attributes\Condition;

/**
 * 简单钩子模板
 * 
 * 如果你不需要复杂的功能，可以直接实现 HookInterface
 * 
 * 使用 PHP 8.2 Attribute 语法（推荐）：
 * #[Hook(name: 'simple.hook.name', priority: 10, group: 'simple')]
 * 
 * 或者使用传统注释语法（向后兼容）：
 * @hook simple.hook.name
 * @priority 10
 * @group simple
 */
#[Hook(
    name: 'simple.hook.name',
    priority: 10,
    group: 'simple',
    description: '简单钩子模板'
)]
#[Middleware(class: 'App\Hooks\Middleware\AuthMiddleware')]
#[Condition(type: 'environment', value: 'production')]
class SimpleHookTemplate implements HookInterface
{
    /**
     * 处理钩子逻辑
     * 
     * @param mixed ...$args 钩子参数
     * @return mixed 处理结果
     */
    public function handle(...$args)
    {
        // TODO: 在这里实现你的简单业务逻辑
        
        return 'Simple hook executed';
    }

    /**
     * 获取钩子描述
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return '简单钩子模板';
    }

    /**
     * 获取钩子优先级
     * 
     * @return int
     */
    public function getPriority(): int
    {
        return 10;
    }
}
<?php

namespace App\Hooks\Facades;

use Illuminate\Support\Facades\Facade;
use App\Hooks\HookManager;

/**
 * 钩子管理器 Facade
 * 
 * @method static string register(string $hookName, $callback, int $priority = 10, ?string $group = null)
 * @method static array registerBatch(array $hooks, ?string $group = null)
 * @method static \App\Hooks\HookResult execute(string $hookName, ...$args)
 * @method static bool remove(string $hookName, ?string $hookId = null)
 * @method static int removeByGroup(string $group)
 * @method static bool toggle(string $hookName, string $hookId, bool $enabled = true)
 * @method static array getHooks(?string $hookName = null, ?string $group = null)
 * @method static array getStats()
 * @method static void addMiddleware(string $hookName, callable $middleware)
 * @method static void clearCache()
 * @method static void setCacheEnabled(bool $enabled)
 * 
 * @see \App\Hooks\HookManager
 */
class Hook extends Facade
{
    /**
     * 获取组件的注册名称
     */
    protected static function getFacadeAccessor(): string
    {
        return HookManager::class;
    }
}
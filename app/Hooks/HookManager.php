<?php

namespace App\Hooks;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Container\Container;
use App\Hooks\Contracts\HookInterface;
use App\Hooks\Exceptions\HookException;

/**
 * 钩子管理器
 * 提供完整的钩子注册、执行、管理功能
 */
class HookManager
{
    protected Container $container;
    protected array $hooks = [];
    protected array $middleware = [];
    protected bool $cacheEnabled = true;
    protected string $cachePrefix = 'hooks:';
    
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->loadHooksFromCache();
    }

    /**
     * 注册钩子
     * 
     * @param string $hookName 钩子名称
     * @param callable|string|array $callback 回调函数
     * @param int $priority 优先级 (数字越小优先级越高)
     * @param string|null $group 钩子分组
     * @return string 钩子ID
     */
    public function register(string $hookName, $callback, int $priority = 10, ?string $group = null): string
    {
        $hookId = $this->generateHookId($hookName, $callback, $group);
        
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }
        
        $this->hooks[$hookName][$hookId] = [
            'id' => $hookId,
            'callback' => $callback,
            'priority' => $priority,
            'group' => $group,
            'enabled' => true,
            'created_at' => now(),
            'call_count' => 0,
            'last_called' => null,
            'metadata' => []
        ];
        
        // 按优先级排序
        uasort($this->hooks[$hookName], fn($a, $b) => $a['priority'] <=> $b['priority']);
        
        $this->saveHooksToCache();
        
        Log::info("钩子已注册", [
            'hook_name' => $hookName,
            'hook_id' => $hookId,
            'priority' => $priority,
            'group' => $group
        ]);
        
        return $hookId;
    }

    /**
     * 批量注册钩子
     * 
     * @param array $hooks 钩子配置数组
     * @param string|null $group 默认分组
     */
    public function registerBatch(array $hooks, ?string $group = null): array
    {
        $registeredIds = [];
        
        foreach ($hooks as $hookName => $config) {
            if (is_callable($config)) {
                // 简单格式: 'hook_name' => callable
                $registeredIds[] = $this->register($hookName, $config, 10, $group);
            } elseif (is_array($config)) {
                // 完整格式: 'hook_name' => ['callback' => ..., 'priority' => ...]
                $callback = $config['callback'] ?? null;
                $priority = $config['priority'] ?? 10;
                $hookGroup = $config['group'] ?? $group;
                
                if ($callback) {
                    $registeredIds[] = $this->register($hookName, $callback, $priority, $hookGroup);
                }
            }
        }
        
        return $registeredIds;
    }

    /**
     * 执行钩子
     * 
     * @param string $hookName 钩子名称
     * @param mixed ...$args 传递给钩子的参数
     * @return HookResult 执行结果
     */
    public function execute(string $hookName, ...$args): HookResult
    {
        $startTime = microtime(true);
        $results = [];
        $errors = [];
        $executedCount = 0;
        
        if (!isset($this->hooks[$hookName])) {
            return new HookResult($hookName, [], [], 0, 0);
        }
        
        Log::debug("开始执行钩子", ['hook_name' => $hookName, 'args_count' => count($args)]);
        
        foreach ($this->hooks[$hookName] as $hookId => &$hook) {
            if (!$hook['enabled']) {
                continue;
            }
            
            try {
                // 执行中间件
                if (!$this->executeMiddleware($hookName, $hookId, $args)) {
                    continue;
                }
                
                $result = $this->executeCallback($hook['callback'], $args);
                $results[$hookId] = $result;
                
                // 更新统计信息
                $hook['call_count']++;
                $hook['last_called'] = now();
                $executedCount++;
                
            } catch (\Throwable $e) {
                $errors[$hookId] = [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ];
                
                Log::error("钩子执行失败", [
                    'hook_name' => $hookName,
                    'hook_id' => $hookId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $executionTime = microtime(true) - $startTime;
        $this->saveHooksToCache();
        
        Log::debug("钩子执行完成", [
            'hook_name' => $hookName,
            'executed_count' => $executedCount,
            'execution_time' => $executionTime
        ]);
        
        return new HookResult($hookName, $results, $errors, $executedCount, $executionTime);
    }

    /**
     * 执行回调函数
     */
    protected function executeCallback($callback, array $args)
    {
        if (is_string($callback)) {
            // 字符串格式: "Class@method" 或 "Class::method"
            if (str_contains($callback, '@')) {
                [$class, $method] = explode('@', $callback, 2);
                $instance = $this->container->make($class);
                return $instance->$method(...$args);
            } elseif (str_contains($callback, '::')) {
                [$class, $method] = explode('::', $callback, 2);
                return $class::$method(...$args);
            } else {
                // 单独的类名，假设有 handle 方法
                $instance = $this->container->make($callback);
                if (method_exists($instance, 'handle')) {
                    return $instance->handle(...$args);
                }
                throw new HookException("类 {$callback} 没有 handle 方法");
            }
        } elseif (is_array($callback)) {
            // 数组格式: [Class::class, 'method'] 或 [$instance, 'method']
            [$class, $method] = $callback;
            if (is_string($class)) {
                $instance = $this->container->make($class);
                return $instance->$method(...$args);
            }
            return $class->$method(...$args);
        } elseif (is_callable($callback)) {
            // 闭包或其他可调用对象
            return call_user_func_array($callback, $args);
        }
        
        throw new HookException("无效的回调格式");
    }

    /**
     * 移除钩子
     */
    public function remove(string $hookName, ?string $hookId = null): bool
    {
        if (!isset($this->hooks[$hookName])) {
            return false;
        }
        
        if ($hookId) {
            if (isset($this->hooks[$hookName][$hookId])) {
                unset($this->hooks[$hookName][$hookId]);
                $this->saveHooksToCache();
                return true;
            }
            return false;
        }
        
        // 移除整个钩子名称下的所有钩子
        unset($this->hooks[$hookName]);
        $this->saveHooksToCache();
        return true;
    }

    /**
     * 按分组移除钩子
     */
    public function removeByGroup(string $group): int
    {
        $removedCount = 0;
        
        foreach ($this->hooks as $hookName => &$hooks) {
            foreach ($hooks as $hookId => $hook) {
                if ($hook['group'] === $group) {
                    unset($hooks[$hookId]);
                    $removedCount++;
                }
            }
            
            // 如果该钩子名称下没有钩子了，移除整个键
            if (empty($hooks)) {
                unset($this->hooks[$hookName]);
            }
        }
        
        if ($removedCount > 0) {
            $this->saveHooksToCache();
        }
        
        return $removedCount;
    }

    /**
     * 启用/禁用钩子
     */
    public function toggle(string $hookName, string $hookId, bool $enabled = true): bool
    {
        if (!isset($this->hooks[$hookName][$hookId])) {
            return false;
        }
        
        $this->hooks[$hookName][$hookId]['enabled'] = $enabled;
        $this->saveHooksToCache();
        
        return true;
    }

    /**
     * 获取钩子列表
     */
    public function getHooks(?string $hookName = null, ?string $group = null): array
    {
        if ($hookName) {
            $hooks = $this->hooks[$hookName] ?? [];
        } else {
            $hooks = $this->hooks;
        }
        
        if ($group) {
            return $this->filterByGroup($hooks, $group);
        }
        
        return $hooks;
    }

    /**
     * 获取钩子统计信息
     */
    public function getStats(): array
    {
        $totalHooks = 0;
        $enabledHooks = 0;
        $totalCalls = 0;
        $groupStats = [];
        
        foreach ($this->hooks as $hookName => $hooks) {
            foreach ($hooks as $hook) {
                $totalHooks++;
                if ($hook['enabled']) {
                    $enabledHooks++;
                }
                $totalCalls += $hook['call_count'];
                
                $group = $hook['group'] ?? 'default';
                if (!isset($groupStats[$group])) {
                    $groupStats[$group] = ['count' => 0, 'calls' => 0];
                }
                $groupStats[$group]['count']++;
                $groupStats[$group]['calls'] += $hook['call_count'];
            }
        }
        
        return [
            'total_hooks' => $totalHooks,
            'enabled_hooks' => $enabledHooks,
            'disabled_hooks' => $totalHooks - $enabledHooks,
            'total_calls' => $totalCalls,
            'hook_names' => array_keys($this->hooks),
            'groups' => $groupStats
        ];
    }

    /**
     * 添加中间件
     */
    public function addMiddleware(string $hookName, callable $middleware): void
    {
        if (!isset($this->middleware[$hookName])) {
            $this->middleware[$hookName] = [];
        }
        
        $this->middleware[$hookName][] = $middleware;
    }

    /**
     * 执行中间件
     */
    protected function executeMiddleware(string $hookName, string $hookId, array $args): bool
    {
        if (!isset($this->middleware[$hookName])) {
            return true;
        }
        
        foreach ($this->middleware[$hookName] as $middleware) {
            try {
                $result = call_user_func($middleware, $hookName, $hookId, $args);
                if ($result === false) {
                    return false;
                }
            } catch (\Throwable $e) {
                Log::error("钩子中间件执行失败", [
                    'hook_name' => $hookName,
                    'hook_id' => $hookId,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        }
        
        return true;
    }

    /**
     * 生成钩子ID
     */
    protected function generateHookId(string $hookName, $callback, ?string $group): string
    {
        $callbackStr = $this->serializeCallback($callback);
        $data = $hookName . '|' . $callbackStr . '|' . ($group ?? 'default');
        return 'hook_' . substr(md5($data), 0, 12);
    }

    /**
     * 序列化回调函数用于生成ID
     */
    protected function serializeCallback($callback): string
    {
        if (is_string($callback)) {
            return $callback;
        } elseif (is_array($callback)) {
            $class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
            return $class . '@' . $callback[1];
        } elseif ($callback instanceof \Closure) {
            return 'closure:' . spl_object_hash($callback);
        }
        
        return 'unknown';
    }

    /**
     * 按分组过滤钩子
     */
    protected function filterByGroup(array $hooks, string $group): array
    {
        $filtered = [];
        
        foreach ($hooks as $hookName => $hookList) {
            if (is_array($hookList)) {
                foreach ($hookList as $hookId => $hook) {
                    if (($hook['group'] ?? 'default') === $group) {
                        if (!isset($filtered[$hookName])) {
                            $filtered[$hookName] = [];
                        }
                        $filtered[$hookName][$hookId] = $hook;
                    }
                }
            }
        }
        
        return $filtered;
    }

    /**
     * 从缓存加载钩子
     */
    protected function loadHooksFromCache(): void
    {
        if (!$this->cacheEnabled) {
            return;
        }
        
        $cached = Cache::get($this->cachePrefix . 'registered');
        if (is_array($cached)) {
            $this->hooks = $cached;
        }
    }

    /**
     * 保存钩子到缓存
     */
    protected function saveHooksToCache(): void
    {
        // if (!$this->cacheEnabled) {
        //     return;
        // }
        
        // Cache::put($this->cachePrefix . 'registered', $this->hooks, now()->addHours(24));
    }

    /**
     * 清除钩子缓存
     */
    public function clearCache(): void
    {
        Cache::forget($this->cachePrefix . 'registered');
        $this->hooks = [];
    }

    /**
     * 启用/禁用缓存
     */
    public function setCacheEnabled(bool $enabled): void
    {
        $this->cacheEnabled = $enabled;
        
        if (!$enabled) {
            $this->clearCache();
        }
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use App\Hooks\Facades\Hook;

class ViewLifecycleService
{
    /**
     * 注册的生命周期钩子
     */
    protected array $lifecycleHooks = [];

    /**
     * 初始化视图生命周期
     */
    public function initialize(): void
    {
        $this->registerViewComposers();
        $this->registerViewCreators();
        $this->registerViewEvents();
    }

    /**
     * 注册视图组合器
     */
    protected function registerViewComposers(): void
    {
        // 全局视图组合器 - 在视图渲染前注入数据
        View::composer('*', function ($view) {
            $viewName = $view->getName();
            $data = $view->getData();

            // 触发视图组合钩子
            $result = Hook::execute('view.composing', $viewName, $data);
            
            // 合并钩子返回的数据
            if ($result->isSuccessful()) {
                foreach ($result->getResults() as $hookResult) {
                    if (isset($hookResult['data']) && is_array($hookResult['data'])) {
                        $view->with($hookResult['data']);
                    }
                }
            }
        });
    }

    /**
     * 注册视图创建器
     */
    protected function registerViewCreators(): void
    {
        // 全局视图创建器 - 在视图实例化时执行
        View::creator('*', function ($view) {
            $viewName = $view->getName();
            
            // 触发视图创建钩子
            Hook::execute('view.creating', $viewName, $view);
        });
    }

    /**
     * 注册视图事件
     */
    protected function registerViewEvents(): void
    {
        // 视图渲染前事件
        Event::listen('composing: *', function ($view, $data = []) {
            $viewName = is_string($view) ? $view : $view->getName();
            
            // 触发渲染前钩子
            Hook::execute('view.before_render', $viewName, $data);
        });
    }

    /**
     * 注册生命周期钩子
     */
    public function registerLifecycleHook(string $lifecycle, string $pattern, callable $callback, int $priority = 10): string
    {
        $hookId = uniqid('lifecycle_', true);
        
        $this->lifecycleHooks[$lifecycle][$hookId] = [
            'pattern' => $pattern,
            'callback' => $callback,
            'priority' => $priority,
        ];

        // 按优先级排序
        if (isset($this->lifecycleHooks[$lifecycle])) {
            uasort($this->lifecycleHooks[$lifecycle], function ($a, $b) {
                return $b['priority'] <=> $a['priority'];
            });
        }

        return $hookId;
    }

    /**
     * 执行生命周期钩子
     */
    public function executeLifecycleHooks(string $lifecycle, string $viewName, array $data = []): array
    {
        $results = [];

        if (!isset($this->lifecycleHooks[$lifecycle])) {
            return $results;
        }

        foreach ($this->lifecycleHooks[$lifecycle] as $hookId => $hook) {
            // 检查视图名称是否匹配模式
            if ($this->matchesPattern($viewName, $hook['pattern'])) {
                try {
                    $result = call_user_func($hook['callback'], $viewName, $data);
                    $results[$hookId] = $result;
                } catch (\Throwable $e) {
                    logger()->error("视图生命周期钩子执行失败", [
                        'lifecycle' => $lifecycle,
                        'hook_id' => $hookId,
                        'view' => $viewName,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $results;
    }

    /**
     * 检查视图名称是否匹配模式
     */
    protected function matchesPattern(string $viewName, string $pattern): bool
    {
        // 支持通配符匹配
        if ($pattern === '*') {
            return true;
        }

        // 转换通配符为正则表达式
        $regex = '/^' . str_replace(
            ['*', '.'],
            ['.*', '\.'],
            $pattern
        ) . '$/';

        return (bool) preg_match($regex, $viewName);
    }

    /**
     * 移除生命周期钩子
     */
    public function removeLifecycleHook(string $lifecycle, string $hookId): bool
    {
        if (isset($this->lifecycleHooks[$lifecycle][$hookId])) {
            unset($this->lifecycleHooks[$lifecycle][$hookId]);
            return true;
        }

        return false;
    }

    /**
     * 获取所有生命周期钩子
     */
    public function getLifecycleHooks(string $lifecycle = null): array
    {
        if ($lifecycle !== null) {
            return $this->lifecycleHooks[$lifecycle] ?? [];
        }

        return $this->lifecycleHooks;
    }
}

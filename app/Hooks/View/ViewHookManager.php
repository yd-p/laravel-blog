<?php

namespace App\Hooks\View;

use App\Hooks\HookManager;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewInstance;

/**
 * 视图钩子管理器
 * 
 * 专门管理视图相关的钩子，提供视图生命周期的钩子支持
 */
class ViewHookManager
{
    protected HookManager $hookManager;
    protected array $registeredViews = [];
    protected array $viewData = [];

    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
        $this->registerViewEvents();
    }

    /**
     * 注册视图事件监听器
     */
    protected function registerViewEvents(): void
    {
        // 视图组合事件
        View::composer('*', function (ViewInstance $view) {
            $this->handleViewComposing($view);
        });

        // 视图创建事件
        View::creator('*', function (ViewInstance $view) {
            $this->handleViewCreating($view);
        });
    }

    /**
     * 处理视图组合
     */
    protected function handleViewComposing(ViewInstance $view): void
    {
        $viewName = $view->getName();
        $data = $view->getData();

        // 执行视图组合钩子
        $result = $this->hookManager->execute('view.composing', $viewName, $data);

        // 处理钩子结果
        if ($result->isSuccessful()) {
            foreach ($result->getResults() as $hookResult) {
                if (isset($hookResult['processed_data'])) {
                    $view->with($hookResult['processed_data']);
                }
            }
        }
    }

    /**
     * 处理视图创建
     */
    protected function handleViewCreating(ViewInstance $view): void
    {
        $viewName = $view->getName();
        $data = $view->getData();

        // 执行视图创建钩子
        $result = $this->hookManager->execute('view.creating', $viewName, $data);

        // 处理钩子结果
        if ($result->isSuccessful()) {
            foreach ($result->getResults() as $hookResult) {
                if (isset($hookResult['creator_data'])) {
                    $view->with($hookResult['creator_data']);
                }
            }
        }
    }

    /**
     * 注册视图渲染前钩子
     */
    public function beforeRender(string $viewPattern, callable $callback, int $priority = 10): string
    {
        return $this->hookManager->register(
            "view.before_render.{$viewPattern}",
            $callback,
            $priority,
            'view'
        );
    }

    /**
     * 注册视图渲染后钩子
     */
    public function afterRender(string $viewPattern, callable $callback, int $priority = 10): string
    {
        return $this->hookManager->register(
            "view.after_render.{$viewPattern}",
            $callback,
            $priority,
            'view'
        );
    }

    /**
     * 注册数据注入钩子
     */
    public function injectData(string $viewPattern, callable $callback, int $priority = 10): string
    {
        return $this->hookManager->register(
            "view.inject_data.{$viewPattern}",
            $callback,
            $priority,
            'view'
        );
    }

    /**
     * 执行视图渲染前钩子
     */
    public function executeBeforeRender(string $viewName, array $data = []): array
    {
        $results = [];

        // 执行通用渲染前钩子
        $generalResult = $this->hookManager->execute('view.before_render', $viewName, $data);
        if ($generalResult->isSuccessful()) {
            $results['general'] = $generalResult->getResults();
        }

        // 执行特定视图的渲染前钩子
        $specificResult = $this->hookManager->execute("view.before_render.{$viewName}", $viewName, $data);
        if ($specificResult->isSuccessful()) {
            $results['specific'] = $specificResult->getResults();
        }

        // 执行模式匹配的钩子
        $patternResults = $this->executePatternHooks('before_render', $viewName, $data);
        if (!empty($patternResults)) {
            $results['pattern'] = $patternResults;
        }

        return $results;
    }

    /**
     * 执行视图渲染后钩子
     */
    public function executeAfterRender(string $viewName, string $content, array $data = []): array
    {
        $results = [];
        $options = ['rendered_content' => $content];

        // 执行通用渲染后钩子
        $generalResult = $this->hookManager->execute('view.after_render', $viewName, $data, $options);
        if ($generalResult->isSuccessful()) {
            $results['general'] = $generalResult->getResults();
        }

        // 执行特定视图的渲染后钩子
        $specificResult = $this->hookManager->execute("view.after_render.{$viewName}", $viewName, $data, $options);
        if ($specificResult->isSuccessful()) {
            $results['specific'] = $specificResult->getResults();
        }

        // 执行模式匹配的钩子
        $patternResults = $this->executePatternHooks('after_render', $viewName, $data, $options);
        if (!empty($patternResults)) {
            $results['pattern'] = $patternResults;
        }

        return $results;
    }

    /**
     * 执行数据注入钩子
     */
    public function executeDataInjection(string $viewName, array $data = []): array
    {
        $results = [];

        // 执行通用数据注入钩子
        $generalResult = $this->hookManager->execute('view.inject_data', $viewName, $data);
        if ($generalResult->isSuccessful()) {
            $results['general'] = $generalResult->getResults();
        }

        // 执行特定视图的数据注入钩子
        $specificResult = $this->hookManager->execute("view.inject_data.{$viewName}", $viewName, $data);
        if ($specificResult->isSuccessful()) {
            $results['specific'] = $specificResult->getResults();
        }

        // 执行模式匹配的钩子
        $patternResults = $this->executePatternHooks('inject_data', $viewName, $data);
        if (!empty($patternResults)) {
            $results['pattern'] = $patternResults;
        }

        return $results;
    }

    /**
     * 执行模式匹配的钩子
     */
    protected function executePatternHooks(string $action, string $viewName, array $data, array $options = []): array
    {
        $results = [];
        $patterns = $this->getViewPatterns($viewName);

        foreach ($patterns as $pattern) {
            $hookName = "view.{$action}.{$pattern}";
            $result = $this->hookManager->execute($hookName, $viewName, $data, $options);
            
            if ($result->isSuccessful()) {
                $results[$pattern] = $result->getResults();
            }
        }

        return $results;
    }

    /**
     * 获取视图模式
     */
    protected function getViewPatterns(string $viewName): array
    {
        $patterns = [];
        $parts = explode('.', $viewName);

        // 生成各种模式
        // 例如：admin.users.index -> [admin.*, admin.users.*, *]
        for ($i = 0; $i < count($parts); $i++) {
            $pattern = implode('.', array_slice($parts, 0, $i + 1)) . '.*';
            $patterns[] = $pattern;
        }

        // 添加通配符模式
        $patterns[] = '*';

        return array_unique($patterns);
    }

    /**
     * 注册视图模板修改钩子
     */
    public function modifyTemplate(string $viewPattern, callable $callback, int $priority = 10): string
    {
        return $this->hookManager->register(
            "view.modify_template.{$viewPattern}",
            $callback,
            $priority,
            'view'
        );
    }

    /**
     * 注册视图主题切换钩子
     */
    public function switchTheme(string $viewPattern, callable $callback, int $priority = 10): string
    {
        return $this->hookManager->register(
            "view.switch_theme.{$viewPattern}",
            $callback,
            $priority,
            'view'
        );
    }

    /**
     * 批量注册视图钩子
     */
    public function registerBatch(array $hooks): array
    {
        $registeredIds = [];

        foreach ($hooks as $hookConfig) {
            $type = $hookConfig['type'] ?? 'before_render';
            $pattern = $hookConfig['pattern'] ?? '*';
            $callback = $hookConfig['callback'];
            $priority = $hookConfig['priority'] ?? 10;

            switch ($type) {
                case 'before_render':
                    $registeredIds[] = $this->beforeRender($pattern, $callback, $priority);
                    break;
                case 'after_render':
                    $registeredIds[] = $this->afterRender($pattern, $callback, $priority);
                    break;
                case 'inject_data':
                    $registeredIds[] = $this->injectData($pattern, $callback, $priority);
                    break;
                case 'modify_template':
                    $registeredIds[] = $this->modifyTemplate($pattern, $callback, $priority);
                    break;
                case 'switch_theme':
                    $registeredIds[] = $this->switchTheme($pattern, $callback, $priority);
                    break;
            }
        }

        return $registeredIds;
    }

    /**
     * 获取视图钩子统计
     */
    public function getViewHookStats(): array
    {
        $allHooks = $this->hookManager->getHooks(null, 'view');
        $stats = [
            'total_view_hooks' => 0,
            'by_type' => [
                'before_render' => 0,
                'after_render' => 0,
                'inject_data' => 0,
                'modify_template' => 0,
                'switch_theme' => 0,
                'composing' => 0,
                'creating' => 0,
            ],
            'by_pattern' => []
        ];

        foreach ($allHooks as $hookName => $hooks) {
            if (str_starts_with($hookName, 'view.')) {
                $stats['total_view_hooks'] += count($hooks);
                
                // 按类型统计
                foreach ($stats['by_type'] as $type => $count) {
                    if (str_contains($hookName, $type)) {
                        $stats['by_type'][$type] += count($hooks);
                    }
                }

                // 按模式统计
                $pattern = $this->extractPatternFromHookName($hookName);
                if ($pattern) {
                    $stats['by_pattern'][$pattern] = ($stats['by_pattern'][$pattern] ?? 0) + count($hooks);
                }
            }
        }

        return $stats;
    }

    /**
     * 从钩子名称提取模式
     */
    protected function extractPatternFromHookName(string $hookName): ?string
    {
        $parts = explode('.', $hookName);
        if (count($parts) >= 3) {
            return implode('.', array_slice($parts, 2));
        }
        return null;
    }

    /**
     * 清除视图钩子缓存
     */
    public function clearViewHookCache(): void
    {
        $this->registeredViews = [];
        $this->viewData = [];
    }

    /**
     * 获取已注册的视图
     */
    public function getRegisteredViews(): array
    {
        return $this->registeredViews;
    }

    /**
     * 设置视图数据
     */
    public function setViewData(string $viewName, array $data): void
    {
        $this->viewData[$viewName] = $data;
    }

    /**
     * 获取视图数据
     */
    public function getViewData(string $viewName): array
    {
        return $this->viewData[$viewName] ?? [];
    }
}
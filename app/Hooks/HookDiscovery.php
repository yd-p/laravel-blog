<?php

namespace App\Hooks;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionAttribute;
use App\Hooks\Contracts\HookInterface;
use App\Hooks\Attributes\Hook as HookAttribute;
use App\Hooks\Attributes\Priority;
use App\Hooks\Attributes\Group;
use App\Hooks\Attributes\Middleware;
use App\Hooks\Attributes\Condition;

/**
 * 钩子发现器
 * 自动发现和注册钩子类
 * 支持 PHP 8.2 Attribute 和传统注释两种方式
 */
class HookDiscovery
{
    protected HookManager $hookManager;
    protected array $discoveryPaths = [];
    protected array $excludePaths = [];

    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
        $this->setupDefaultPaths();
    }

    /**
     * 设置默认发现路径
     */
    protected function setupDefaultPaths(): void
    {
        $this->discoveryPaths = [
            app_path('Hooks'),
            app_path('Hooks/Custom'),
            base_path('plugins/*/app/Hooks'),
        ];

        $this->excludePaths = [
            app_path('Hooks/Contracts'),
            app_path('Hooks/Exceptions'),
            app_path('Hooks/Attributes'),
        ];
    }

    /**
     * 添加发现路径
     */
    public function addPath(string $path): self
    {
        $this->discoveryPaths[] = $path;
        return $this;
    }

    /**
     * 添加排除路径
     */
    public function addExcludePath(string $path): self
    {
        $this->excludePaths[] = $path;
        return $this;
    }

    /**
     * 发现并注册钩子
     */
    public function discover(): void
    {
        foreach ($this->discoveryPaths as $path) {
            $this->discoverInPath($path);
        }
    }

    /**
     * 在指定路径中发现钩子
     */
    protected function discoverInPath(string $path): void
    {
        // 处理通配符路径
        if (str_contains($path, '*')) {
            $paths = glob($path, GLOB_ONLYDIR);
            foreach ($paths as $expandedPath) {
                $this->discoverInPath($expandedPath);
            }
            return;
        }

        if (!is_dir($path)) {
            return;
        }

        // 检查是否在排除路径中
        foreach ($this->excludePaths as $excludePath) {
            if (str_starts_with($path, $excludePath)) {
                return;
            }
        }

        $files = File::allFiles($path);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $this->processFile($file->getPathname());
        }
    }

    /**
     * 处理单个PHP文件
     */
    protected function processFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        
        // 提取命名空间
        $namespace = $this->extractNamespace($content);
        if (!$namespace) {
            return;
        }

        // 提取类名
        $className = $this->extractClassName($content);
        if (!$className) {
            return;
        }

        $fullClassName = $namespace . '\\' . $className;

        try {
            // 检查类是否存在
            if (!class_exists($fullClassName)) {
                return;
            }

            $reflection = new ReflectionClass($fullClassName);

            // 检查是否实现了钩子接口
            if (!$reflection->implementsInterface(HookInterface::class)) {
                return;
            }

            // 检查是否是抽象类
            if ($reflection->isAbstract()) {
                return;
            }

            $this->registerHookClass($fullClassName, $reflection);

        } catch (\Throwable $e) {
            // 记录错误但不中断发现过程
            logger()->warning("钩子发现失败: {$fullClassName}", [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 注册钩子类
     */
    protected function registerHookClass(string $className, ReflectionClass $reflection): void
    {
        // 从 Attribute 或注释获取钩子信息
        $hookInfo = $this->extractHookInfo($reflection);
        
        // 如果钩子被禁用，跳过注册
        if (!$hookInfo['enabled']) {
            logger()->debug("钩子已禁用，跳过注册: {$className}");
            return;
        }

        // 注册钩子
        $hookId = $this->hookManager->register(
            $hookInfo['name'], 
            $className, 
            $hookInfo['priority'], 
            $hookInfo['group']
        );

        // 注册中间件
        $this->registerHookMiddleware($hookInfo['name'], $hookInfo['middleware']);

        // 处理条件
        $this->processHookConditions($hookInfo['name'], $hookId, $hookInfo['conditions']);

        logger()->debug("自动注册钩子", [
            'hook_name' => $hookInfo['name'],
            'hook_id' => $hookId,
            'class' => $className,
            'priority' => $hookInfo['priority'],
            'group' => $hookInfo['group'],
            'middleware_count' => count($hookInfo['middleware']),
            'conditions_count' => count($hookInfo['conditions'])
        ]);
    }

    /**
     * 提取钩子信息（支持 Attribute 和注释）
     */
    protected function extractHookInfo(ReflectionClass $reflection): array
    {
        $hookInfo = [
            'name' => null,
            'priority' => 10,
            'group' => null,
            'description' => null,
            'enabled' => true,
            'middleware' => [],
            'conditions' => []
        ];

        // 优先使用 PHP 8.2 Attribute
        if (PHP_VERSION_ID >= 80200) {
            $hookInfo = $this->extractFromAttributes($reflection, $hookInfo);
        }

        // 如果没有找到 Attribute，回退到注释
        if ($hookInfo['name'] === null) {
            $hookInfo = $this->extractFromDocComment($reflection, $hookInfo);
        }

        // 如果仍然没有钩子名称，从类名推断
        if ($hookInfo['name'] === null) {
            $hookInfo['name'] = $this->inferHookNameFromClass($reflection);
        }

        return $hookInfo;
    }

    /**
     * 从 PHP 8.2 Attribute 提取信息
     */
    protected function extractFromAttributes(ReflectionClass $reflection, array $hookInfo): array
    {
        // 获取 Hook Attribute
        $hookAttributes = $reflection->getAttributes(HookAttribute::class);
        if (!empty($hookAttributes)) {
            /** @var HookAttribute $hookAttr */
            $hookAttr = $hookAttributes[0]->newInstance();
            $hookInfo['name'] = $hookAttr->name;
            $hookInfo['priority'] = $hookAttr->priority;
            $hookInfo['group'] = $hookAttr->group;
            $hookInfo['description'] = $hookAttr->description;
            $hookInfo['enabled'] = $hookAttr->enabled;
        }

        // 获取 Priority Attribute（可以覆盖 Hook 中的优先级）
        $priorityAttributes = $reflection->getAttributes(Priority::class);
        if (!empty($priorityAttributes)) {
            /** @var Priority $priorityAttr */
            $priorityAttr = $priorityAttributes[0]->newInstance();
            $hookInfo['priority'] = $priorityAttr->value;
        }

        // 获取 Group Attribute（可以覆盖 Hook 中的分组）
        $groupAttributes = $reflection->getAttributes(Group::class);
        if (!empty($groupAttributes)) {
            /** @var Group $groupAttr */
            $groupAttr = $groupAttributes[0]->newInstance();
            $hookInfo['group'] = $groupAttr->name;
        }

        // 获取 Middleware Attributes
        $middlewareAttributes = $reflection->getAttributes(Middleware::class);
        foreach ($middlewareAttributes as $middlewareAttribute) {
            /** @var Middleware $middlewareAttr */
            $middlewareAttr = $middlewareAttribute->newInstance();
            $hookInfo['middleware'][] = [
                'class' => $middlewareAttr->class,
                'parameters' => $middlewareAttr->parameters
            ];
        }

        // 获取 Condition Attributes
        $conditionAttributes = $reflection->getAttributes(Condition::class);
        foreach ($conditionAttributes as $conditionAttribute) {
            /** @var Condition $conditionAttr */
            $conditionAttr = $conditionAttribute->newInstance();
            $hookInfo['conditions'][] = [
                'type' => $conditionAttr->type,
                'value' => $conditionAttr->value,
                'operator' => $conditionAttr->operator
            ];
        }

        return $hookInfo;
    }

    /**
     * 从注释提取信息（向后兼容）
     */
    protected function extractFromDocComment(ReflectionClass $reflection, array $hookInfo): array
    {
        $docComment = $reflection->getDocComment();
        if (!$docComment) {
            return $hookInfo;
        }

        // 提取 @hook 注解
        if (preg_match('/@hook\s+([^\s\n]+)/', $docComment, $matches)) {
            $hookInfo['name'] = $matches[1];
        }

        // 提取 @priority 注解
        if (preg_match('/@priority\s+(\d+)/', $docComment, $matches)) {
            $hookInfo['priority'] = (int) $matches[1];
        }

        // 提取 @group 注解
        if (preg_match('/@group\s+([^\s\n]+)/', $docComment, $matches)) {
            $hookInfo['group'] = $matches[1];
        }

        // 提取 @description 注解
        if (preg_match('/@description\s+(.+)/', $docComment, $matches)) {
            $hookInfo['description'] = trim($matches[1]);
        }

        // 提取 @enabled 注解
        if (preg_match('/@enabled\s+(true|false)/', $docComment, $matches)) {
            $hookInfo['enabled'] = $matches[1] === 'true';
        }

        return $hookInfo;
    }

    /**
     * 从类名推断钩子名称
     */
    protected function inferHookNameFromClass(ReflectionClass $reflection): string
    {
        $shortName = $reflection->getShortName();
        
        // 移除 Hook 后缀
        if (str_ends_with($shortName, 'Hook')) {
            $shortName = substr($shortName, 0, -4);
        }

        // 转换为蛇形命名
        return Str::snake($shortName, '.');
    }

    /**
     * 注册钩子中间件
     */
    protected function registerHookMiddleware(string $hookName, array $middleware): void
    {
        foreach ($middleware as $middlewareInfo) {
            $middlewareClass = $middlewareInfo['class'];
            $parameters = $middlewareInfo['parameters'] ?? [];

            try {
                $middlewareInstance = new $middlewareClass(...$parameters);
                $this->hookManager->addMiddleware($hookName, $middlewareInstance);
            } catch (\Throwable $e) {
                logger()->warning("钩子中间件注册失败", [
                    'hook_name' => $hookName,
                    'middleware_class' => $middlewareClass,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * 处理钩子条件
     */
    protected function processHookConditions(string $hookName, string $hookId, array $conditions): void
    {
        if (empty($conditions)) {
            return;
        }

        // 为钩子添加条件中间件
        $conditionMiddleware = function (string $hookName, string $hookId, array $args) use ($conditions) {
            return $this->evaluateConditions($conditions, $hookName, $hookId, $args);
        };

        $this->hookManager->addMiddleware($hookName, $conditionMiddleware);
    }

    /**
     * 评估钩子条件
     */
    protected function evaluateConditions(array $conditions, string $hookName, string $hookId, array $args): bool
    {
        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($condition, $hookName, $hookId, $args)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 评估单个条件
     */
    protected function evaluateCondition(array $condition, string $hookName, string $hookId, array $args): bool
    {
        $type = $condition['type'];
        $value = $condition['value'];
        $operator = $condition['operator'] ?? '=';

        switch ($type) {
            case 'environment':
                return $this->compareValues(app()->environment(), $value, $operator);
            
            case 'auth':
                return $this->compareValues(auth()->check(), $value, $operator);
            
            case 'user_role':
                $user = auth()->user();
                if (!$user) return false;
                return $this->compareValues($user->role ?? null, $value, $operator);
            
            case 'config':
                $configValue = config($value);
                return $this->compareValues($configValue, true, $operator);
            
            case 'time':
                return $this->compareValues(now()->format('H:i'), $value, $operator);
            
            case 'custom':
                // 允许自定义条件评估
                if (is_callable($value)) {
                    return call_user_func($value, $hookName, $hookId, $args);
                }
                return true;
            
            default:
                logger()->warning("未知的钩子条件类型: {$type}");
                return true;
        }
    }

    /**
     * 比较值
     */
    protected function compareValues($actual, $expected, string $operator): bool
    {
        switch ($operator) {
            case '=':
            case '==':
                return $actual == $expected;
            case '===':
                return $actual === $expected;
            case '!=':
                return $actual != $expected;
            case '!==':
                return $actual !== $expected;
            case '>':
                return $actual > $expected;
            case '>=':
                return $actual >= $expected;
            case '<':
                return $actual < $expected;
            case '<=':
                return $actual <= $expected;
            case 'in':
                return in_array($actual, (array) $expected);
            case 'not_in':
                return !in_array($actual, (array) $expected);
            case 'contains':
                return str_contains((string) $actual, (string) $expected);
            case 'starts_with':
                return str_starts_with((string) $actual, (string) $expected);
            case 'ends_with':
                return str_ends_with((string) $actual, (string) $expected);
            default:
                return $actual == $expected;
        }
    }

    /**
     * 提取命名空间
     */
    protected function extractNamespace(string $content): ?string
    {
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * 提取类名
     */
    protected function extractClassName(string $content): ?string
    {
        if (preg_match('/class\s+([^\s{]+)/', $content, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * 重新发现钩子（清除缓存后重新发现）
     */
    public function rediscover(): void
    {
        $this->hookManager->clearCache();
        $this->discover();
    }

    /**
     * 获取发现统计信息
     */
    public function getDiscoveryStats(): array
    {
        return [
            'discovery_paths' => $this->discoveryPaths,
            'exclude_paths' => $this->excludePaths,
            'php_version' => PHP_VERSION,
            'attributes_supported' => PHP_VERSION_ID >= 80200,
        ];
    }
}
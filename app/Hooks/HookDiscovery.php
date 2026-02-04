<?php

namespace App\Hooks;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use App\Hooks\Contracts\HookInterface;

/**
 * 钩子发现器
 * 自动发现和注册钩子类
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
        // 从类名推断钩子名称
        $hookName = $this->inferHookName($className, $reflection);
        
        // 获取优先级
        $priority = $this->getHookPriority($reflection);
        
        // 获取分组
        $group = $this->getHookGroup($reflection);

        // 注册钩子
        $hookId = $this->hookManager->register($hookName, $className, $priority, $group);

        logger()->debug("自动注册钩子", [
            'hook_name' => $hookName,
            'hook_id' => $hookId,
            'class' => $className,
            'priority' => $priority,
            'group' => $group
        ]);
    }

    /**
     * 推断钩子名称
     */
    protected function inferHookName(string $className, ReflectionClass $reflection): string
    {
        // 检查是否有 @hook 注解
        $docComment = $reflection->getDocComment();
        if ($docComment && preg_match('/@hook\s+([^\s\n]+)/', $docComment, $matches)) {
            return $matches[1];
        }

        // 从类名推断
        $shortName = $reflection->getShortName();
        
        // 移除 Hook 后缀
        if (str_ends_with($shortName, 'Hook')) {
            $shortName = substr($shortName, 0, -4);
        }

        // 转换为蛇形命名
        return Str::snake($shortName, '.');
    }

    /**
     * 获取钩子优先级
     */
    protected function getHookPriority(ReflectionClass $reflection): int
    {
        $docComment = $reflection->getDocComment();
        if ($docComment && preg_match('/@priority\s+(\d+)/', $docComment, $matches)) {
            return (int) $matches[1];
        }

        // 尝试从类的常量获取
        if ($reflection->hasConstant('PRIORITY')) {
            return $reflection->getConstant('PRIORITY');
        }

        return 10; // 默认优先级
    }

    /**
     * 获取钩子分组
     */
    protected function getHookGroup(ReflectionClass $reflection): ?string
    {
        $docComment = $reflection->getDocComment();
        if ($docComment && preg_match('/@group\s+([^\s\n]+)/', $docComment, $matches)) {
            return $matches[1];
        }

        // 从命名空间推断分组
        $namespace = $reflection->getNamespaceName();
        $parts = explode('\\', $namespace);
        
        // 查找 Hooks 后的部分作为分组
        $hookIndex = array_search('Hooks', $parts);
        if ($hookIndex !== false && isset($parts[$hookIndex + 1])) {
            return strtolower($parts[$hookIndex + 1]);
        }

        return null;
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
}
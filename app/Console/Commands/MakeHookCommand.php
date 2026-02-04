<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

/**
 * 钩子生成器命令
 */
class MakeHookCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'make:hook {name} {--template=basic} {--hook=} {--priority=10} {--group=} {--force} {--attribute} {--legacy}';

    /**
     * 命令描述
     */
    protected $description = '创建一个新的钩子类（支持 PHP 8.2 Attribute 和传统注释）';

    protected Filesystem $files;

    // 可用的模板
    protected array $templates = [
        'basic' => 'HookTemplate.php',
        'simple' => 'SimpleHookTemplate.php',
        'async' => 'AsyncHookTemplate.php',
        'conditional' => 'ConditionalHookTemplate.php',
        'batch' => 'BatchProcessingHookTemplate.php',
        'event' => 'EventDrivenHookTemplate.php',
        'cache' => 'CacheAwareHookTemplate.php',
        'validation' => 'ValidationHookTemplate.php',
        'view' => 'ViewHookTemplate.php',
        'view-composer' => 'ViewComposerHookTemplate.php',
    ];

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $template = $this->option('template');

        // 验证模板
        if (!isset($this->templates[$template])) {
            $this->error("模板 '{$template}' 不存在。");
            $this->info('可用模板: ' . implode(', ', array_keys($this->templates)));
            return 1;
        }

        // 生成钩子类
        $result = $this->generateHook($name, $template);

        if ($result) {
            $this->info("钩子类 {$name} 创建成功！");
            $this->info("文件位置: {$result}");
            $this->line('');
            $this->info('下一步:');
            $this->line('1. 编辑钩子类实现你的业务逻辑');
            $this->line('2. 运行 php artisan hook discover 注册钩子');
            $this->line('3. 使用 Hook::execute() 执行钩子');
            return 0;
        }

        return 1;
    }

    /**
     * 生成钩子类
     */
    protected function generateHook(string $name, string $template): ?string
    {
        $className = $this->getClassName($name);
        $filePath = $this->getFilePath($className);

        // 检查文件是否已存在
        if ($this->files->exists($filePath) && !$this->option('force')) {
            $this->error("钩子类 {$className} 已存在！");
            $this->info("使用 --force 选项覆盖现有文件。");
            return null;
        }

        // 确保目录存在
        $this->files->ensureDirectoryExists(dirname($filePath));

        // 生成文件内容
        $content = $this->generateContent($className, $template);

        // 写入文件
        $this->files->put($filePath, $content);

        return $filePath;
    }

    /**
     * 获取类名
     */
    protected function getClassName(string $name): string
    {
        $name = Str::studly($name);
        
        if (!Str::endsWith($name, 'Hook')) {
            $name .= 'Hook';
        }

        return $name;
    }

    /**
     * 获取文件路径
     */
    protected function getFilePath(string $className): string
    {
        return app_path("Hooks/Custom/{$className}.php");
    }

    /**
     * 生成文件内容
     */
    protected function generateContent(string $className, string $template): string
    {
        $templateFile = app_path("Hooks/Templates/{$this->templates[$template]}");
        
        if (!$this->files->exists($templateFile)) {
            throw new \RuntimeException("模板文件不存在: {$templateFile}");
        }

        $content = $this->files->get($templateFile);

        // 替换模板内容
        $replacements = $this->getReplacements($className, $template);
        
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }

    /**
     * 获取替换内容
     */
    protected function getReplacements(string $className, string $template): array
    {
        $hookName = $this->option('hook') ?: $this->generateHookName($className);
        $priority = $this->option('priority') ?: 10;
        $group = $this->option('group') ?: 'custom';
        $description = $this->generateDescription($className);

        // 检查是否使用 Attribute 语法
        $useAttribute = $this->shouldUseAttribute();

        $replacements = [
            'namespace App\Hooks\Templates;' => 'namespace App\Hooks\Custom;',
        ];

        // 根据模板类型进行不同的替换
        switch ($template) {
            case 'basic':
                $replacements = array_merge($replacements, [
                    'class HookTemplate' => "class {$className}",
                    'your.hook.name' => $hookName,
                    'your_group' => $group,
                    '钩子描述' => $description,
                ]);
                break;

            case 'simple':
                $replacements = array_merge($replacements, [
                    'class SimpleHookTemplate' => "class {$className}",
                    'simple.hook.name' => $hookName,
                    'simple' => $group,
                    '简单钩子模板' => $description,
                ]);
                break;

            case 'async':
                $replacements = array_merge($replacements, [
                    'class AsyncHookTemplate' => "class {$className}",
                    'async.hook.name' => $hookName,
                    'async' => $group,
                    '异步处理钩子模板' => $description,
                ]);
                break;

            case 'conditional':
                $replacements = array_merge($replacements, [
                    'class ConditionalHookTemplate' => "class {$className}",
                    'conditional.hook.name' => $hookName,
                    'conditional' => $group,
                    '条件处理钩子模板' => $description,
                ]);
                break;

            case 'batch':
                $replacements = array_merge($replacements, [
                    'class BatchProcessingHookTemplate' => "class {$className}",
                    'batch.processing.hook' => $hookName,
                    'batch' => $group,
                    '批量处理钩子模板' => $description,
                ]);
                break;

            case 'event':
                $replacements = array_merge($replacements, [
                    'class EventDrivenHookTemplate' => "class {$className}",
                    'event.driven.hook' => $hookName,
                    'event' => $group,
                    '事件驱动钩子模板' => $description,
                ]);
                break;

            case 'cache':
                $replacements = array_merge($replacements, [
                    'class CacheAwareHookTemplate' => "class {$className}",
                    'cache.aware.hook' => $hookName,
                    'cache' => $group,
                    '缓存感知钩子模板' => $description,
                ]);
                break;

            case 'validation':
                $replacements = array_merge($replacements, [
                    'class ValidationHookTemplate' => "class {$className}",
                    'validation.hook' => $hookName,
                    'validation' => $group,
                    '数据验证钩子模板' => $description,
                ]);
                break;

            case 'view':
                $replacements = array_merge($replacements, [
                    'class ViewHookTemplate' => "class {$className}",
                    'view.hook.name' => $hookName,
                    'view' => $group,
                    '视图处理钩子模板' => $description,
                ]);
                break;

            case 'view-composer':
                $replacements = array_merge($replacements, [
                    'class ViewComposerHookTemplate' => "class {$className}",
                    'view.composer.hook' => $hookName,
                    'view' => $group,
                    '视图组合器钩子模板' => $description,
                ]);
                break;
        }

        // 处理 Attribute 或传统注释
        if ($useAttribute) {
            $replacements = $this->addAttributeReplacements($replacements, $hookName, $priority, $group, $description);
        } else {
            $replacements = $this->addLegacyReplacements($replacements, $hookName, $priority, $group);
        }

        return $replacements;
    }

    /**
     * 判断是否应该使用 Attribute 语法
     */
    protected function shouldUseAttribute(): bool
    {
        // 如果明确指定了 --legacy，使用传统注释
        if ($this->option('legacy')) {
            return false;
        }

        // 如果明确指定了 --attribute，使用 Attribute
        if ($this->option('attribute')) {
            return true;
        }

        // 默认：如果 PHP 版本支持 Attribute，则使用 Attribute
        return PHP_VERSION_ID >= 80200;
    }

    /**
     * 添加 Attribute 相关的替换
     */
    protected function addAttributeReplacements(array $replacements, string $hookName, int $priority, string $group, string $description): array
    {
        // 确保导入 Attribute 类
        $attributeImports = [
            'use App\Hooks\Attributes\Hook;',
            'use App\Hooks\Attributes\Priority;',
            'use App\Hooks\Attributes\Group;',
        ];

        // 生成 Attribute 语法
        $attributeCode = "#[Hook(\n    name: '{$hookName}',\n    priority: {$priority},\n    group: '{$group}',\n    description: '{$description}'\n)]";

        // 添加导入语句
        foreach ($attributeImports as $import) {
            if (!str_contains($replacements['namespace App\Hooks\Templates;'] ?? '', $import)) {
                $replacements['use App\Hooks\AbstractHook;'] = $import . "\nuse App\Hooks\AbstractHook;";
            }
        }

        return $replacements;
    }

    /**
     * 添加传统注释相关的替换
     */
    protected function addLegacyReplacements(array $replacements, string $hookName, int $priority, string $group): array
    {
        $replacements["@priority 10"] = "@priority {$priority}";
        
        return $replacements;
    }

    /**
     * 生成钩子名称
     */
    protected function generateHookName(string $className): string
    {
        // 移除 Hook 后缀
        $name = Str::replaceLast('Hook', '', $className);
        
        // 转换为蛇形命名
        return Str::snake($name, '.');
    }

    /**
     * 生成描述
     */
    protected function generateDescription(string $className): string
    {
        $name = Str::replaceLast('Hook', '', $className);
        return Str::title(str_replace('_', ' ', Str::snake($name))) . '钩子';
    }

    /**
     * 显示帮助信息
     */
    public function getHelp(): string
    {
        return <<<'HELP'
创建钩子类命令

用法:
  php artisan make:hook <name> [选项]

参数:
  name                  钩子类名称

选项:
  --template=TEMPLATE   使用的模板 (默认: basic)
  --hook=HOOK          钩子名称 (默认: 根据类名生成)
  --priority=PRIORITY   优先级 (默认: 10)
  --group=GROUP        分组 (默认: custom)
  --force              覆盖现有文件

可用模板:
  basic        基础模板 (完整功能)
  simple       简单模板 (最小实现)
  async        异步处理模板
  conditional  条件处理模板
  batch        批量处理模板
  event        事件驱动模板
  cache        缓存感知模板
  validation   验证模板
  view         视图处理模板
  view-composer 视图组合器模板

示例:
  php artisan make:hook UserLogin
  php artisan make:hook OrderProcess --template=async
  php artisan make:hook DataValidator --template=validation --group=validation
  php artisan make:hook BatchImport --template=batch --hook=data.import.batch
  php artisan make:hook ViewProcessor --template=view --group=view
  php artisan make:hook MenuComposer --template=view-composer --group=view

HELP;
    }
}
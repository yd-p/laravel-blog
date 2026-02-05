# 视图生命周期系统

## 概述

视图生命周期系统允许插件和主题在视图渲染的不同阶段介入，实现数据注入、内容修改、事件监听等功能。

## 核心概念

### 生命周期阶段

1. **view.creating** - 视图实例创建时
2. **view.composing** - 视图组合时（渲染前数据准备）
3. **view.before_render** - 视图渲染前
4. **view.after_render** - 视图渲染后

### 架构组件

- **ViewLifecycleService** - 生命周期服务核心
- **ViewHookManager** - 视图钩子管理器
- **Hook System** - 底层钩子系统

## 快速开始

### 1. 在插件中注册生命周期钩子

```php
<?php

namespace Plugins\YourPlugin\Hooks;

use App\Services\ViewLifecycleService;
use App\Hooks\Facades\Hook;

class YourPluginViewHooks
{
    protected ViewLifecycleService $lifecycle;

    public function __construct(ViewLifecycleService $lifecycle)
    {
        $this->lifecycle = $lifecycle;
    }

    public function register(): void
    {
        // 方式1: 使用 ViewLifecycleService
        $this->lifecycle->registerLifecycleHook(
            'view.composing',
            'posts.*',
            function ($viewName, $data) {
                return [
                    'data' => [
                        'plugin_data' => 'value',
                    ]
                ];
            },
            10 // 优先级
        );

        // 方式2: 使用 Hook Facade
        Hook::register('view.composing', function ($viewName, $data) {
            if (str_starts_with($viewName, 'posts.')) {
                return [
                    'data' => [
                        'latest_posts' => Post::latest()->take(5)->get(),
                    ]
                ];
            }
        }, 10, 'your-plugin');
    }
}
```

### 2. 在服务提供者中注册

```php
<?php

namespace Plugins\YourPlugin\Providers;

use Illuminate\Support\ServiceProvider;
use Plugins\YourPlugin\Hooks\YourPluginViewHooks;

class YourPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(YourPluginViewHooks::class);
    }

    public function boot(): void
    {
        $hooks = $this->app->make(YourPluginViewHooks::class);
        $hooks->register();
    }
}
```

### 3. 在视图中使用钩子

```blade
{{-- 使用插件钩子 --}}
@plugin_hook('post.before_content')

<article>
    <h1>{{ $post->title }}</h1>
    <div>{!! $post->content !!}</div>
</article>

@plugin_hook('post.after_content')

{{-- 使用通用钩子 --}}
@hook('custom.hook.name', ['data' => $someData])
```

## 详细用法

### 视图模式匹配

支持通配符模式匹配视图名称：

```php
// 匹配所有视图
$lifecycle->registerLifecycleHook('view.composing', '*', $callback);

// 匹配特定前缀
$lifecycle->registerLifecycleHook('view.composing', 'posts.*', $callback);

// 匹配特定视图
$lifecycle->registerLifecycleHook('view.composing', 'posts.show', $callback);

// 匹配多级路径
$lifecycle->registerLifecycleHook('view.composing', 'admin.posts.*', $callback);
```

### 数据注入

在视图渲染前注入数据：

```php
Hook::register('view.composing', function ($viewName, $data) {
    return [
        'data' => [
            'site_name' => config('app.name'),
            'current_user' => auth()->user(),
            'notifications' => Notification::unread()->get(),
        ]
    ];
}, 10, 'global-data');
```

### 内容修改

修改渲染后的内容：

```php
Hook::register('view.after_render', function ($viewName, $data, $options) {
    $content = $options['rendered_content'] ?? '';
    
    // 添加分析代码
    $analyticsCode = '<script>/* analytics */</script>';
    $content = str_replace('</body>', $analyticsCode . '</body>', $content);
    
    return ['modified_content' => $content];
}, 10, 'analytics');
```

### 条件执行

根据条件执行钩子：

```php
Hook::register('view.composing', function ($viewName, $data) {
    // 只在前台执行
    if (!request()->is('admin/*')) {
        return [
            'data' => [
                'frontend_data' => 'value',
            ]
        ];
    }
}, 10, 'frontend-only');
```

### 优先级控制

使用优先级控制执行顺序（数字越大优先级越高）：

```php
// 高优先级 - 先执行
$lifecycle->registerLifecycleHook('view.composing', '*', $callback1, 20);

// 中优先级
$lifecycle->registerLifecycleHook('view.composing', '*', $callback2, 10);

// 低优先级 - 后执行
$lifecycle->registerLifecycleHook('view.composing', '*', $callback3, 5);
```

## 实际应用场景

### 场景1: 全局数据注入

为所有视图注入通用数据：

```php
public function registerGlobalData(): void
{
    Hook::register('view.composing', function ($viewName, $data) {
        return [
            'data' => [
                'app_name' => config('app.name'),
                'app_version' => config('app.version'),
                'current_theme' => app('theme')->getCurrentTheme(),
                'menu_items' => Menu::active()->get(),
            ]
        ];
    }, 5, 'global-data');
}
```

### 场景2: 阅读统计

自动统计文章阅读量：

```php
public function registerViewCounter(): void
{
    $this->lifecycle->registerLifecycleHook(
        'view.before_render',
        'posts.show',
        function ($viewName, $data) {
            if (isset($data['post'])) {
                $data['post']->increment('view_count');
            }
            return $data;
        },
        15
    );
}
```

### 场景3: SEO优化

自动添加SEO元标签：

```php
public function registerSeoTags(): void
{
    Hook::register('view.composing', function ($viewName, $data) {
        if (str_starts_with($viewName, 'posts.show')) {
            $post = $data['post'] ?? null;
            if ($post) {
                return [
                    'data' => [
                        'seo_title' => $post->seo_title ?: $post->title,
                        'seo_description' => $post->seo_description ?: $post->excerpt,
                        'seo_keywords' => $post->seo_keywords,
                        'og_image' => $post->thumbnail,
                    ]
                ];
            }
        }
    }, 10, 'seo');
}
```

### 场景4: 侧边栏小部件

动态添加侧边栏内容：

```php
public function registerSidebarWidgets(): void
{
    Hook::register('plugin.sidebar', function ($viewName, $data) {
        $html = '<div class="widget">';
        $html .= '<h3>热门文章</h3>';
        $html .= '<ul>';
        
        $posts = Post::orderBy('view_count', 'desc')->take(5)->get();
        foreach ($posts as $post) {
            $html .= '<li><a href="/posts/' . $post->slug . '">' . $post->title . '</a></li>';
        }
        
        $html .= '</ul></div>';
        return $html;
    }, 10, 'popular-posts-widget');
}
```

### 场景5: 内容过滤

过滤和处理内容：

```php
public function registerContentFilter(): void
{
    Hook::register('view.composing', function ($viewName, $data) {
        if (isset($data['post'])) {
            $post = $data['post'];
            
            // 处理短代码
            $post->content = $this->processShortcodes($post->content);
            
            // 添加目录
            $post->content = $this->addTableOfContents($post->content);
            
            return ['data' => ['post' => $post]];
        }
    }, 10, 'content-filter');
}
```

## Blade 指令

### @plugin_hook

在视图中插入插件钩子点：

```blade
{{-- 基本用法 --}}
@plugin_hook('post.before_content')

{{-- 钩子会执行并输出返回的内容 --}}
```

### @hook

执行通用钩子：

```blade
{{-- 执行钩子但不输出 --}}
@hook('custom.event', ['data' => $value])
```

### @lifecycle

执行生命周期钩子：

```blade
{{-- 执行特定生命周期阶段的钩子 --}}
@lifecycle('before_render', 'posts.*')
```

## API 参考

### ViewLifecycleService

#### registerLifecycleHook()

注册生命周期钩子：

```php
public function registerLifecycleHook(
    string $lifecycle,    // 生命周期阶段
    string $pattern,      // 视图名称模式
    callable $callback,   // 回调函数
    int $priority = 10    // 优先级
): string                 // 返回钩子ID
```

#### executeLifecycleHooks()

执行生命周期钩子：

```php
public function executeLifecycleHooks(
    string $lifecycle,    // 生命周期阶段
    string $viewName,     // 视图名称
    array $data = []      // 视图数据
): array                  // 返回执行结果
```

#### removeLifecycleHook()

移除生命周期钩子：

```php
public function removeLifecycleHook(
    string $lifecycle,    // 生命周期阶段
    string $hookId        // 钩子ID
): bool                   // 是否成功
```

### Hook Facade

#### register()

注册钩子：

```php
Hook::register(
    string $name,         // 钩子名称
    callable $callback,   // 回调函数
    int $priority = 10,   // 优先级
    string $group = null  // 分组
): string                 // 返回钩子ID
```

#### execute()

执行钩子：

```php
Hook::execute(
    string $name,         // 钩子名称
    mixed ...$args        // 参数
): HookResult             // 返回执行结果
```

## 最佳实践

### 1. 使用命名空间

为插件钩子使用明确的命名空间：

```php
// 好的做法
Hook::register('plugin.yourplugin.before_content', $callback);

// 避免
Hook::register('before_content', $callback);
```

### 2. 设置合理的优先级

- 数据准备: 5-10
- 业务逻辑: 10-15
- 内容修改: 15-20
- 最终处理: 20+

### 3. 错误处理

在钩子中添加错误处理：

```php
Hook::register('view.composing', function ($viewName, $data) {
    try {
        // 你的逻辑
        return ['data' => $result];
    } catch (\Exception $e) {
        logger()->error('Hook error', [
            'hook' => 'view.composing',
            'error' => $e->getMessage(),
        ]);
        return [];
    }
}, 10, 'your-plugin');
```

### 4. 性能优化

避免在钩子中执行重量级操作：

```php
// 不好的做法
Hook::register('view.composing', function ($viewName, $data) {
    // 每次都查询数据库
    $posts = Post::all();
    return ['data' => ['posts' => $posts]];
});

// 好的做法
Hook::register('view.composing', function ($viewName, $data) {
    // 使用缓存
    $posts = cache()->remember('all_posts', 3600, function () {
        return Post::all();
    });
    return ['data' => ['posts' => $posts]];
});
```

### 5. 条件注册

只在需要时注册钩子：

```php
public function register(): void
{
    // 只在前台注册
    if (!request()->is('admin/*')) {
        $this->registerFrontendHooks();
    }
    
    // 只在特定路由注册
    if (request()->routeIs('posts.*')) {
        $this->registerPostHooks();
    }
}
```

## 调试

### 查看已注册的钩子

```php
// 获取所有视图钩子
$viewHooks = app(\App\Hooks\View\ViewHookManager::class)->getViewHookStats();
dd($viewHooks);

// 获取特定生命周期的钩子
$lifecycle = app('view.lifecycle');
$hooks = $lifecycle->getLifecycleHooks('view.composing');
dd($hooks);
```

### 日志记录

在钩子中添加日志：

```php
Hook::register('view.composing', function ($viewName, $data) {
    logger()->debug('View composing', [
        'view' => $viewName,
        'data_keys' => array_keys($data),
    ]);
    
    // 你的逻辑
}, 10, 'debug');
```

## 与其他系统集成

### 与主题系统集成

```php
// 主题自动注入配置
Hook::register('view.composing', function ($viewName, $data) {
    return [
        'data' => [
            'theme_config' => app('theme')->getThemeConfig(),
            'theme_colors' => app('theme')->getColors(),
        ]
    ];
}, 5, 'theme-integration');
```

### 与插件系统集成

```php
// 插件可以注册自己的钩子点
class YourPlugin
{
    public function boot(): void
    {
        // 注册插件特定的钩子点
        Hook::register('plugin.yourplugin.init', function () {
            // 初始化逻辑
        });
    }
}
```

## 故障排除

### 钩子未执行

1. 检查钩子名称是否正确
2. 检查视图模式是否匹配
3. 检查优先级设置
4. 查看日志文件

### 数据未注入

1. 确认返回格式正确：`['data' => [...]]`
2. 检查钩子执行顺序
3. 验证视图名称匹配

### 性能问题

1. 使用缓存减少数据库查询
2. 避免在钩子中执行重量级操作
3. 使用条件注册减少不必要的钩子

## 相关文档

- [钩子系统](03-hook-system.md)
- [插件系统](04-plugin-system.md)
- [主题系统](02-theme-system.md)

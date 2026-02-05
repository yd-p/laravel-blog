# 视图生命周期快速参考

## 生命周期阶段

```
view.creating → view.composing → view.before_render → [渲染] → view.after_render
```

## 快速注册钩子

### 方式1: ViewLifecycleService

```php
use App\Services\ViewLifecycleService;

$lifecycle = app(ViewLifecycleService::class);

$lifecycle->registerLifecycleHook(
    'view.composing',      // 生命周期阶段
    'posts.*',             // 视图模式
    function ($viewName, $data) {
        return ['data' => ['key' => 'value']];
    },
    10                     // 优先级
);
```

### 方式2: Hook Facade

```php
use App\Hooks\Facades\Hook;

Hook::register('view.composing', function ($viewName, $data) {
    if (str_starts_with($viewName, 'posts.')) {
        return ['data' => ['key' => 'value']];
    }
}, 10, 'group-name');
```

## Blade 指令

### @plugin_hook

```blade
{{-- 插件钩子点 --}}
@plugin_hook('post.before_content')

{{-- 输出钩子返回的内容 --}}
```

### @hook

```blade
{{-- 执行钩子（不输出） --}}
@hook('custom.event', ['data' => $value])
```

### @lifecycle

```blade
{{-- 执行生命周期钩子 --}}
@lifecycle('before_render', 'posts.*')
```

## 常用模式

### 数据注入

```php
Hook::register('view.composing', function ($viewName, $data) {
    return [
        'data' => [
            'site_name' => config('app.name'),
            'user' => auth()->user(),
        ]
    ];
});
```

### 内容修改

```php
Hook::register('view.after_render', function ($viewName, $data, $options) {
    $content = $options['rendered_content'] ?? '';
    // 修改内容
    return ['modified_content' => $content];
});
```

### 条件执行

```php
Hook::register('view.composing', function ($viewName, $data) {
    if (request()->is('admin/*')) {
        return ['data' => ['admin_data' => 'value']];
    }
});
```

## 优先级

- 5-10: 数据准备
- 10-15: 业务逻辑
- 15-20: 内容修改
- 20+: 最终处理

## 插件集成示例

```php
// 在插件服务提供者中
public function boot(): void
{
    $lifecycle = app(ViewLifecycleService::class);
    
    // 注册钩子
    $lifecycle->registerLifecycleHook(
        'view.composing',
        'posts.*',
        [$this, 'injectPostData'],
        10
    );
}

public function injectPostData($viewName, $data)
{
    return [
        'data' => [
            'latest_posts' => Post::latest()->take(5)->get(),
        ]
    ];
}
```

## 调试

```php
// 查看所有钩子
$hooks = app('view.lifecycle')->getLifecycleHooks();
dd($hooks);

// 查看视图钩子统计
$stats = app(\App\Hooks\View\ViewHookManager::class)->getViewHookStats();
dd($stats);
```

## 完整文档

查看 [视图生命周期完整文档](../../docs/06-view-lifecycle.md)

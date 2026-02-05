# 钩子系统框架 - 快速开始指南

## 🎯 重要说明

**这是一个纯框架系统！** 系统只提供钩子管理的基础设施，**所有业务逻辑都需要你自己实现**。

## 🚀 5分钟快速上手

### 1. 基础设置

```bash
# 发布配置和迁移文件
php artisan vendor:publish --tag=hooks-config
php artisan vendor:publish --tag=hooks-migrations

# 运行迁移
php artisan migrate

# 发现钩子（初次运行可能没有钩子）
php artisan hook discover
```

### 2. 创建你的第一个钩子

#### 方法1：使用生成器命令（推荐）

```bash
# 创建基础钩子（自动选择注解语法）
php artisan make:hook MyFirstHook

# 创建异步处理钩子
php artisan make:hook DataProcessor --template=async

# 创建验证钩子
php artisan make:hook UserValidator --template=validation --group=validation

# 强制使用 PHP 8.2 Attribute 语法
php artisan make:hook ModernHook --attribute

# 强制使用传统注释语法
php artisan make:hook LegacyHook --legacy

# 查看所有可用模板
php artisan make:hook --help
```

#### 方法2：手动复制模板

```bash
# 复制基础模板
cp app/Hooks/Templates/HookTemplate.php app/Hooks/Custom/MyFirstHook.php
```

#### PHP 8.2 Attribute 语法（推荐）

```php
<?php

namespace App\Hooks\Custom;

use App\Hooks\AbstractHook;
use App\Hooks\Attributes\Hook;

/**
 * 使用 PHP 8.2 Attribute 语法
 */
#[Hook(
    name: 'my.first.hook',
    priority: 10,
    group: 'demo',
    description: '我的第一个钩子'
)]
class MyFirstHook extends AbstractHook
{
    protected string $description = '我的第一个钩子';

    public function handle(...$args)
    {
        [$message] = $args;
        
        // TODO: 在这里实现你的业务逻辑
        logger()->info("钩子执行: {$message}");
        
        return [
            'status' => 'success',
            'message' => "处理了消息: {$message}",
            'timestamp' => now()
        ];
    }
}
```

#### 传统注释语法（向后兼容）

然后编辑 `app/Hooks/Custom/MyFirstHook.php`：

```php
<?php

namespace App\Hooks\Custom;

use App\Hooks\AbstractHook;

/**
 * 使用传统注释语法
 * 
 * @hook my.first.hook
 * @priority 10
 * @group demo
 */
class MyFirstHook extends AbstractHook
{
    protected string $description = '我的第一个钩子';

    public function handle(...$args)
    {
        [$message] = $args;
        
        // TODO: 在这里实现你的业务逻辑
        logger()->info("钩子执行: {$message}");
        
        return [
            'status' => 'success',
            'message' => "处理了消息: {$message}",
            'timestamp' => now()
        ];
    }
}
```

### 3. 注册并使用钩子

```bash
# 发现并注册钩子
php artisan hook discover
```

在代码中使用：
```php
use App\Hooks\Facades\Hook;

// 执行钩子
$result = Hook::execute('my.first.hook', 'Hello World');

if ($result->isSuccessful()) {
    $data = $result->getFirstResult();
    echo $data['message']; // 输出: 处理了消息: Hello World
}
```

### 4. 命令行管理

```bash
# 查看所有钩子
php artisan hook list

# 查看统计信息
php artisan hook stats

# 测试钩子
php artisan hook test --hook=my.first.hook
```

## 📝 常用场景（用户需要实现业务逻辑）

### 用户登录钩子

```php
// 在 AuthController 中触发钩子
public function login(Request $request)
{
    // 登录逻辑...
    
    if ($user = Auth::attempt($credentials)) {
        // 执行登录后钩子（用户需要实现具体的钩子逻辑）
        Hook::execute('user.login.after', $user, $request->ip(), $request->userAgent());
        
        return redirect()->intended();
    }
}
```

然后创建钩子类 `app/Hooks/Custom/UserLoginHook.php`：
```php
/**
 * @hook user.login.after
 * @priority 10
 * @group auth
 */
class UserLoginHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$user, $ip, $userAgent] = $args;
        
        // TODO: 实现你的登录后处理逻辑
        // 例如：记录登录日志、发送通知、更新统计等
        
        return ['processed' => true];
    }
}
```

### 模型事件钩子

```php
// 在 User 模型中触发钩子
protected static function boot()
{
    parent::boot();
    
    static::created(function ($user) {
        Hook::execute('user.created', $user);
    });
    
    static::updated(function ($user) {
        Hook::execute('user.updated', $user, $user->getChanges());
    });
}
```

然后创建钩子类处理这些事件：
```php
/**
 * @hook user.created
 * @priority 10
 * @group user
 */
class UserCreatedHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$user] = $args;
        
        // TODO: 实现用户创建后的处理逻辑
        // 例如：发送欢迎邮件、创建默认设置、记录审计日志等
        
        return ['user_id' => $user->id, 'processed' => true];
    }
}
```

### 视图钩子

```php
// 在控制器中使用视图钩子
use App\Hooks\View\ViewHookManager;

class DashboardController extends Controller
{
    public function index()
    {
        $data = ['stats' => $this->getStats()];
        
        // 执行视图渲染前钩子
        $viewHookManager = app(ViewHookManager::class);
        $beforeResults = $viewHookManager->executeBeforeRender('dashboard.index', $data);
        
        // 合并钩子处理的数据
        foreach ($beforeResults as $results) {
            foreach ($results as $result) {
                if (isset($result['processed_data'])) {
                    $data = array_merge($data, $result['processed_data']);
                }
            }
        }
        
        return view('dashboard.index', $data);
    }
}
```

然后创建视图钩子类：
```php
/**
 * @hook view.dashboard.before_render
 * @priority 10
 * @group view
 */
class DashboardViewHook extends ViewHookTemplate
{
    protected function handleBeforeRender(string $viewName, array $data, array $options): array
    {
        // TODO: 实现仪表板视图前置处理逻辑
        // 例如：添加导航菜单、注入用户权限、加载小组件数据等
        
        return [
            'processed_data' => array_merge($data, [
                'navigation_menu' => $this->getNavigationMenu(),
                'user_permissions' => $this->getUserPermissions(),
                'dashboard_widgets' => $this->getDashboardWidgets()
            ])
        ];
    }
}
```

在Blade模板中使用视图钩子指令：
```blade
{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('content')
    {{-- 执行渲染前钩子 --}}
    @hookBefore('dashboard.widgets')
    
    <div class="dashboard">
        <h1>仪表板</h1>
        
        {{-- 注入钩子数据 --}}
        @hookData('dashboard.stats', ['refresh' => true])
        
        {{-- 条件钩子 --}}
        @ifhook('feature.advanced_dashboard')
            <div class="advanced-widgets">
                <!-- 高级功能组件 -->
            </div>
        @endifhook
    </div>
    
    {{-- 执行渲染后钩子 --}}
    @hookAfter('dashboard.widgets')
@endsection
```

## 🎯 进阶用法（框架功能演示）

### 条件执行

```php
// 注册条件钩子（用户需要实现具体的条件判断逻辑）
Hook::register('order.created', function ($order) {
    // TODO: 用户实现订单创建后的处理逻辑
    if ($order->amount > 1000) {
        // 高价值订单特殊处理
        return ['action' => 'high_value_notification', 'order_id' => $order->id];
    }
    return ['action' => 'normal_processing', 'order_id' => $order->id];
});
```

### 中间件验证

```php
// 添加权限验证中间件（用户需要实现具体的权限检查逻辑）
Hook::addMiddleware('admin.*', function ($hookName, $hookId, $args) {
    // TODO: 用户实现权限检查逻辑
    return true; // 或 false 来控制是否执行钩子
});
```

### 批量注册

```php
$userHooks = [
    'user.login.after' => [
        'callback' => UserLoginHook::class,  // 用户需要实现这个类
        'priority' => 10,
        'group' => 'auth'
    ],
    'user.logout.after' => [
        'callback' => UserLogoutHook::class, // 用户需要实现这个类
        'priority' => 10,
        'group' => 'auth'
    ]
];

Hook::registerBatch($userHooks);
```

## 🛠️ 调试技巧

### 1. 启用日志

```php
// config/hooks.php
'log_execution' => true,
'log_level' => 'debug',
```

### 2. 性能监控

```php
// config/hooks.php
'performance_monitoring' => true,
'performance_threshold' => 50, // 50ms
```

### 3. 查看执行结果

```php
$result = Hook::execute('my.hook', $data);

// 详细信息
echo "执行数量: " . $result->getExecutedCount() . "\n";
echo "执行时间: " . ($result->getExecutionTime() * 1000) . " ms\n";
echo "成功率: " . $result->getSuccessRate() . "%\n";

// 错误信息
if ($result->hasErrors()) {
    foreach ($result->getErrors() as $hookId => $error) {
        echo "错误 [{$hookId}]: {$error['error']}\n";
    }
}
```

## 🔧 配置优化

### 生产环境配置

```php
// config/hooks.php
return [
    'auto_discovery' => false,      // 生产环境关闭自动发现
    'cache_enabled' => true,        // 启用缓存
    'cache_ttl' => 24,             // 缓存24小时
    'log_execution' => false,       // 关闭执行日志
    'performance_monitoring' => true, // 保留性能监控
];
```

### 缓存预热

```bash
# 预热钩子缓存
php artisan hook discover

# 清除缓存（如果需要）
php artisan hook clear-cache
```

## 📚 更多资源

- [完整文档](README.md)
- [用户实现指南](USER_GUIDE.md)
- [视图钩子指南](VIEW_HOOKS_GUIDE.md)
- [模板文档](Templates/README.md)
- [API参考](HookManager.php)
- [示例代码](Examples/)
- [测试用例](Tests/)

## 🤔 常见问题

### Q: 钩子没有执行？
A: 
1. 检查钩子是否已注册：`php artisan hook list`
2. 检查钩子是否被禁用
3. 检查中间件是否阻止了执行
4. 确保已运行 `php artisan hook discover`

### Q: 如何实现具体的业务逻辑？
A: 
1. 在 `app/Hooks/Custom/` 目录下创建钩子类
2. 实现 `handle()` 方法中的业务逻辑
3. 使用注解标记钩子信息
4. 运行 `php artisan hook discover` 注册钩子

### Q: 性能问题？
A: 
1. 启用缓存：`'cache_enabled' => true`
2. 避免在钩子中执行耗时操作
3. 使用队列处理重型任务
4. 监控钩子执行时间

### Q: 如何调试钩子？
A: 
1. 启用日志记录：`'log_execution' => true`
2. 使用 `php artisan hook test --hook=钩子名称`
3. 检查执行结果的错误信息
4. 在钩子中添加调试日志

### Q: 框架提供了哪些业务逻辑？
A: **框架不提供任何业务逻辑！** 所有业务逻辑都需要用户自己在 `app/Hooks/Custom/` 目录下实现。

---

🎉 恭喜！你已经掌握了钩子系统框架的基础用法。现在可以开始在 `app/Hooks/Custom/` 目录下实现你的业务钩子了！

**记住：这是一个纯框架系统，所有业务逻辑都由你来定义和实现！**

## 📚 可用模板

系统提供了8种不同的钩子模板，适用于各种场景：

### 基础模板
- **basic** - 完整功能模板（推荐新手）
- **simple** - 简单模板（最小实现）

### 专业模板
- **async** - 异步处理模板（耗时操作）
- **conditional** - 条件处理模板（多分支逻辑）
- **batch** - 批量处理模板（大数据处理）
- **event** - 事件驱动模板（事件系统集成）
- **cache** - 缓存感知模板（性能优化）
- **validation** - 验证模板（数据验证）
- **view** - 视图处理模板（视图生命周期管理）
- **view-composer** - 视图组合器模板（视图数据共享）

### 快速创建示例

```bash
# 用户登录处理钩子
php artisan make:hook UserLogin --hook=user.login.after --group=auth

# 数据验证钩子
php artisan make:hook OrderValidator --template=validation --group=validation

# 异步文件处理钩子
php artisan make:hook FileProcessor --template=async --group=file

# 批量数据导入钩子
php artisan make:hook DataImporter --template=batch --group=import

# 缓存计算钩子
php artisan make:hook Calculator --template=cache --group=compute

# 视图处理钩子
php artisan make:hook ViewProcessor --template=view --group=view

# 视图组合器钩子
php artisan make:hook MenuComposer --template=view-composer --group=view
```

查看所有模板详情：[模板文档](app/Hooks/Templates/README.md)

## 🎯 模板选择建议

| 场景 | 推荐模板 | 命令示例 |
|------|----------|----------|
| 用户认证处理 | basic/simple | `make:hook UserAuth --group=auth` |
| 数据验证 | validation | `make:hook DataValidator --template=validation` |
| 文件处理 | async | `make:hook FileProcessor --template=async` |
| 批量导入 | batch | `make:hook DataImporter --template=batch` |
| 复杂业务规则 | conditional | `make:hook BusinessRule --template=conditional` |
| 性能敏感操作 | cache | `make:hook Calculator --template=cache` |
| 事件驱动架构 | event | `make:hook EventHandler --template=event` |
| 视图数据处理 | view | `make:hook ViewProcessor --template=view` |
| 视图组合器 | view-composer | `make:hook MenuComposer --template=view-composer` |

## 🎨 视图钩子特别说明

视图钩子是专门为Laravel视图系统设计的钩子，提供了强大的视图处理能力：

### 视图钩子类型

```bash
# 视图处理钩子 - 完整的视图生命周期管理
php artisan make:hook ViewProcessor --template=view --group=view

# 视图组合器钩子 - 视图数据共享和组合
php artisan make:hook MenuComposer --template=view-composer --group=view
```

### 视图钩子使用场景

#### 1. 视图数据预处理
```php
// 在控制器中触发
Hook::execute('view.before_render', 'admin.dashboard', $data);
```

#### 2. 全局数据注入
```php
// 为所有视图注入数据
Hook::execute('view.inject_data', '*', $globalData);
```

#### 3. 主题和布局切换
```php
// 动态切换主题
Hook::execute('view.switch_theme', $viewName, $data, ['theme' => 'dark']);
```

#### 4. 视图组合器
```php
// 注册导航菜单组合器
Hook::execute('view.composer', 'layouts.navigation', $menuData);
```

### Blade指令支持

视图钩子系统还提供了Blade指令支持：

```blade
{{-- 在模板中执行钩子 --}}
@hook('view.custom.processing', $data)

{{-- 注入数据到视图 --}}
@hookData('user.profile', ['user_id' => $user->id])

{{-- 条件钩子 --}}
@ifhook('feature.enabled')
    <div>功能已启用</div>
@endifhook
```

### 视图钩子管理器

使用专门的视图钩子管理器：

```php
use App\Hooks\View\ViewHookFacade as ViewHook;

// 注册视图钩子
ViewHook::beforeRender('admin.*', $callback);
ViewHook::afterRender('user.*', $callback);
ViewHook::injectData('*', $callback);
```

视图钩子让你可以在不修改控制器和视图文件的情况下，灵活地处理视图相关的业务逻辑！
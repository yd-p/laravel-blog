# 钩子系统完整指南

## 📖 目录

- [系统概述](#系统概述)
- [快速开始](#快速开始)
- [创建钩子](#创建钩子)
- [使用钩子](#使用钩子)
- [视图钩子](#视图钩子)
- [命令行工具](#命令行工具)
- [最佳实践](#最佳实践)

---

## 系统概述

钩子系统是一个**纯框架**，只提供钩子管理的基础设施，**所有业务逻辑都需要开发者自己实现**。

### 核心特性

✅ **纯框架设计** - 只提供基础设施  
✅ **PHP 8.2 Attribute** - 支持现代注解语法  
✅ **优先级控制** - 灵活的执行顺序  
✅ **中间件支持** - 拦截和验证机制  
✅ **自动发现** - 自动扫描和注册  
✅ **性能监控** - 执行时间追踪  

### 目录结构

```
app/Hooks/
├── Custom/                 # 🎯 用户自定义钩子目录
│   └── .gitkeep
├── Templates/              # 钩子模板（供参考）
├── View/                   # 视图钩子管理
├── HookManager.php         # 钩子管理器
└── AbstractHook.php        # 抽象基类
```

---

## 快速开始

### 1. 创建钩子

```bash
# 使用生成器命令（推荐）
php artisan make:hook MyFirstHook

# 使用特定模板
php artisan make:hook DataProcessor --template=async

# 指定钩子名称和分组
php artisan make:hook UserLogin --hook=user.login.after --group=auth
```

### 2. 实现钩子逻辑

使用 PHP 8.2 Attribute 语法（推荐）:

```php
<?php

namespace App\Hooks\Custom;

use App\Hooks\AbstractHook;
use App\Hooks\Attributes\Hook;

#[Hook(
    name: 'user.login.after',
    priority: 10,
    group: 'auth',
    description: '用户登录后处理'
)]
class UserLoginHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$user, $ip] = $args;
        
        // TODO: 实现你的业务逻辑
        logger()->info("用户登录", [
            'user_id' => $user->id,
            'ip' => $ip
        ]);
        
        return ['processed' => true];
    }
}
```

或使用传统注释语法:

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
        // 业务逻辑
        return ['processed' => true];
    }
}
```

### 3. 注册钩子

```bash
php artisan hook discover
```

### 4. 触发钩子

```php
use App\Hooks\Facades\Hook;

// 在控制器中
public function login(Request $request)
{
    if ($user = Auth::attempt($credentials)) {
        // 触发钩子
        Hook::execute('user.login.after', $user, $request->ip());
        
        return redirect()->intended();
    }
}
```

---

## 创建钩子

### 可用模板

| 模板 | 适用场景 | 命令示例 |
|------|----------|----------|
| **basic** | 通用场景 | `make:hook MyHook` |
| **simple** | 简单处理 | `make:hook MyHook --template=simple` |
| **async** | 异步处理 | `make:hook FileProcessor --template=async` |
| **conditional** | 条件处理 | `make:hook BusinessRule --template=conditional` |
| **batch** | 批量处理 | `make:hook DataImporter --template=batch` |
| **event** | 事件驱动 | `make:hook EventHandler --template=event` |
| **cache** | 缓存优化 | `make:hook Calculator --template=cache` |
| **validation** | 数据验证 | `make:hook Validator --template=validation` |
| **view** | 视图处理 | `make:hook ViewProcessor --template=view` |
| **view-composer** | 视图组合器 | `make:hook MenuComposer --template=view-composer` |

### PHP 8.2 Attribute

#### Hook Attribute

```php
#[Hook(
    name: 'user.login.after',      // 钩子名称（必需）
    priority: 10,                  // 优先级（可选）
    group: 'auth',                 // 分组（可选）
    description: '用户登录后处理',   // 描述（可选）
    enabled: true                  // 是否启用（可选）
)]
```

#### Middleware Attribute

```php
#[Middleware(class: 'App\Hooks\Middleware\AuthMiddleware')]
#[Middleware(
    class: 'App\Hooks\Middleware\LoggingMiddleware',
    parameters: ['level' => 'info']
)]
```

#### Condition Attribute

```php
// 环境条件
#[Condition(type: 'environment', value: 'production')]

// 认证条件
#[Condition(type: 'auth', value: true)]

// 角色条件
#[Condition(type: 'user_role', value: 'admin')]

// 时间条件
#[Condition(type: 'time', value: '09:00', operator: '>=')]
```

---

## 使用钩子

### 注册钩子

```php
use App\Hooks\Facades\Hook;

// 使用闭包
Hook::register('user.login', function ($user) {
    logger()->info("用户登录: {$user->email}");
});

// 使用类方法
Hook::register('user.login', [UserLoginHandler::class, 'handle'], 10, 'auth');

// 批量注册
Hook::registerBatch([
    'user.login' => function ($user) { /* ... */ },
    'user.logout' => function ($user) { /* ... */ },
], 'auth');
```

### 执行钩子

```php
// 执行钩子
$result = Hook::execute('user.login', $user, $request->ip());

// 检查结果
if ($result->isSuccessful()) {
    echo "执行了 {$result->getExecutedCount()} 个钩子";
    echo "执行时间: {$result->getExecutionTime()} 秒";
}

// 获取结果
$results = $result->getResults();
$firstResult = $result->getFirstResult();
```

### 管理钩子

```php
// 获取钩子列表
$hooks = Hook::getHooks('user.login');

// 启用/禁用钩子
Hook::toggle('user.login', $hookId, false);

// 移除钩子
Hook::remove('user.login', $hookId);
Hook::removeByGroup('auth');
```

---

## 视图钩子

视图钩子专门处理视图相关的逻辑。

### 注册视图钩子

```php
use App\Hooks\View\ViewHookManager;

$viewHookManager = app(ViewHookManager::class);

// 渲染前钩子
$viewHookManager->beforeRender('admin.*', function ($viewName, $data) {
    return [
        'processed_data' => array_merge($data, [
            'admin_menu' => $this->getAdminMenu()
        ])
    ];
});

// 数据注入钩子
$viewHookManager->injectData('*', function ($viewName, $data) {
    return [
        'injected_data' => [
            'app_name' => config('app.name'),
            'current_user' => auth()->user()
        ]
    ];
});
```

### 使用视图钩子模板

```php
use App\Hooks\Templates\ViewHookTemplate;

#[Hook(name: 'view.dashboard', group: 'view')]
class DashboardViewHook extends ViewHookTemplate
{
    protected function handleBeforeRender(string $viewName, array $data, array $options): array
    {
        // TODO: 实现渲染前逻辑
        return [
            'processed_data' => array_merge($data, [
                'widgets' => $this->getDashboardWidgets()
            ])
        ];
    }
}
```

### Blade 指令

```blade
{{-- 执行钩子 --}}
@hook('view.custom.widget', $data)

{{-- 注入数据 --}}
@hookData('dashboard.stats', ['refresh' => true])

{{-- 渲染前后钩子 --}}
@hookBefore('dashboard.widgets')
<div class="content">...</div>
@hookAfter('dashboard.widgets')

{{-- 条件钩子 --}}
@ifhook('feature.advanced')
    <div>高级功能</div>
@endifhook
```

---

## 命令行工具

```bash
# 列出所有钩子
php artisan hook list

# 按分组列出
php artisan hook list --group=auth

# 显示统计信息
php artisan hook stats

# 发现并注册钩子
php artisan hook discover

# 测试钩子
php artisan hook test --hook=user.login

# 清除缓存
php artisan hook clear-cache
```

---

## 最佳实践

### 1. 钩子命名规范

使用点号分隔的命名空间：

```
模块.操作.时机

例如:
user.login.before
user.login.after
order.created.after
```

### 2. 错误处理

```php
public function handle(...$args)
{
    try {
        // 钩子逻辑
        return $result;
    } catch (\Exception $e) {
        logger()->error("钩子执行失败", [
            'hook' => static::class,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
```

### 3. 参数验证

```php
protected function validateArgs(...$args): bool
{
    return count($args) >= 2 && 
           is_object($args[0]) && 
           is_string($args[1]);
}
```

### 4. 性能优化

- 避免在钩子中执行耗时操作
- 使用队列处理重型任务
- 合理设置缓存策略

### 5. 测试

```php
public function testUserLoginHook()
{
    $user = User::factory()->create();
    
    $result = Hook::execute('user.login', $user);
    
    $this->assertTrue($result->isSuccessful());
    $this->assertEquals(1, $result->getExecutedCount());
}
```

---

## 常见场景

### 用户认证

```php
#[Hook(name: 'user.login.after', group: 'auth')]
class UserLoginHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$user, $ip] = $args;
        
        // TODO: 实现登录后逻辑
        // - 记录登录日志
        // - 发送登录通知
        // - 更新用户信息
        
        return ['processed' => true];
    }
}
```

### 数据审计

```php
#[Hook(name: 'model.updated', group: 'audit')]
class DataAuditHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$model, $changes] = $args;
        
        // TODO: 实现审计逻辑
        // - 记录变更日志
        // - 保存审计记录
        
        return ['audited' => true];
    }
}
```

### 插件生命周期

```php
#[Hook(name: 'plugin.enabled', group: 'plugin')]
class PluginEnabledHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$pluginName] = $args;
        
        // TODO: 实现插件启用逻辑
        // - 运行迁移
        // - 发布资源
        // - 清理缓存
        
        return ['enabled' => true];
    }
}
```

---

## 配置选项

```php
// config/hooks.php
return [
    'auto_discovery' => true,           // 自动发现钩子
    'cache_enabled' => true,            // 启用缓存
    'cache_ttl' => 24,                  // 缓存时间（小时）
    'middleware_enabled' => true,       // 启用中间件
    'log_execution' => false,           // 记录执行日志
    'performance_monitoring' => false,  // 性能监控
    'performance_threshold' => 100,     // 性能阈值（毫秒）
];
```

---

## 故障排除

### 钩子没有执行？

```bash
# 检查钩子是否已注册
php artisan hook list

# 重新发现钩子
php artisan hook discover

# 清除缓存
php artisan hook clear-cache
```

### 性能问题？

```php
// 启用性能监控
'performance_monitoring' => true,
'performance_threshold' => 50, // 50ms

// 查看统计信息
php artisan hook stats
```

---

## 相关文档

- [快速开始指南](01-getting-started.md)
- [主题系统](02-theme-system.md)
- [插件系统](04-plugin-system.md)

---

**下一篇**: [插件系统](04-plugin-system.md)

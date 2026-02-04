# 钩子管理系统框架

这是一个**纯框架**的钩子管理系统，为Laravel应用提供灵活的事件驱动架构基础设施。**系统不包含任何具体的业务逻辑**，所有业务逻辑都需要开发人员根据自己的需求来实现。

## 🎯 设计理念

- **纯框架设计** - 只提供钩子管理的基础设施，不包含任何业务逻辑
- **用户定义一切** - 所有钩子的具体实现都由开发人员自己编写
- **高度灵活** - 支持多种钩子注册方式和执行模式
- **完全可控** - 开发人员拥有对钩子行为的完全控制权

## 🚀 功能特性

### 框架提供的基础设施
- **钩子注册管理** - 支持多种回调格式（闭包、类方法、字符串）
- **优先级控制** - 支持钩子执行优先级设置
- **分组管理** - 按功能模块对钩子进行分组
- **中间件支持** - 提供钩子执行前的拦截和验证框架
- **自动发现** - 自动扫描和注册钩子类
- **性能监控** - 监控钩子执行性能，记录慢查询
- **缓存优化** - 支持钩子配置缓存，提高性能
- **统计分析** - 提供详细的钩子使用统计
- **命令行管理** - 提供完整的CLI管理工具

### 用户需要实现的部分
- **所有钩子的业务逻辑** - 在 `app/Hooks/Custom/` 目录下实现
- **具体的中间件逻辑** - 根据业务需求实现权限验证、日志记录等
- **钩子触发点** - 在控制器、模型、服务等地方调用钩子执行

## 📁 目录结构

```
app/Hooks/
├── 核心框架/
│   ├── HookManager.php         # 钩子管理器（框架核心）
│   ├── HookResult.php          # 执行结果（框架提供）
│   ├── HookDiscovery.php       # 自动发现（框架提供）
│   └── AbstractHook.php        # 抽象基类（框架提供）
├── 接口和异常/
│   ├── Contracts/HookInterface.php  # 钩子接口（框架定义）
│   └── Exceptions/HookException.php # 钩子异常（框架提供）
├── 中间件框架/
│   ├── AuthMiddleware.php      # 权限验证中间件（示例，用户需自定义）
│   ├── LoggingMiddleware.php   # 日志记录中间件（示例，用户需自定义）
│   └── PerformanceMiddleware.php # 性能监控中间件（示例，用户需自定义）
├── 用户实现区域/
│   ├── Custom/                 # 🎯 用户自定义钩子目录
│   │   └── .gitkeep           # 用户在此目录实现所有业务钩子
│   └── Templates/              # 钩子模板（供用户参考）
│       ├── HookTemplate.php    # 完整钩子模板
│       ├── SimpleHookTemplate.php # 简单钩子模板
│       └── ClosureHookTemplate.php # 闭包钩子模板
└── 工具和文档/
    ├── Facades/Hook.php        # Facade（框架提供）
    ├── Examples/               # 使用示例（框架演示）
    ├── Tests/                  # 测试用例（框架测试）
    └── 文档/                   # 使用文档
```

## 🛠️ 安装配置

### 1. 注册服务提供者

在 `config/app.php` 中添加服务提供者：

```php
'providers' => [
    // ...
    App\Hooks\HookServiceProvider::class,
],
```

### 2. 注册Facade（可选）

```php
'aliases' => [
    // ...
    'Hook' => App\Hooks\Facades\Hook::class,
],
```

### 3. 发布配置文件

```bash
php artisan vendor:publish --tag=hooks-config
```

### 4. 运行迁移

```bash
php artisan vendor:publish --tag=hooks-migrations
php artisan migrate
```

### 5. 发现钩子

```bash
php artisan hook discover
```

## 📖 基础使用

### 注册钩子

```php
use App\Hooks\Facades\Hook;

// 1. 使用闭包
Hook::register('user.login', function ($user) {
    logger()->info("用户登录: {$user->email}");
    return ['status' => 'success', 'user_id' => $user->id];
});

// 2. 使用类方法
Hook::register('user.login', [UserLoginHandler::class, 'handle'], 10, 'auth');

// 3. 使用字符串格式
Hook::register('user.login', 'UserLoginHandler@handle', 10, 'auth');

// 4. 批量注册
Hook::registerBatch([
    'user.login' => function ($user) { /* ... */ },
    'user.logout' => function ($user) { /* ... */ },
], 'auth');
```

### 执行钩子

```php
// 执行钩子
$result = Hook::execute('user.login', $user, $request->ip());

// 检查执行结果
if ($result->isSuccessful()) {
    echo "钩子执行成功，执行了 {$result->getExecutedCount()} 个钩子";
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

// 获取统计信息
$stats = Hook::getStats();

// 启用/禁用钩子
Hook::toggle('user.login', $hookId, false); // 禁用
Hook::toggle('user.login', $hookId, true);  // 启用

// 移除钩子
Hook::remove('user.login', $hookId);        // 移除特定钩子
Hook::remove('user.login');                 // 移除所有同名钩子
Hook::removeByGroup('auth');                // 移除分组下所有钩子
```

## 🎯 创建自定义钩子

### 重要说明
**所有业务逻辑都需要用户自己实现！** 框架只提供基础设施，不包含任何具体的业务逻辑。

### 1. 使用钩子模板

复制模板到自定义目录：
```bash
cp app/Hooks/Templates/HookTemplate.php app/Hooks/Custom/MyCustomHook.php
```

### 2. 实现钩子逻辑

```php
<?php

namespace App\Hooks\Custom;

use App\Hooks\AbstractHook;

/**
 * @hook user.welcome
 * @priority 10
 * @group user
 */
class UserWelcomeHook extends AbstractHook
{
    protected string $description = '用户欢迎钩子';
    protected int $priority = 10;

    public function handle(...$args)
    {
        [$user] = $args;
        
        // TODO: 在这里实现你的业务逻辑
        // 例如：发送欢迎邮件、记录日志、更新统计等
        
        return [
            'status' => 'success',
            'message' => '欢迎处理完成',
            'user_id' => $user->id
        ];
    }

    protected function validateArgs(...$args): bool
    {
        // TODO: 实现参数验证逻辑
        return count($args) >= 1 && is_object($args[0]);
    }
}
```

### 3. 自动注册钩子

```bash
php artisan hook discover
```

### 4. 使用钩子

```php
// 在控制器或服务中
$result = Hook::execute('user.welcome', $user);
```

## 🔧 中间件系统

### 创建中间件

```php
<?php

namespace App\Hooks\Middleware;

class CustomMiddleware
{
    public function __invoke(string $hookName, string $hookId, array $args): bool
    {
        // 执行前置逻辑
        if (!$this->shouldExecute($hookName, $args)) {
            return false; // 阻止执行
        }
        
        return true; // 允许执行
    }
    
    private function shouldExecute(string $hookName, array $args): bool
    {
        // 自定义逻辑
        return true;
    }
}
```

### 注册中间件

```php
// 为特定钩子添加中间件
Hook::addMiddleware('user.delete', new CustomMiddleware());

// 在配置文件中全局配置
'middleware' => [
    'global' => [
        App\Hooks\Middleware\LoggingMiddleware::class,
    ],
    'specific' => [
        'user.login' => [
            App\Hooks\Middleware\AuthMiddleware::class,
        ],
    ],
],
```

## 🎮 命令行工具

```bash
# 列出所有钩子
php artisan hook list

# 按分组列出钩子
php artisan hook list --group=auth

# 显示统计信息
php artisan hook stats

# 发现并注册钩子
php artisan hook discover

# 启用钩子
php artisan hook enable --hook=user.login --id=hook_abc123

# 禁用钩子
php artisan hook disable --hook=user.login --id=hook_abc123

# 移除钩子
php artisan hook remove --hook=user.login --id=hook_abc123

# 按分组移除钩子
php artisan hook remove --group=auth

# 测试钩子
php artisan hook test --hook=user.login

# 清除缓存
php artisan hook clear-cache
```

## 🔍 内置钩子点定义

系统预定义了以下钩子点（**仅定义钩子点，不包含业务逻辑**）：

### 系统钩子点
- `app.booting` - 应用启动时
- `app.booted` - 应用启动完成
- `app.terminating` - 应用终止时

### 认证钩子点
- `user.login.before` - 用户登录前
- `user.login.after` - 用户登录后
- `user.logout.before` - 用户登出前
- `user.logout.after` - 用户登出后
- `user.registered` - 用户注册后
- `user.password.changed` - 密码修改后

### 数据库钩子点
- `model.creating` - 模型创建前
- `model.created` - 模型创建后
- `model.updating` - 模型更新前
- `model.updated` - 模型更新后
- `model.deleting` - 模型删除前
- `model.deleted` - 模型删除后
- `model.saving` - 模型保存前
- `model.saved` - 模型保存后

### 插件钩子点
- `plugin.installing` - 插件安装前
- `plugin.installed` - 插件安装后
- `plugin.enabling` - 插件启用前
- `plugin.enabled` - 插件启用后
- `plugin.disabling` - 插件禁用前
- `plugin.disabled` - 插件禁用后
- `plugin.uninstalling` - 插件卸载前
- `plugin.uninstalled` - 插件卸载后
- `plugin.deleting` - 插件删除前
- `plugin.deleted` - 插件删除后

### 缓存钩子点
- `cache.clearing` - 缓存清理前
- `cache.cleared` - 缓存清理后
- `cache.writing` - 缓存写入前
- `cache.written` - 缓存写入后

### HTTP钩子点
- `request.received` - 请求接收时
- `request.processed` - 请求处理完成
- `response.sending` - 响应发送前
- `response.sent` - 响应发送后

**注意：以上只是钩子点的定义，具体的业务逻辑需要用户自己实现！**

## 📊 性能监控

系统提供了完整的性能监控功能：

### 配置监控

```php
// config/hooks.php
'performance_monitoring' => true,
'performance_threshold' => 100, // 毫秒
'log_execution' => true,
```

### 查看性能数据

```php
$stats = Hook::getStats();
echo "总调用次数: {$stats['total_calls']}";

// 查看慢钩子日志
tail -f storage/logs/laravel.log | grep "钩子执行缓慢"
```

## 🛡️ 最佳实践

### 1. 钩子命名规范
- 使用点号分隔的命名空间：`模块.操作.时机`
- 例如：`user.login.before`、`order.created.after`

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
        throw $e; // 重新抛出或返回错误结果
    }
}
```

### 3. 参数验证
```php
protected function validateArgs(...$args): bool
{
    // 验证参数数量和类型
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
// 在测试中
public function testUserLoginHook()
{
    $user = User::factory()->create();
    
    $result = Hook::execute('user.login', $user);
    
    $this->assertTrue($result->isSuccessful());
    $this->assertEquals(1, $result->getExecutedCount());
}
```

## 🔧 配置选项

详细配置请查看 `config/hooks.php` 文件：

```php
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

## 🤝 贡献指南

### 用户实现钩子的步骤

1. **在 `app/Hooks/Custom/` 目录下创建你的钩子类**
2. **实现 `HookInterface` 接口或继承 `AbstractHook`**
3. **使用注解标记钩子信息**
4. **实现具体的业务逻辑**
5. **编写测试用例**
6. **运行 `php artisan hook discover` 注册钩子**

### 框架贡献

如果你想为框架本身贡献代码：

1. Fork 项目
2. 创建功能分支
3. 提交更改
4. 发起 Pull Request

**注意：框架本身不接受包含具体业务逻辑的贡献，只接受基础设施的改进。**

## 📝 许可证

MIT License

---

## 🎯 重要提醒

**这是一个纯框架系统！**

- ✅ 框架提供：钩子管理基础设施、注册机制、执行引擎、中间件框架、命令行工具
- ❌ 框架不提供：任何具体的业务逻辑实现
- 🎯 用户需要：在 `app/Hooks/Custom/` 目录下实现所有业务钩子
- 📝 参考：使用 `app/Hooks/Templates/` 下的模板开始编写

这个钩子系统为你的Laravel应用提供了强大而灵活的事件驱动架构基础设施。通过在 `app/Hooks/Custom/` 目录下实现你的业务钩子，你可以构建松耦合、可扩展的业务逻辑，让你的应用更加模块化和可维护。

**记住：所有的业务逻辑都由你来定义和实现！**
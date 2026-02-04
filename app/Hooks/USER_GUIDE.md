# 用户实现指南

## 🎯 重要说明

**这个钩子系统是一个纯框架！** 它只提供钩子管理的基础设施，**不包含任何业务逻辑**。所有的业务逻辑都需要你根据自己的需求来实现。

## 📋 你需要做什么

### 1. 实现钩子类
在 `app/Hooks/Custom/` 目录下创建你的钩子类，实现具体的业务逻辑。

### 2. 触发钩子执行
在你的控制器、模型、服务等地方调用钩子执行。

### 3. 配置中间件（可选）
根据需要实现权限验证、日志记录等中间件。

## 🛠️ 实现步骤

### 步骤1：创建钩子类

#### 方法1：使用生成器命令（推荐）

```bash
# 创建基础钩子
php artisan make:hook YourHookName

# 使用特定模板
php artisan make:hook YourHookName --template=async --group=your_group

# 指定钩子名称和优先级
php artisan make:hook YourHookName --hook=your.hook.name --priority=5
```

#### 方法2：手动复制模板

```bash
# 复制模板
cp app/Hooks/Templates/HookTemplate.php app/Hooks/Custom/YourHookName.php
```

编辑钩子类：
```php
<?php

namespace App\Hooks\Custom;

use App\Hooks\AbstractHook;

/**
 * @hook your.hook.name
 * @priority 10
 * @group your_group
 */
class YourHookName extends AbstractHook
{
    protected string $description = '你的钩子描述';

    public function handle(...$args)
    {
        // 🎯 在这里实现你的业务逻辑
        
        // 示例：处理用户登录
        // [$user, $ip] = $args;
        // 
        // // 记录登录日志
        // Log::info('用户登录', ['user_id' => $user->id, 'ip' => $ip]);
        // 
        // // 发送通知
        // Notification::send($user, new LoginNotification());
        // 
        // // 更新最后登录时间
        // $user->update(['last_login_at' => now()]);
        
        return [
            'status' => 'success',
            'message' => '处理完成'
        ];
    }

    protected function validateArgs(...$args): bool
    {
        // 🎯 在这里实现参数验证逻辑
        return true;
    }
}
```

### 步骤2：注册钩子

```bash
php artisan hook discover
```

### 步骤3：触发钩子执行

在你的代码中触发钩子：

```php
use App\Hooks\Facades\Hook;

// 在控制器中
public function login(Request $request)
{
    // 你的登录逻辑...
    
    if ($user = Auth::attempt($credentials)) {
        // 触发登录后钩子
        Hook::execute('user.login.after', $user, $request->ip());
        
        return redirect()->intended();
    }
}

// 在模型中
protected static function boot()
{
    parent::boot();
    
    static::created(function ($model) {
        Hook::execute('model.created', $model);
    });
}

// 在服务中
public function processOrder($order)
{
    // 处理订单逻辑...
    
    // 触发订单处理完成钩子
    Hook::execute('order.processed', $order);
}
```

## 📚 常见业务场景实现

### 1. 用户认证相关

```php
// app/Hooks/Custom/UserAuthHook.php
/**
 * @hook user.login.after
 * @priority 10
 * @group auth
 */
class UserAuthHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$user, $ip, $userAgent] = $args;
        
        // 🎯 实现你的登录后逻辑
        // - 记录登录日志
        // - 发送登录通知
        // - 更新用户信息
        // - 检查异地登录
        // - 清理失败尝试记录
        
        return ['processed' => true];
    }
}
```

### 2. 数据审计相关

```php
// app/Hooks/Custom/DataAuditHook.php
/**
 * @hook model.updated
 * @priority 5
 * @group audit
 */
class DataAuditHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$model, $changes] = $args;
        
        // 🎯 实现你的数据审计逻辑
        // - 记录变更日志
        // - 保存审计记录
        // - 发送变更通知
        // - 检查敏感字段变更
        
        return ['audited' => true];
    }
}
```

### 3. 缓存管理相关

```php
// app/Hooks/Custom/CacheManagementHook.php
/**
 * @hook cache.invalidate
 * @priority 15
 * @group cache
 */
class CacheManagementHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$event, $data] = $args;
        
        // 🎯 实现你的缓存失效逻辑
        // - 清理相关缓存
        // - 更新缓存标签
        // - 预热重要缓存
        
        return ['cache_cleared' => true];
    }
}
```

### 4. 插件生命周期相关

```php
// app/Hooks/Custom/PluginLifecycleHook.php
/**
 * @hook plugin.enabled
 * @priority 5
 * @group plugin
 */
class PluginLifecycleHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$pluginName, $pluginInfo] = $args;
        
        // 🎯 实现你的插件启用逻辑
        // - 运行插件迁移
        // - 发布插件资源
        // - 注册插件路由
        // - 清理相关缓存
        
        return ['plugin_enabled' => true];
    }
}
```

## 🔧 中间件实现

如果需要权限验证或其他中间件功能：

```php
// app/Hooks/Middleware/CustomAuthMiddleware.php
class CustomAuthMiddleware
{
    public function __invoke(string $hookName, string $hookId, array $args): bool
    {
        // 🎯 实现你的权限检查逻辑
        
        // 示例：检查用户权限
        if (str_starts_with($hookName, 'admin.')) {
            return auth()->check() && auth()->user()->isAdmin();
        }
        
        return true;
    }
}
```

然后注册中间件：
```php
Hook::addMiddleware('admin.*', new CustomAuthMiddleware());
```

## 📊 监控和调试

### 1. 启用日志记录

```php
// config/hooks.php
'log_execution' => true,
'performance_monitoring' => true,
```

### 2. 在钩子中添加日志

```php
public function handle(...$args)
{
    Log::info('钩子开始执行', ['hook' => static::class, 'args' => $args]);
    
    try {
        // 你的业务逻辑
        $result = $this->processBusinessLogic($args);
        
        Log::info('钩子执行成功', ['result' => $result]);
        return $result;
        
    } catch (\Exception $e) {
        Log::error('钩子执行失败', ['error' => $e->getMessage()]);
        throw $e;
    }
}
```

### 3. 使用命令行工具

```bash
# 查看钩子列表
php artisan hook list

# 测试特定钩子
php artisan hook test --hook=your.hook.name

# 查看统计信息
php artisan hook stats
```

## ✅ 检查清单

在实现钩子时，请确保：

- [ ] 钩子类在正确的命名空间下 (`App\Hooks\Custom`)
- [ ] 使用了正确的注解 (`@hook`, `@priority`, `@group`)
- [ ] 实现了 `handle()` 方法的业务逻辑
- [ ] 添加了适当的参数验证
- [ ] 包含了错误处理逻辑
- [ ] 添加了必要的日志记录
- [ ] 运行了 `php artisan hook discover`
- [ ] 在适当的地方触发了钩子执行
- [ ] 编写了测试用例

## 🚨 注意事项

1. **性能考虑**：避免在钩子中执行耗时操作，考虑使用队列
2. **错误处理**：确保钩子异常不会影响主业务流程
3. **参数验证**：验证钩子参数的有效性
4. **日志记录**：记录重要的执行信息便于调试
5. **测试覆盖**：为你的钩子编写测试用例

---

## 🎯 总结

记住，这个钩子系统只是一个框架！它提供了：

- ✅ 钩子注册和管理机制
- ✅ 钩子执行引擎
- ✅ 中间件框架
- ✅ 命令行管理工具
- ✅ 性能监控和统计

但是它**不提供**：

- ❌ 任何具体的业务逻辑
- ❌ 预定义的钩子实现
- ❌ 特定的业务场景处理

**所有的业务逻辑都需要你在 `app/Hooks/Custom/` 目录下自己实现！**

开始编写你的第一个钩子吧！🚀

## 🎨 模板选择指南

### 可用模板

| 模板 | 适用场景 | 复杂度 | 特性 |
|------|----------|--------|------|
| **basic** | 通用场景 | ⭐⭐⭐⭐ | 完整功能、生命周期管理 |
| **simple** | 简单处理 | ⭐ | 最小实现、快速开发 |
| **async** | 异步处理 | ⭐⭐⭐⭐ | 队列支持、性能监控 |
| **conditional** | 条件处理 | ⭐⭐⭐ | 多分支逻辑、动态处理 |
| **batch** | 批量处理 | ⭐⭐⭐⭐ | 分批处理、错误恢复 |
| **event** | 事件驱动 | ⭐⭐⭐ | Laravel事件集成 |
| **cache** | 缓存优化 | ⭐⭐⭐ | 智能缓存、性能提升 |
| **validation** | 数据验证 | ⭐⭐⭐ | Laravel验证器集成 |

### 选择建议

```bash
# 新手推荐：从简单模板开始
php artisan make:hook MyFirstHook --template=simple

# 复杂业务：使用基础模板
php artisan make:hook BusinessLogic --template=basic

# 性能敏感：使用缓存模板
php artisan make:hook DataCalculator --template=cache

# 大数据处理：使用批量模板
php artisan make:hook DataProcessor --template=batch

# 数据验证：使用验证模板
php artisan make:hook InputValidator --template=validation
```

### 模板组合使用

你也可以组合多个模板的特性：

```php
<?php

namespace App\Hooks\Custom;

use App\Hooks\Templates\CacheAwareHookTemplate;

/**
 * 组合缓存和验证功能的钩子
 * 
 * @hook combined.cache.validation
 * @priority 10
 * @group combined
 */
class CombinedHook extends CacheAwareHookTemplate
{
    public function handle(...$args)
    {
        // 先进行数据验证
        $this->validateInput($args[0]);
        
        // 然后使用缓存处理
        return parent::handle(...$args);
    }
    
    protected function validateInput($data): void
    {
        // 实现验证逻辑
        if (empty($data)) {
            throw new \InvalidArgumentException('数据不能为空');
        }
    }
}
```
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
# 创建基础钩子（自动选择注解语法）
php artisan make:hook YourHookName

# 使用特定模板
php artisan make:hook YourHookName --template=async --group=your_group

# 指定钩子名称和优先级
php artisan make:hook YourHookName --hook=your.hook.name --priority=5

# 强制使用 PHP 8.2 Attribute 语法
php artisan make:hook YourHookName --attribute

# 强制使用传统注释语法
php artisan make:hook YourHookName --legacy
```

#### 方法2：手动复制模板

```bash
# 复制模板
cp app/Hooks/Templates/HookTemplate.php app/Hooks/Custom/YourHookName.php
```

#### PHP 8.2 Attribute 语法（推荐）

```php
<?php

namespace App\Hooks\Custom;

use App\Hooks\AbstractHook;
use App\Hooks\Attributes\Hook;
use App\Hooks\Attributes\Priority;
use App\Hooks\Attributes\Group;
use App\Hooks\Attributes\Middleware;
use App\Hooks\Attributes\Condition;

/**
 * 使用 PHP 8.2 Attribute 语法定义钩子
 */
#[Hook(
    name: 'your.hook.name',
    priority: 10,
    group: 'your_group',
    description: '你的钩子描述',
    enabled: true
)]
#[Middleware(class: 'App\Hooks\Middleware\AuthMiddleware')]
#[Condition(type: 'environment', value: 'production')]
class YourHookName extends AbstractHook
{
    public function handle(...$args)
    {
        // 🎯 在这里实现你的业务逻辑
        
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

#### 传统注释语法（向后兼容）

```php
<?php

namespace App\Hooks\Custom;

use App\Hooks\AbstractHook;

/**
 * 使用传统注释语法定义钩子
 * 
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

### 5. 视图处理相关

```php
// app/Hooks/Custom/ViewProcessingHook.php
/**
 * @hook view.before_render
 * @priority 10
 * @group view
 */
class ViewProcessingHook extends AbstractHook
{
    public function handle(...$args)
    {
        [$action, $viewName, $data, $options] = $args;
        
        // 🎯 实现你的视图处理逻辑
        switch ($action) {
            case 'before_render':
                return $this->handleBeforeRender($viewName, $data, $options);
            case 'after_render':
                return $this->handleAfterRender($viewName, $data, $options);
            case 'data_injection':
                return $this->handleDataInjection($viewName, $data, $options);
        }
        
        return ['processed' => true];
    }
    
    private function handleBeforeRender($viewName, $data, $options)
    {
        // 🎯 实现渲染前处理逻辑
        // - 数据预处理
        // - 权限检查
        // - 主题切换
        // - 布局选择
        
        return [
            'processed_data' => $data,
            'view_modifications' => []
        ];
    }
    
    private function handleAfterRender($viewName, $data, $options)
    {
        // 🎯 实现渲染后处理逻辑
        // - 内容优化
        // - SEO标签注入
        // - 性能监控
        // - 缓存处理
        
        return [
            'processed_content' => $options['rendered_content']
        ];
    }
    
    private function handleDataInjection($viewName, $data, $options)
    {
        // 🎯 实现数据注入逻辑
        // - 全局变量注入
        // - 用户数据注入
        // - 系统配置注入
        // - 动态内容注入
        
        return [
            'injected_data' => [
                'global_config' => config('app'),
                'user_info' => auth()->user(),
                'system_time' => now()
            ]
        ];
    }
}
```

### 6. 视图组合器相关

```php
// app/Hooks/Custom/NavigationComposerHook.php
use App\Hooks\Templates\ViewComposerHookTemplate;

/**
 * @hook view.composer.navigation
 * @priority 10
 * @group view
 */
class NavigationComposerHook extends ViewComposerHookTemplate
{
    protected function getComposerDataForView($view, array $data, array $options): array
    {
        $viewName = $view->getName();
        
        // 🎯 实现导航组合器逻辑
        if (str_starts_with($viewName, 'admin.')) {
            return $this->getAdminNavigationData();
        } elseif (str_starts_with($viewName, 'user.')) {
            return $this->getUserNavigationData();
        }
        
        return $this->getPublicNavigationData();
    }
    
    private function getAdminNavigationData(): array
    {
        // 🎯 实现管理员导航数据
        return [
            'admin_menu' => [
                ['title' => '仪表板', 'url' => '/admin/dashboard'],
                ['title' => '用户管理', 'url' => '/admin/users'],
                ['title' => '系统设置', 'url' => '/admin/settings']
            ],
            'admin_notifications' => $this->getAdminNotifications()
        ];
    }
    
    private function getUserNavigationData(): array
    {
        // 🎯 实现用户导航数据
        return [
            'user_menu' => [
                ['title' => '个人中心', 'url' => '/profile'],
                ['title' => '我的订单', 'url' => '/orders'],
                ['title' => '设置', 'url' => '/settings']
            ],
            'user_notifications' => $this->getUserNotifications()
        ];
    }
    
    private function getPublicNavigationData(): array
    {
        // 🎯 实现公共导航数据
        return [
            'main_menu' => [
                ['title' => '首页', 'url' => '/'],
                ['title' => '产品', 'url' => '/products'],
                ['title' => '关于我们', 'url' => '/about']
            ]
        ];
    }
}
```

## 🎨 PHP 8.2 Attribute 使用指南

### Attribute 优势

PHP 8.2 Attribute 相比传统注释有以下优势：

- ✅ **类型安全** - 编译时检查，避免拼写错误
- ✅ **IDE支持** - 更好的代码补全和重构支持
- ✅ **性能更好** - 不需要解析注释字符串
- ✅ **功能更强** - 支持复杂的参数和条件

### 可用的 Attribute

#### 1. Hook Attribute - 基础钩子定义

```php
use App\Hooks\Attributes\Hook;

#[Hook(
    name: 'user.login.after',           // 钩子名称（必需）
    priority: 10,                       // 优先级（可选，默认10）
    group: 'auth',                      // 分组（可选）
    description: '用户登录后处理',        // 描述（可选）
    enabled: true                       // 是否启用（可选，默认true）
)]
class UserLoginHook extends AbstractHook { ... }
```

#### 2. Priority Attribute - 单独设置优先级

```php
use App\Hooks\Attributes\Priority;

#[Hook(name: 'data.process')]
#[Priority(value: 5)]  // 覆盖 Hook 中的优先级
class DataProcessHook extends AbstractHook { ... }
```

#### 3. Group Attribute - 单独设置分组

```php
use App\Hooks\Attributes\Group;

#[Hook(name: 'cache.clear')]
#[Group(name: 'cache')]  // 覆盖 Hook 中的分组
class CacheClearHook extends AbstractHook { ... }
```

#### 4. Middleware Attribute - 钩子中间件

```php
use App\Hooks\Attributes\Middleware;

#[Hook(name: 'admin.action')]
#[Middleware(class: 'App\Hooks\Middleware\AuthMiddleware')]
#[Middleware(
    class: 'App\Hooks\Middleware\LoggingMiddleware',
    parameters: ['level' => 'info', 'channel' => 'hooks']
)]
class AdminActionHook extends AbstractHook { ... }
```

#### 5. Condition Attribute - 执行条件

```php
use App\Hooks\Attributes\Condition;

#[Hook(name: 'production.task')]
#[Condition(type: 'environment', value: 'production')]
#[Condition(type: 'auth', value: true)]
#[Condition(type: 'user_role', value: 'admin')]
#[Condition(type: 'time', value: '09:00', operator: '>=')]
#[Condition(type: 'time', value: '18:00', operator: '<=')]
class ProductionTaskHook extends AbstractHook { ... }
```

### 条件类型详解

#### 环境条件

```php
// 单个环境
#[Condition(type: 'environment', value: 'production')]

// 多个环境
#[Condition(type: 'environment', value: ['production', 'staging'], operator: 'in')]
```

#### 认证条件

```php
// 需要登录
#[Condition(type: 'auth', value: true)]

// 不需要登录
#[Condition(type: 'auth', value: false)]
```

#### 用户角色条件

```php
// 特定角色
#[Condition(type: 'user_role', value: 'admin')]

// 多个角色
#[Condition(type: 'user_role', value: ['admin', 'manager'], operator: 'in')]
```

#### 时间条件

```php
// 工作时间执行
#[Condition(type: 'time', value: '09:00', operator: '>=')]
#[Condition(type: 'time', value: '17:00', operator: '<=')]
```

#### 配置条件

```php
// 检查功能开关
#[Condition(type: 'config', value: 'features.advanced_hooks')]
```

#### 自定义条件

```php
#[Condition(
    type: 'custom',
    value: [MyHook::class, 'checkCustomCondition']
)]
class MyHook extends AbstractHook
{
    public static function checkCustomCondition(string $hookName, string $hookId, array $args): bool
    {
        // 自定义条件逻辑
        return true;
    }
}
```

### 高级 Attribute 用法

#### 组合使用多个 Attribute

```php
#[Hook(
    name: 'complex.business.logic',
    priority: 5,
    group: 'business'
)]
#[Middleware(class: 'App\Hooks\Middleware\AuthMiddleware')]
#[Middleware(class: 'App\Hooks\Middleware\RateLimitMiddleware', parameters: ['limit' => 100])]
#[Condition(type: 'environment', value: 'production')]
#[Condition(type: 'user_role', value: ['admin', 'manager'], operator: 'in')]
#[Condition(type: 'time', value: '08:00', operator: '>=')]
#[Condition(type: 'time', value: '20:00', operator: '<=')]
class ComplexBusinessHook extends AbstractHook
{
    public function handle(...$args)
    {
        // 复杂业务逻辑
        return ['processed' => true];
    }
}
```

#### 禁用钩子

```php
#[Hook(
    name: 'disabled.feature',
    enabled: false  // 钩子被禁用
)]
class DisabledFeatureHook extends AbstractHook
{
    public function handle(...$args)
    {
        // 这个钩子不会被执行
        return ['executed' => false];
    }
}
```

### 兼容性说明

- **PHP >= 8.2**: 优先使用 Attribute，回退到注释
- **PHP < 8.2**: 只能使用传统注释
- **混合使用**: Attribute 优先级高于注释
- **命令行工具**: 自动检测 PHP 版本选择语法

### 最佳实践

1. **优先使用 Attribute**: 如果项目使用 PHP 8.2+
2. **保持一致性**: 项目内统一使用一种语法
3. **合理使用条件**: 避免过度复杂的条件组合
4. **中间件分离**: 将通用逻辑抽取为中间件
5. **文档化**: 为复杂的条件添加注释说明

## 🎨 视图钩子使用方式

视图钩子系统提供了强大的视图处理能力，让你可以在视图的各个生命周期阶段插入自定义逻辑。

### 视图钩子管理器

首先获取视图钩子管理器：

```php
use App\Hooks\View\ViewHookManager;

// 在服务提供者或控制器中
$viewHookManager = app(ViewHookManager::class);
```

### 1. 注册视图钩子

#### 渲染前钩子

```php
// 为管理员视图注册渲染前钩子
$viewHookManager->beforeRender('admin.*', function ($viewName, $data) {
    // 🎯 实现管理员视图前置处理
    
    // 添加管理员专用数据
    $adminData = [
        'admin_menu' => $this->getAdminMenu(),
        'system_stats' => $this->getSystemStats(),
        'pending_tasks' => $this->getPendingTasks()
    ];
    
    return [
        'processed_data' => array_merge($data, $adminData)
    ];
});

// 为特定视图注册钩子
$viewHookManager->beforeRender('dashboard.index', function ($viewName, $data) {
    // 🎯 实现仪表板特定处理
    return [
        'dashboard_widgets' => $this->getDashboardWidgets(),
        'recent_activities' => $this->getRecentActivities()
    ];
});
```

#### 渲染后钩子

```php
$viewHookManager->afterRender('admin.*', function ($viewName, $data, $options) {
    $content = $options['rendered_content'];
    
    // 🎯 实现内容后处理
    // 添加管理员工具栏
    $toolbar = '<div class="admin-toolbar">管理员工具</div>';
    $processedContent = str_replace('</body>', $toolbar . '</body>', $content);
    
    return ['processed_content' => $processedContent];
});
```

#### 数据注入钩子

```php
// 全局数据注入
$viewHookManager->injectData('*', function ($viewName, $data) {
    // 🎯 实现全局数据注入
    return [
        'injected_data' => [
            'app_name' => config('app.name'),
            'current_user' => auth()->user(),
            'system_time' => now(),
            'csrf_token' => csrf_token()
        ]
    ];
});

// 用户视图数据注入
$viewHookManager->injectData('user.*', function ($viewName, $data) {
    $user = auth()->user();
    
    // 🎯 实现用户视图数据注入
    return [
        'injected_data' => [
            'user_permissions' => $this->getUserPermissions($user),
            'user_preferences' => $this->getUserPreferences($user),
            'unread_messages' => $this->getUnreadMessages($user)
        ]
    ];
});
```

### 2. 使用视图钩子模板

#### 创建视图处理钩子

```php
// app/Hooks/Custom/MyViewHook.php
use App\Hooks\Templates\ViewHookTemplate;

/**
 * @hook view.my_custom
 * @priority 10
 * @group view
 */
class MyViewHook extends ViewHookTemplate
{
    protected function handleBeforeRender(string $viewName, array $data, array $options): array
    {
        // 🎯 实现你的渲染前逻辑
        
        // 检查用户权限
        if (!$this->checkViewPermission($viewName)) {
            throw new UnauthorizedException('无权访问此视图');
        }
        
        // 预处理数据
        $processedData = $this->preprocessData($data);
        
        // 选择主题
        $theme = $this->selectTheme($viewName, $data);
        
        return [
            'status' => 'success',
            'processed_data' => $processedData,
            'theme' => $theme
        ];
    }
    
    protected function handleDataInjection(string $viewName, array $data, array $options): array
    {
        // 🎯 实现你的数据注入逻辑
        
        $injectedData = [];
        
        // 注入SEO数据
        if (str_starts_with($viewName, 'public.')) {
            $injectedData['seo'] = $this->getSeoData($viewName);
        }
        
        // 注入分析代码
        $injectedData['analytics'] = $this->getAnalyticsCode();
        
        return [
            'injected_data' => $injectedData
        ];
    }
    
    // 🎯 实现你的辅助方法
    private function checkViewPermission(string $viewName): bool
    {
        // 权限检查逻辑
        return true;
    }
    
    private function preprocessData(array $data): array
    {
        // 数据预处理逻辑
        return $data;
    }
    
    private function selectTheme(string $viewName, array $data): string
    {
        // 主题选择逻辑
        return 'default';
    }
}
```

#### 创建视图组合器钩子

```php
// app/Hooks/Custom/MyComposerHook.php
use App\Hooks\Templates\ViewComposerHookTemplate;

/**
 * @hook view.composer.sidebar
 * @priority 10
 * @group view
 */
class SidebarComposerHook extends ViewComposerHookTemplate
{
    protected function getComposerDataForView($view, array $data, array $options): array
    {
        $viewName = $view->getName();
        
        // 🎯 实现你的组合器逻辑
        
        $composerData = [];
        
        // 根据视图类型提供不同数据
        if (str_starts_with($viewName, 'admin.')) {
            $composerData = $this->getAdminSidebarData();
        } elseif (str_starts_with($viewName, 'user.')) {
            $composerData = $this->getUserSidebarData();
        } else {
            $composerData = $this->getPublicSidebarData();
        }
        
        return $composerData;
    }
    
    // 🎯 实现你的数据获取方法
    private function getAdminSidebarData(): array
    {
        return [
            'admin_menu' => $this->buildAdminMenu(),
            'system_notifications' => $this->getSystemNotifications(),
            'quick_actions' => $this->getQuickActions()
        ];
    }
    
    private function getUserSidebarData(): array
    {
        return [
            'user_menu' => $this->buildUserMenu(),
            'user_notifications' => $this->getUserNotifications(),
            'recommended_content' => $this->getRecommendedContent()
        ];
    }
    
    private function getPublicSidebarData(): array
    {
        return [
            'categories' => $this->getCategories(),
            'popular_posts' => $this->getPopularPosts(),
            'recent_comments' => $this->getRecentComments()
        ];
    }
}
```

### 3. 在控制器中使用视图钩子

```php
// app/Http/Controllers/DashboardController.php
use App\Hooks\View\ViewHookManager;

class DashboardController extends Controller
{
    protected ViewHookManager $viewHookManager;
    
    public function __construct(ViewHookManager $viewHookManager)
    {
        $this->viewHookManager = $viewHookManager;
    }
    
    public function index()
    {
        $data = ['user_count' => 100, 'order_count' => 50];
        
        // 执行渲染前钩子
        $beforeResults = $this->viewHookManager->executeBeforeRender('dashboard.index', $data);
        
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

### 4. 在Blade模板中使用钩子指令

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
        
        {{-- 执行自定义钩子 --}}
        @hook('dashboard.custom_widget', $user)
        
        {{-- 条件钩子 --}}
        @ifhook('feature.advanced_dashboard')
            <div class="advanced-features">
                <!-- 高级功能 -->
            </div>
        @endifhook
        
        <div class="widgets">
            <!-- 仪表板组件 -->
        </div>
    </div>
    
    {{-- 执行渲染后钩子 --}}
    @hookAfter('dashboard.widgets')
@endsection
```

### 5. 使用视图宏

```php
// 在控制器中
return view('admin.users.index')
    ->withHook('admin.users.data', ['filter' => 'active'])
    ->withTheme('admin-dark')
    ->withLayout('admin.layout');
```

### 6. 批量注册视图钩子

```php
// 在服务提供者中
public function boot()
{
    $viewHookManager = app(ViewHookManager::class);
    
    $hooks = [
        [
            'type' => 'before_render',
            'pattern' => 'admin.*',
            'callback' => function ($viewName, $data) {
                // 🎯 管理员视图前置处理
                return ['admin_data' => $this->getAdminData()];
            },
            'priority' => 5
        ],
        [
            'type' => 'inject_data',
            'pattern' => 'emails.*',
            'callback' => function ($viewName, $data) {
                // 🎯 邮件视图数据注入
                return ['email_config' => $this->getEmailConfig()];
            },
            'priority' => 10
        ],
        [
            'type' => 'after_render',
            'pattern' => 'reports.*',
            'callback' => function ($viewName, $data, $options) {
                // 🎯 报表视图后处理
                return ['report_meta' => $this->addReportMeta($options['rendered_content'])];
            },
            'priority' => 15
        ]
    ];
    
    $viewHookManager->registerBatch($hooks);
}
```

### 7. 视图钩子最佳实践

#### 性能优化

```php
class OptimizedViewHook extends ViewHookTemplate
{
    protected function handleDataInjection(string $viewName, array $data, array $options): array
    {
        // 🎯 使用缓存提高性能
        $cacheKey = "view_data_{$viewName}_" . md5(serialize($data));
        
        return cache()->remember($cacheKey, 300, function () use ($viewName, $data) {
            return [
                'injected_data' => $this->getExpensiveData($viewName, $data)
            ];
        });
    }
    
    protected function handleBeforeRender(string $viewName, array $data, array $options): array
    {
        // 🎯 延迟加载非关键数据
        if ($this->shouldLoadLazyData($viewName)) {
            $data['lazy_data'] = function () {
                return $this->getLazyData();
            };
        }
        
        return ['processed_data' => $data];
    }
}
```

#### 错误处理

```php
class SafeViewHook extends ViewHookTemplate
{
    public function handle(...$args)
    {
        try {
            return parent::handle(...$args);
        } catch (\Exception $e) {
            // 🎯 记录错误但不影响视图渲染
            Log::error('视图钩子执行失败', [
                'hook' => static::class,
                'error' => $e->getMessage(),
                'args' => $args
            ]);
            
            // 返回默认结果
            return ['status' => 'error', 'message' => '钩子执行失败'];
        }
    }
}
```

### 8. 视图钩子调试

```php
// 启用调试模式
$viewHookManager->enableDebugMode();

// 查看钩子执行统计
$stats = $viewHookManager->getViewHookStats();
dd($stats);

// 监听钩子执行事件
Event::listen('view.hook.executed', function ($hookName, $result) {
    Log::debug("视图钩子执行", ['hook' => $hookName, 'result' => $result]);
});
```

### 9. 视图钩子测试

```php
// tests/Feature/ViewHookTest.php
class ViewHookTest extends TestCase
{
    public function test_admin_view_hook_adds_admin_data()
    {
        // 🎯 测试管理员视图钩子
        $this->actingAs($this->createAdminUser());
        
        $response = $this->get('/admin/dashboard');
        
        $response->assertOk();
        $response->assertViewHas('admin_menu');
        $response->assertViewHas('system_stats');
    }
    
    public function test_view_composer_injects_navigation_data()
    {
        // 🎯 测试视图组合器
        $response = $this->get('/');
        
        $response->assertOk();
        $response->assertViewHas('main_menu');
    }
}
```

视图钩子系统为你提供了强大的视图处理能力，让你可以：

- 🎯 在视图渲染的各个阶段插入自定义逻辑
- 🎯 动态注入数据到视图
- 🎯 实现主题和布局的动态切换
- 🎯 优化视图性能和用户体验
- 🎯 实现复杂的视图组合和数据共享

记住，所有的业务逻辑都需要你自己实现！框架只提供基础设施。

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
| **view** | 视图处理 | ⭐⭐⭐⭐ | 视图生命周期、数据注入 |
| **view-composer** | 视图组合器 | ⭐⭐⭐ | 视图数据共享、组合器 |

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

# 视图处理：使用视图模板
php artisan make:hook ViewProcessor --template=view

# 视图组合器：使用视图组合器模板
php artisan make:hook NavigationComposer --template=view-composer
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
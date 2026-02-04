# 视图钩子使用指南

## 🎨 概述

视图钩子系统是钩子框架的重要组成部分，专门为Laravel视图系统设计，提供了强大的视图处理能力。通过视图钩子，你可以在视图的各个生命周期阶段插入自定义逻辑，实现数据注入、主题切换、内容优化等功能。

## 🚀 快速开始

### 1. 创建视图钩子

```bash
# 创建视图处理钩子
php artisan make:hook ViewProcessor --template=view --group=view

# 创建视图组合器钩子
php artisan make:hook NavigationComposer --template=view-composer --group=view
```

### 2. 注册视图钩子

```bash
# 发现并注册钩子
php artisan hook discover
```

### 3. 使用视图钩子管理器

```php
use App\Hooks\View\ViewHookManager;

$viewHookManager = app(ViewHookManager::class);
```

## 📋 视图钩子类型

### 1. 视图渲染前钩子

在视图渲染前执行，用于数据预处理、权限检查等。

```php
$viewHookManager->beforeRender('admin.*', function ($viewName, $data) {
    // 🎯 实现管理员视图前置处理
    return [
        'processed_data' => array_merge($data, [
            'admin_menu' => $this->getAdminMenu(),
            'system_notifications' => $this->getSystemNotifications()
        ])
    ];
});
```

### 2. 视图渲染后钩子

在视图渲染后执行，用于内容优化、SEO处理等。

```php
$viewHookManager->afterRender('public.*', function ($viewName, $data, $options) {
    $content = $options['rendered_content'];
    
    // 🎯 实现SEO优化处理
    $optimizedContent = $this->addSeoTags($content, $data);
    
    return ['processed_content' => $optimizedContent];
});
```

### 3. 数据注入钩子

向视图注入全局或特定数据。

```php
// 全局数据注入
$viewHookManager->injectData('*', function ($viewName, $data) {
    // 🎯 实现全局数据注入
    return [
        'injected_data' => [
            'app_config' => config('app'),
            'current_user' => auth()->user(),
            'system_time' => now()
        ]
    ];
});

// 特定视图数据注入
$viewHookManager->injectData('user.*', function ($viewName, $data) {
    // 🎯 实现用户视图数据注入
    return [
        'injected_data' => [
            'user_permissions' => $this->getUserPermissions(),
            'user_notifications' => $this->getUserNotifications()
        ]
    ];
});
```

### 4. 主题切换钩子

动态切换视图主题。

```php
$viewHookManager->switchTheme('*', function ($viewName, $data, $options) {
    // 🎯 实现主题切换逻辑
    $theme = $this->determineTheme($options);
    
    return [
        'theme_switched' => true,
        'active_theme' => $theme,
        'theme_assets' => $this->getThemeAssets($theme)
    ];
});
```

### 5. 模板修改钩子

修改视图模板路径或结构。

```php
$viewHookManager->modifyTemplate('*', function ($viewName, $data, $options) {
    // 🎯 实现模板修改逻辑
    $modifications = [];
    
    // 移动端适配
    if ($this->isMobileDevice()) {
        $modifications['mobile_view'] = $this->getMobileView($viewName);
    }
    
    return ['modifications' => $modifications];
});
```

## 🛠️ 视图钩子模板

### ViewHookTemplate - 完整视图钩子

适用于需要完整视图生命周期管理的场景。

```php
use App\Hooks\Templates\ViewHookTemplate;

/**
 * @hook view.admin.processor
 * @priority 10
 * @group view
 */
class AdminViewHook extends ViewHookTemplate
{
    protected function handleBeforeRender(string $viewName, array $data, array $options): array
    {
        // 🎯 实现管理员视图前置处理
        
        // 权限检查
        if (!$this->checkAdminPermission()) {
            throw new UnauthorizedException('无权访问管理员视图');
        }
        
        // 数据预处理
        $processedData = array_merge($data, [
            'admin_sidebar' => $this->getAdminSidebar(),
            'system_alerts' => $this->getSystemAlerts(),
            'quick_stats' => $this->getQuickStats()
        ]);
        
        return [
            'status' => 'success',
            'processed_data' => $processedData
        ];
    }
    
    protected function handleAfterRender(string $viewName, array $data, array $options): array
    {
        // 🎯 实现管理员视图后置处理
        
        $content = $options['rendered_content'];
        
        // 添加管理员工具栏
        $toolbar = view('admin.partials.toolbar')->render();
        $content = str_replace('</body>', $toolbar . '</body>', $content);
        
        // 添加性能监控代码
        if (config('app.debug')) {
            $debugInfo = $this->getDebugInfo();
            $content = str_replace('</body>', $debugInfo . '</body>', $content);
        }
        
        return [
            'status' => 'success',
            'processed_content' => $content
        ];
    }
    
    protected function handleDataInjection(string $viewName, array $data, array $options): array
    {
        // 🎯 实现管理员视图数据注入
        
        return [
            'injected_data' => [
                'admin_user' => auth()->guard('admin')->user(),
                'admin_permissions' => $this->getAdminPermissions(),
                'system_config' => $this->getSystemConfig()
            ]
        ];
    }
    
    // 🎯 实现你的辅助方法
    private function checkAdminPermission(): bool
    {
        return auth()->guard('admin')->check();
    }
    
    private function getAdminSidebar(): array
    {
        // 获取管理员侧边栏数据
        return [];
    }
    
    private function getSystemAlerts(): array
    {
        // 获取系统警告
        return [];
    }
}
```

### ViewComposerHookTemplate - 视图组合器钩子

适用于视图数据共享和组合器场景。

```php
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
                ['title' => '仪表板', 'url' => '/admin/dashboard', 'icon' => 'dashboard'],
                ['title' => '用户管理', 'url' => '/admin/users', 'icon' => 'users'],
                ['title' => '系统设置', 'url' => '/admin/settings', 'icon' => 'settings']
            ],
            'admin_user' => auth()->guard('admin')->user(),
            'pending_approvals' => $this->getPendingApprovals()
        ];
    }
    
    private function getUserNavigationData(): array
    {
        // 🎯 实现用户导航数据
        $user = auth()->user();
        
        return [
            'user_menu' => [
                ['title' => '个人中心', 'url' => '/profile'],
                ['title' => '我的订单', 'url' => '/orders'],
                ['title' => '设置', 'url' => '/settings']
            ],
            'user_avatar' => $user->avatar ?? '/images/default-avatar.png',
            'unread_notifications' => $this->getUnreadNotifications($user)
        ];
    }
    
    private function getPublicNavigationData(): array
    {
        // 🎯 实现公共导航数据
        return [
            'main_menu' => [
                ['title' => '首页', 'url' => '/'],
                ['title' => '产品', 'url' => '/products'],
                ['title' => '服务', 'url' => '/services'],
                ['title' => '关于我们', 'url' => '/about']
            ],
            'contact_info' => [
                'phone' => config('site.phone'),
                'email' => config('site.email')
            ]
        ];
    }
}
```

## 🎮 在控制器中使用

### 基础用法

```php
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
        $data = [
            'user_count' => User::count(),
            'order_count' => Order::count(),
            'revenue' => Order::sum('amount')
        ];
        
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

### 高级用法

```php
class ProductController extends Controller
{
    public function show(Product $product)
    {
        $data = ['product' => $product];
        
        // 执行数据注入钩子
        $injectionResults = $this->viewHookManager->executeDataInjection('product.show', $data);
        
        // 处理注入的数据
        foreach ($injectionResults as $results) {
            foreach ($results as $result) {
                if (isset($result['injected_data'])) {
                    $data = array_merge($data, $result['injected_data']);
                }
            }
        }
        
        // 渲染视图
        $view = view('products.show', $data);
        $content = $view->render();
        
        // 执行渲染后钩子
        $afterResults = $this->viewHookManager->executeAfterRender('product.show', $content, $data);
        
        // 处理渲染后的内容
        foreach ($afterResults as $results) {
            foreach ($results as $result) {
                if (isset($result['processed_content'])) {
                    $content = $result['processed_content'];
                }
            }
        }
        
        return response($content);
    }
}
```

## 🎨 Blade指令

视图钩子系统提供了便捷的Blade指令。

### @hook 指令

在模板中执行钩子：

```blade
{{-- 执行自定义钩子 --}}
@hook('view.custom.widget', ['widget_id' => 1])

{{-- 带多个参数的钩子 --}}
@hook('view.user.profile', $user, ['show_private' => true])
```

### @hookData 指令

注入数据到视图：

```blade
{{-- 注入用户相关数据 --}}
@hookData('user.dashboard', ['user_id' => $user->id])

{{-- 注入系统配置 --}}
@hookData('system.config', ['refresh' => true])
```

### @hookBefore 和 @hookAfter 指令

在模板的特定位置执行钩子：

```blade
{{-- 渲染前钩子 --}}
@hookBefore('dashboard.widgets')

<div class="dashboard-content">
    <h1>仪表板</h1>
    
    <div class="widgets">
        <!-- 仪表板组件 -->
    </div>
</div>

{{-- 渲染后钩子 --}}
@hookAfter('dashboard.widgets')
```

### @ifHook 条件指令

根据钩子存在性条件渲染：

```blade
@ifhook('feature.advanced_dashboard')
    <div class="advanced-features">
        <h2>高级功能</h2>
        <!-- 高级功能组件 -->
    </div>
@endifhook

@ifhook('user.premium')
    <div class="premium-content">
        <!-- 高级用户内容 -->
    </div>
@else
    <div class="upgrade-prompt">
        <!-- 升级提示 -->
    </div>
@endifhook
```

## 🔧 视图宏

视图钩子系统还提供了视图宏支持：

### withHook 宏

```php
// 在控制器中使用
return view('admin.users.index')
    ->withHook('admin.users.data', ['filter' => 'active'])
    ->with('users', $users);
```

### withTheme 宏

```php
// 动态切换主题
return view('dashboard.index')
    ->withTheme('dark')
    ->with('data', $data);
```

### withLayout 宏

```php
// 动态切换布局
return view('content.page')
    ->withLayout('admin.layout')
    ->with('content', $content);
```

## 📊 批量注册视图钩子

在服务提供者中批量注册：

```php
// app/Providers/ViewHookServiceProvider.php
public function boot()
{
    $viewHookManager = app(ViewHookManager::class);
    
    $hooks = [
        [
            'type' => 'before_render',
            'pattern' => 'admin.*',
            'callback' => function ($viewName, $data) {
                // 🎯 管理员视图前置处理
                return [
                    'processed_data' => array_merge($data, [
                        'admin_sidebar' => $this->getAdminSidebar(),
                        'system_notifications' => $this->getSystemNotifications()
                    ])
                ];
            },
            'priority' => 5
        ],
        [
            'type' => 'inject_data',
            'pattern' => 'emails.*',
            'callback' => function ($viewName, $data) {
                // 🎯 邮件视图数据注入
                return [
                    'injected_data' => [
                        'email_config' => config('mail'),
                        'tracking_pixel' => $this->getTrackingPixel()
                    ]
                ];
            },
            'priority' => 10
        ],
        [
            'type' => 'after_render',
            'pattern' => 'reports.*',
            'callback' => function ($viewName, $data, $options) {
                // 🎯 报表视图后处理
                $content = $options['rendered_content'];
                $content = $this->addReportWatermark($content);
                
                return ['processed_content' => $content];
            },
            'priority' => 15
        ]
    ];
    
    $viewHookManager->registerBatch($hooks);
}
```

## 🔍 视图钩子统计和调试

### 获取统计信息

```php
$stats = $viewHookManager->getViewHookStats();

echo "总视图钩子数: {$stats['total_view_hooks']}\n";
echo "按类型统计:\n";
foreach ($stats['by_type'] as $type => $count) {
    echo "  {$type}: {$count}\n";
}
echo "按模式统计:\n";
foreach ($stats['by_pattern'] as $pattern => $count) {
    echo "  {$pattern}: {$count}\n";
}
```

### 调试视图钩子

```php
// 启用调试模式
config(['hooks.debug_mode' => true]);

// 在钩子中添加调试信息
class DebugViewHook extends ViewHookTemplate
{
    public function handle(...$args)
    {
        if (config('hooks.debug_mode')) {
            Log::debug('视图钩子开始执行', [
                'hook' => static::class,
                'args' => $args
            ]);
        }
        
        $result = parent::handle(...$args);
        
        if (config('hooks.debug_mode')) {
            Log::debug('视图钩子执行完成', [
                'hook' => static::class,
                'result' => $result
            ]);
        }
        
        return $result;
    }
}
```

## 🚀 性能优化

### 缓存视图数据

```php
class CachedViewHook extends ViewHookTemplate
{
    protected function handleDataInjection(string $viewName, array $data, array $options): array
    {
        // 🎯 使用缓存提高性能
        $cacheKey = "view_data_{$viewName}_" . md5(serialize($data));
        
        $injectedData = cache()->remember($cacheKey, 300, function () use ($viewName, $data) {
            return $this->getExpensiveData($viewName, $data);
        });
        
        return ['injected_data' => $injectedData];
    }
}
```

### 延迟加载数据

```php
class LazyViewHook extends ViewHookTemplate
{
    protected function handleBeforeRender(string $viewName, array $data, array $options): array
    {
        // 🎯 延迟加载非关键数据
        $data['lazy_sidebar'] = function () {
            return $this->getSidebarData();
        };
        
        $data['lazy_widgets'] = function () {
            return $this->getWidgetData();
        };
        
        return ['processed_data' => $data];
    }
}
```

## ✅ 最佳实践

### 1. 错误处理

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
            
            // 返回默认结果，确保视图正常渲染
            return [
                'status' => 'error',
                'message' => '钩子执行失败，使用默认数据'
            ];
        }
    }
}
```

### 2. 性能监控

```php
class PerformanceViewHook extends ViewHookTemplate
{
    public function handle(...$args)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $result = parent::handle(...$args);
        
        $executionTime = microtime(true) - $startTime;
        $memoryUsage = memory_get_usage(true) - $startMemory;
        
        // 🎯 记录性能数据
        if ($executionTime > 0.1) { // 超过100ms记录警告
            Log::warning('视图钩子执行缓慢', [
                'hook' => static::class,
                'execution_time' => $executionTime,
                'memory_usage' => $memoryUsage
            ]);
        }
        
        return $result;
    }
}
```

### 3. 条件执行

```php
class ConditionalViewHook extends ViewHookTemplate
{
    protected function handleBeforeRender(string $viewName, array $data, array $options): array
    {
        // 🎯 根据条件决定是否执行
        
        // 只在生产环境执行某些逻辑
        if (app()->environment('production')) {
            $data['analytics'] = $this->getAnalyticsCode();
        }
        
        // 只为认证用户执行
        if (auth()->check()) {
            $data['user_specific_data'] = $this->getUserSpecificData();
        }
        
        // 根据用户权限执行
        if (auth()->user()?->can('view-admin-data')) {
            $data['admin_data'] = $this->getAdminData();
        }
        
        return ['processed_data' => $data];
    }
}
```

## 🧪 测试视图钩子

```php
// tests/Feature/ViewHookTest.php
class ViewHookTest extends TestCase
{
    public function test_admin_view_hook_adds_admin_data()
    {
        // 🎯 测试管理员视图钩子
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'admin');
        
        $response = $this->get('/admin/dashboard');
        
        $response->assertOk();
        $response->assertViewHas('admin_sidebar');
        $response->assertViewHas('system_notifications');
    }
    
    public function test_view_composer_injects_navigation_data()
    {
        // 🎯 测试视图组合器
        $response = $this->get('/');
        
        $response->assertOk();
        $response->assertViewHas('main_menu');
        $response->assertViewHas('contact_info');
    }
    
    public function test_view_hook_performance()
    {
        // 🎯 测试视图钩子性能
        $startTime = microtime(true);
        
        $response = $this->get('/dashboard');
        
        $executionTime = microtime(true) - $startTime;
        
        $this->assertLessThan(0.5, $executionTime, '视图钩子执行时间应小于500ms');
        $response->assertOk();
    }
}
```

## 📝 总结

视图钩子系统为Laravel应用提供了强大的视图处理能力：

- 🎯 **视图生命周期管理** - 在渲染前后插入自定义逻辑
- 🎯 **数据注入** - 动态向视图注入全局或特定数据
- 🎯 **主题切换** - 实现动态主题和布局切换
- 🎯 **内容优化** - 对渲染后的内容进行优化处理
- 🎯 **组合器支持** - 强大的视图数据共享机制
- 🎯 **Blade指令** - 便捷的模板内钩子调用
- 🎯 **性能优化** - 缓存和延迟加载支持

记住，视图钩子系统只提供框架基础设施，所有的业务逻辑都需要你在 `app/Hooks/Custom/` 目录下自己实现！

开始使用视图钩子来构建更灵活、更强大的视图系统吧！🚀
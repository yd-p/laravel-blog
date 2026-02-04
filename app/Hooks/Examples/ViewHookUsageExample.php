<?php

namespace App\Hooks\Examples;

use App\Hooks\View\ViewHookManager;
use App\Hooks\Facades\Hook;
use Illuminate\Support\Facades\View;

/**
 * 视图钩子使用示例
 * 展示如何在视图系统中使用钩子
 */
class ViewHookUsageExample
{
    protected ViewHookManager $viewHookManager;

    public function __construct(ViewHookManager $viewHookManager)
    {
        $this->viewHookManager = $viewHookManager;
    }

    /**
     * 示例1: 基础视图钩子注册
     */
    public function basicViewHookExample()
    {
        echo "=== 基础视图钩子示例 ===\n";

        // 注册视图渲染前钩子
        $this->viewHookManager->beforeRender('admin.*', function ($viewName, $data) {
            // TODO: 用户实现管理员视图的前置处理逻辑
            echo "管理员视图 {$viewName} 渲染前处理\n";
            
            return [
                'processed_data' => array_merge($data, [
                    'admin_menu' => ['dashboard', 'users', 'settings'],
                    'admin_notifications' => 5
                ])
            ];
        });

        // 注册视图渲染后钩子
        $this->viewHookManager->afterRender('admin.*', function ($viewName, $data, $options) {
            // TODO: 用户实现管理员视图的后置处理逻辑
            echo "管理员视图 {$viewName} 渲染后处理\n";
            
            return [
                'processed_content' => $options['rendered_content'] . "\n<!-- Admin view processed -->"
            ];
        });

        echo "基础视图钩子注册完成\n\n";
    }

    /**
     * 示例2: 数据注入钩子
     */
    public function dataInjectionExample()
    {
        echo "=== 数据注入钩子示例 ===\n";

        // 为所有视图注入全局数据
        $this->viewHookManager->injectData('*', function ($viewName, $data) {
            // TODO: 用户实现全局数据注入逻辑
            return [
                'injected_data' => [
                    'app_name' => config('app.name'),
                    'current_time' => now()->format('Y-m-d H:i:s'),
                    'user_count' => 1000, // 示例数据
                    'online_users' => 50   // 示例数据
                ]
            ];
        });

        // 为用户相关视图注入特定数据
        $this->viewHookManager->injectData('user.*', function ($viewName, $data) {
            // TODO: 用户实现用户视图特定数据注入逻辑
            $user = auth()->user();
            
            return [
                'injected_data' => [
                    'user_profile' => $user ? $user->toArray() : null,
                    'user_permissions' => $user ? ['read', 'write'] : ['read'], // 示例权限
                    'user_preferences' => ['theme' => 'dark', 'language' => 'zh-CN']
                ]
            ];
        });

        echo "数据注入钩子注册完成\n\n";
    }

    /**
     * 示例3: 视图组合器钩子
     */
    public function viewComposerExample()
    {
        echo "=== 视图组合器钩子示例 ===\n";

        // 注册导航菜单组合器
        Hook::register('view.composer.navigation', function ($viewPattern, $data) {
            // TODO: 用户实现导航菜单组合器逻辑
            
            View::composer('layouts.navigation', function ($view) {
                $view->with([
                    'main_menu' => [
                        ['title' => '首页', 'url' => '/'],
                        ['title' => '产品', 'url' => '/products'],
                        ['title' => '关于', 'url' => '/about']
                    ],
                    'user_menu' => auth()->check() ? [
                        ['title' => '个人中心', 'url' => '/profile'],
                        ['title' => '设置', 'url' => '/settings'],
                        ['title' => '退出', 'url' => '/logout']
                    ] : [
                        ['title' => '登录', 'url' => '/login'],
                        ['title' => '注册', 'url' => '/register']
                    ]
                ]);
            });

            return ['composer_registered' => true];
        });

        // 执行组合器钩子
        Hook::execute('view.composer.navigation', 'layouts.navigation', []);

        echo "视图组合器钩子执行完成\n\n";
    }

    /**
     * 示例4: 主题切换钩子
     */
    public function themeSwitchingExample()
    {
        echo "=== 主题切换钩子示例 ===\n";

        // 注册主题切换钩子
        $this->viewHookManager->switchTheme('*', function ($viewName, $data, $options) {
            // TODO: 用户实现主题切换逻辑
            
            $theme = $options['theme'] ?? 'default';
            
            // 根据用户偏好或请求参数切换主题
            if (request()->has('theme')) {
                $theme = request()->get('theme');
            } elseif (auth()->check()) {
                $theme = auth()->user()->preferred_theme ?? 'default';
            }

            return [
                'theme_switched' => true,
                'active_theme' => $theme,
                'theme_assets' => [
                    'css' => "/themes/{$theme}/app.css",
                    'js' => "/themes/{$theme}/app.js"
                ]
            ];
        });

        echo "主题切换钩子注册完成\n\n";
    }

    /**
     * 示例5: 模板修改钩子
     */
    public function templateModificationExample()
    {
        echo "=== 模板修改钩子示例 ===\n";

        // 注册模板修改钩子
        $this->viewHookManager->modifyTemplate('*', function ($viewName, $data, $options) {
            // TODO: 用户实现模板修改逻辑
            
            $modifications = [];

            // 移动端视图切换
            if ($this->isMobileDevice()) {
                $modifications['mobile_view'] = str_replace('.', '.mobile.', $viewName);
            }

            // A/B测试视图切换
            if (session('ab_test_group') === 'B') {
                $modifications['ab_test_view'] = $viewName . '_variant_b';
            }

            // 多语言视图切换
            $locale = app()->getLocale();
            if ($locale !== 'en') {
                $modifications['localized_view'] = "{$locale}.{$viewName}";
            }

            return [
                'modifications' => $modifications,
                'modification_count' => count($modifications)
            ];
        });

        echo "模板修改钩子注册完成\n\n";
    }

    /**
     * 示例6: 批量注册视图钩子
     */
    public function batchRegistrationExample()
    {
        echo "=== 批量注册视图钩子示例 ===\n";

        $hooks = [
            [
                'type' => 'before_render',
                'pattern' => 'dashboard.*',
                'callback' => function ($viewName, $data) {
                    // TODO: 用户实现仪表板视图前置处理
                    return ['dashboard_data' => $this->getDashboardData()];
                },
                'priority' => 5
            ],
            [
                'type' => 'inject_data',
                'pattern' => 'reports.*',
                'callback' => function ($viewName, $data) {
                    // TODO: 用户实现报表视图数据注入
                    return ['report_config' => $this->getReportConfig()];
                },
                'priority' => 10
            ],
            [
                'type' => 'after_render',
                'pattern' => 'emails.*',
                'callback' => function ($viewName, $data, $options) {
                    // TODO: 用户实现邮件视图后置处理
                    return ['email_tracking' => $this->addEmailTracking($options['rendered_content'])];
                },
                'priority' => 15
            ]
        ];

        $registeredIds = $this->viewHookManager->registerBatch($hooks);
        
        echo "批量注册了 " . count($registeredIds) . " 个视图钩子\n\n";
    }

    /**
     * 示例7: 视图钩子执行
     */
    public function hookExecutionExample()
    {
        echo "=== 视图钩子执行示例 ===\n";

        $viewName = 'admin.dashboard';
        $viewData = ['user_count' => 100, 'order_count' => 50];

        // 执行渲染前钩子
        $beforeResults = $this->viewHookManager->executeBeforeRender($viewName, $viewData);
        echo "渲染前钩子执行结果: " . json_encode($beforeResults, JSON_UNESCAPED_UNICODE) . "\n";

        // 模拟视图渲染
        $renderedContent = "<html><body>Dashboard Content</body></html>";

        // 执行渲染后钩子
        $afterResults = $this->viewHookManager->executeAfterRender($viewName, $renderedContent, $viewData);
        echo "渲染后钩子执行结果: " . json_encode($afterResults, JSON_UNESCAPED_UNICODE) . "\n";

        // 执行数据注入钩子
        $injectionResults = $this->viewHookManager->executeDataInjection($viewName, $viewData);
        echo "数据注入钩子执行结果: " . json_encode($injectionResults, JSON_UNESCAPED_UNICODE) . "\n\n";
    }

    /**
     * 示例8: 视图钩子统计
     */
    public function hookStatsExample()
    {
        echo "=== 视图钩子统计示例 ===\n";

        $stats = $this->viewHookManager->getViewHookStats();
        
        echo "视图钩子统计信息:\n";
        echo "总钩子数: {$stats['total_view_hooks']}\n";
        echo "按类型统计:\n";
        foreach ($stats['by_type'] as $type => $count) {
            echo "  {$type}: {$count}\n";
        }
        echo "按模式统计:\n";
        foreach ($stats['by_pattern'] as $pattern => $count) {
            echo "  {$pattern}: {$count}\n";
        }
        echo "\n";
    }

    /**
     * 示例9: Blade指令使用
     */
    public function bladeDirectiveExample()
    {
        echo "=== Blade指令使用示例 ===\n";

        echo "在Blade模板中可以使用以下指令:\n\n";

        echo "1. @hook 指令 - 执行钩子:\n";
        echo "   @hook('view.custom.hook', \$data)\n\n";

        echo "2. @hookData 指令 - 注入数据:\n";
        echo "   @hookData('user.profile', ['user_id' => \$user->id])\n\n";

        echo "3. @hookBefore 指令 - 渲染前钩子:\n";
        echo "   @hookBefore('dashboard.widgets')\n\n";

        echo "4. @hookAfter 指令 - 渲染后钩子:\n";
        echo "   @hookAfter('dashboard.widgets')\n\n";

        echo "5. @ifHook 指令 - 条件钩子:\n";
        echo "   @ifhook('feature.enabled')\n";
        echo "       <!-- 功能启用时显示的内容 -->\n";
        echo "   @endifhook\n\n";
    }

    /**
     * 运行所有示例
     */
    public function runAllExamples()
    {
        echo "=== 视图钩子系统使用示例 ===\n\n";

        $this->basicViewHookExample();
        $this->dataInjectionExample();
        $this->viewComposerExample();
        $this->themeSwitchingExample();
        $this->templateModificationExample();
        $this->batchRegistrationExample();
        $this->hookExecutionExample();
        $this->hookStatsExample();
        $this->bladeDirectiveExample();

        echo "=== 所有视图钩子示例执行完成 ===\n";
        echo "注意：以上示例仅展示框架用法，所有业务逻辑都需要用户自己实现\n";
    }

    // 辅助方法（示例实现）

    protected function isMobileDevice(): bool
    {
        return false; // 示例实现
    }

    protected function getDashboardData(): array
    {
        // TODO: 用户实现仪表板数据获取逻辑
        return ['widgets' => [], 'stats' => []];
    }

    protected function getReportConfig(): array
    {
        // TODO: 用户实现报表配置获取逻辑
        return ['charts' => [], 'filters' => []];
    }

    protected function addEmailTracking(string $content): string
    {
        // TODO: 用户实现邮件跟踪逻辑
        return $content . '<!-- Email tracking pixel -->';
    }
}
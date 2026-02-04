<?php

namespace App\Hooks\Examples;

use App\Hooks\HookManager;
use App\Hooks\Facades\Hook;

/**
 * 钩子使用示例
 * 展示如何在实际项目中使用钩子系统（不包含具体业务逻辑）
 */
class HookUsageExample
{
    protected HookManager $hookManager;

    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
    }

    /**
     * 示例1: 基础钩子注册和执行
     */
    public function basicExample()
    {
        // 注册一个简单的钩子（用户需要实现具体逻辑）
        Hook::register('user.welcome', function ($user) {
            // TODO: 用户在这里实现欢迎逻辑
            return "欢迎 {$user->name}！";
        });

        // 执行钩子
        $user = (object) ['name' => '张三'];
        $result = Hook::execute('user.welcome', $user);
        
        echo "执行结果: " . $result->getFirstResult() . "\n";
    }

    /**
     * 示例2: 批量注册钩子
     */
    public function batchRegistrationExample()
    {
        $hooks = [
            'user.login' => [
                'callback' => function ($user) {
                    // TODO: 用户实现登录后的处理逻辑
                    return ['status' => 'logged', 'user_id' => $user->id ?? 1];
                },
                'priority' => 10,
                'group' => 'auth'
            ],
            'user.logout' => [
                'callback' => function ($user) {
                    // TODO: 用户实现登出后的处理逻辑
                    return ['status' => 'logged_out', 'user_id' => $user->id ?? 1];
                },
                'priority' => 10,
                'group' => 'auth'
            ]
        ];

        Hook::registerBatch($hooks);
        
        echo "批量注册完成\n";
    }

    /**
     * 示例3: 在控制器中使用钩子
     */
    public function controllerExample()
    {
        // 模拟用户登录控制器
        $user = (object) ['id' => 1, 'email' => 'user@example.com'];

        // 登录前钩子 - 用户可以在这里实现登录前的验证逻辑
        Hook::register('user.login.before', function ($user) {
            // TODO: 用户实现登录前验证逻辑
            return ['validation' => 'passed'];
        });

        $beforeResult = Hook::execute('user.login.before', $user);
        
        if ($beforeResult->hasErrors()) {
            echo "登录前检查失败\n";
            return;
        }

        // 执行登录逻辑...
        echo "用户登录成功\n";

        // 登录后钩子 - 用户可以在这里实现登录后的处理逻辑
        Hook::register('user.login.after', function ($user, $ip) {
            // TODO: 用户实现登录后处理逻辑
            return ['logged_at' => now(), 'ip' => $ip];
        });

        $afterResult = Hook::execute('user.login.after', $user, '127.0.0.1');
        
        echo "登录后处理完成，执行了 {$afterResult->getExecutedCount()} 个钩子\n";
    }

    /**
     * 示例4: 在模型事件中使用钩子
     */
    public function modelEventExample()
    {
        echo "模型事件钩子示例：\n";
        echo "在 User 模型的 boot 方法中添加以下代码：\n\n";
        
        echo "static::created(function (\$user) {\n";
        echo "    Hook::execute('user.created', \$user);\n";
        echo "});\n\n";
        
        echo "static::updated(function (\$user) {\n";
        echo "    Hook::execute('user.updated', \$user, \$user->getChanges());\n";
        echo "});\n\n";
        
        echo "然后用户可以创建钩子类来处理这些事件\n";
    }

    /**
     * 示例5: 插件系统中使用钩子
     */
    public function pluginSystemExample()
    {
        $pluginName = 'TestPlugin';
        $pluginInfo = ['name' => $pluginName, 'version' => '1.0.0'];

        // 注册插件钩子（用户需要实现具体逻辑）
        Hook::register('plugin.installed', function ($name, $info) {
            // TODO: 用户实现插件安装后的处理逻辑
            return ['plugin' => $name, 'processed' => true];
        });

        Hook::register('plugin.enabled', function ($name, $info) {
            // TODO: 用户实现插件启用后的处理逻辑
            return ['plugin' => $name, 'enabled' => true];
        });

        // 执行钩子
        $result = Hook::execute('plugin.installed', $pluginName, $pluginInfo);
        echo "插件安装钩子执行完成\n";

        $result = Hook::execute('plugin.enabled', $pluginName, $pluginInfo);
        echo "插件启用钩子执行完成\n";
    }

    /**
     * 示例6: 条件钩子执行
     */
    public function conditionalHookExample()
    {
        // 注册带条件的钩子（用户实现具体的条件判断逻辑）
        Hook::register('order.created', function ($order) {
            // TODO: 用户实现订单创建后的处理逻辑
            if ($order->amount > 1000) {
                // 高价值订单的特殊处理
                return ['action' => 'high_value_notification', 'order_id' => $order->id];
            }
            return ['action' => 'normal_processing', 'order_id' => $order->id];
        });

        // 执行钩子
        $order = (object) ['id' => 1, 'amount' => 1500];
        $result = Hook::execute('order.created', $order);
        
        echo "订单处理结果: " . json_encode($result->getFirstResult()) . "\n";
    }

    /**
     * 示例7: 钩子中间件使用
     */
    public function middlewareExample()
    {
        // 为特定钩子添加中间件（用户实现具体的权限检查逻辑）
        Hook::addMiddleware('admin.action', function ($hookName, $hookId, $args) {
            // TODO: 用户实现权限检查逻辑
            // 这里只是示例，实际需要根据业务需求实现
            $isAdmin = true; // 模拟权限检查结果
            
            if (!$isAdmin) {
                echo "权限不足，阻止执行钩子: {$hookName}\n";
                return false; // 阻止执行
            }
            return true; // 允许执行
        });

        echo "中间件添加完成\n";
    }

    /**
     * 示例8: 钩子统计和监控
     */
    public function monitoringExample()
    {
        // 获取钩子统计信息
        $stats = Hook::getStats();
        
        echo "钩子统计信息:\n";
        echo "总钩子数: {$stats['total_hooks']}\n";
        echo "启用钩子数: {$stats['enabled_hooks']}\n";
        echo "总调用次数: {$stats['total_calls']}\n";

        // 获取特定钩子的详细信息
        $userHooks = Hook::getHooks('user.login');
        echo "用户登录钩子数量: " . count($userHooks) . "\n";
    }

    /**
     * 示例9: 动态钩子管理
     */
    public function dynamicManagementExample()
    {
        // 注册钩子
        $hookId = Hook::register('dynamic.test', function () {
            return 'dynamic hook executed';
        });

        echo "注册钩子ID: {$hookId}\n";

        // 执行钩子
        $result = Hook::execute('dynamic.test');
        echo "执行结果: " . $result->getFirstResult() . "\n";

        // 禁用钩子
        Hook::toggle('dynamic.test', $hookId, false);
        echo "钩子已禁用\n";

        // 再次执行（应该没有结果）
        $result = Hook::execute('dynamic.test');
        echo "禁用后执行数量: " . $result->getExecutedCount() . "\n";

        // 重新启用
        Hook::toggle('dynamic.test', $hookId, true);
        echo "钩子已重新启用\n";

        // 移除钩子
        Hook::remove('dynamic.test', $hookId);
        echo "钩子已移除\n";
    }

    /**
     * 示例10: 使用钩子类
     */
    public function hookClassExample()
    {
        echo "钩子类使用示例：\n\n";
        
        echo "1. 在 app/Hooks/Custom/ 目录下创建钩子类：\n";
        echo "<?php\n";
        echo "namespace App\\Hooks\\Custom;\n";
        echo "use App\\Hooks\\AbstractHook;\n\n";
        echo "/**\n";
        echo " * @hook my.custom.hook\n";
        echo " * @priority 10\n";
        echo " * @group custom\n";
        echo " */\n";
        echo "class MyCustomHook extends AbstractHook\n";
        echo "{\n";
        echo "    public function handle(...\$args)\n";
        echo "    {\n";
        echo "        // TODO: 实现你的业务逻辑\n";
        echo "        return ['status' => 'success'];\n";
        echo "    }\n";
        echo "}\n\n";
        
        echo "2. 运行发现命令：\n";
        echo "php artisan hook discover\n\n";
        
        echo "3. 执行钩子：\n";
        echo "Hook::execute('my.custom.hook', \$data);\n";
    }

    /**
     * 运行所有示例
     */
    public function runAllExamples()
    {
        echo "=== 钩子系统使用示例（框架演示，用户需实现具体业务逻辑）===\n\n";

        echo "1. 基础示例:\n";
        $this->basicExample();
        echo "\n";

        echo "2. 批量注册示例:\n";
        $this->batchRegistrationExample();
        echo "\n";

        echo "3. 控制器使用示例:\n";
        $this->controllerExample();
        echo "\n";

        echo "4. 模型事件示例:\n";
        $this->modelEventExample();
        echo "\n";

        echo "5. 插件系统示例:\n";
        $this->pluginSystemExample();
        echo "\n";

        echo "6. 条件钩子示例:\n";
        $this->conditionalHookExample();
        echo "\n";

        echo "7. 中间件示例:\n";
        $this->middlewareExample();
        echo "\n";

        echo "8. 监控示例:\n";
        $this->monitoringExample();
        echo "\n";

        echo "9. 动态管理示例:\n";
        $this->dynamicManagementExample();
        echo "\n";

        echo "10. 钩子类示例:\n";
        $this->hookClassExample();
        echo "\n";

        echo "=== 所有示例执行完成 ===\n";
        echo "注意：以上示例仅展示框架用法，所有业务逻辑都需要用户自己实现\n";
    }
}
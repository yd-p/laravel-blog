<?php

namespace App\Hooks\Examples;

use App\Hooks\AbstractHook;
use App\Hooks\Attributes\Hook;
use App\Hooks\Attributes\Priority;
use App\Hooks\Attributes\Group;
use App\Hooks\Attributes\Middleware;
use App\Hooks\Attributes\Condition;

/**
 * PHP 8.2 Attribute 钩子示例
 * 
 * 展示如何使用 PHP 8.2 的 Attribute 语法来定义钩子
 */
#[Hook(
    name: 'example.attribute.hook',
    priority: 5,
    group: 'example',
    description: 'PHP 8.2 Attribute 钩子示例',
    enabled: true
)]
#[Middleware(class: 'App\Hooks\Middleware\AuthMiddleware')]
#[Middleware(class: 'App\Hooks\Middleware\LoggingMiddleware', parameters: ['level' => 'info'])]
#[Condition(type: 'environment', value: 'production')]
#[Condition(type: 'auth', value: true)]
#[Condition(type: 'user_role', value: 'admin')]
class AttributeHookExample extends AbstractHook
{
    protected string $description = 'PHP 8.2 Attribute 钩子示例';

    /**
     * 处理钩子逻辑
     * 
     * @param mixed ...$args 钩子参数
     * @return array 处理结果
     */
    public function handle(...$args): array
    {
        // TODO: 实现你的业务逻辑
        
        [$action, $data] = $args;
        
        return [
            'status' => 'success',
            'action' => $action,
            'processed_data' => $this->processData($data),
            'timestamp' => now(),
            'hook_info' => [
                'name' => 'example.attribute.hook',
                'group' => 'example',
                'priority' => 5,
                'middleware_count' => 2,
                'conditions_count' => 3
            ]
        ];
    }

    /**
     * 处理数据
     * 
     * @param mixed $data 输入数据
     * @return mixed 处理后的数据
     */
    private function processData($data)
    {
        // TODO: 实现数据处理逻辑
        
        if (is_array($data)) {
            return array_map('strtoupper', $data);
        }
        
        if (is_string($data)) {
            return strtoupper($data);
        }
        
        return $data;
    }

    /**
     * 参数验证
     * 
     * @param mixed ...$args 钩子参数
     * @return bool 验证是否通过
     */
    protected function validateArgs(...$args): bool
    {
        return count($args) >= 2 && 
               is_string($args[0]) && 
               !empty($args[1]);
    }
}

/**
 * 使用单独 Attribute 的钩子示例
 * 
 * 展示如何使用单独的 Priority 和 Group Attribute
 */
#[Hook(name: 'example.separate.attributes')]
#[Priority(value: 15)]
#[Group(name: 'separate')]
class SeparateAttributesExample extends AbstractHook
{
    public function handle(...$args): array
    {
        return [
            'message' => '使用单独 Attribute 的钩子',
            'priority' => 15,
            'group' => 'separate'
        ];
    }
}

/**
 * 条件钩子示例
 * 
 * 展示如何使用多种条件来控制钩子执行
 */
#[Hook(name: 'example.conditional.hook', group: 'conditional')]
#[Condition(type: 'environment', value: ['production', 'staging'], operator: 'in')]
#[Condition(type: 'time', value: '09:00', operator: '>=')]
#[Condition(type: 'time', value: '18:00', operator: '<=')]
#[Condition(type: 'config', value: 'features.advanced_hooks')]
class ConditionalHookExample extends AbstractHook
{
    public function handle(...$args): array
    {
        return [
            'message' => '条件钩子执行成功',
            'conditions' => [
                'environment' => app()->environment(),
                'current_time' => now()->format('H:i'),
                'feature_enabled' => config('features.advanced_hooks', false)
            ]
        ];
    }
}

/**
 * 自定义条件钩子示例
 * 
 * 展示如何使用自定义条件函数
 */
#[Hook(name: 'example.custom.condition', group: 'custom')]
#[Condition(
    type: 'custom',
    value: [CustomConditionHookExample::class, 'checkCustomCondition']
)]
class CustomConditionHookExample extends AbstractHook
{
    public function handle(...$args): array
    {
        return [
            'message' => '自定义条件钩子执行成功',
            'custom_check' => true
        ];
    }

    /**
     * 自定义条件检查函数
     * 
     * @param string $hookName 钩子名称
     * @param string $hookId 钩子ID
     * @param array $args 钩子参数
     * @return bool 是否满足条件
     */
    public static function checkCustomCondition(string $hookName, string $hookId, array $args): bool
    {
        // TODO: 实现自定义条件逻辑
        
        // 示例：检查用户是否有特定权限
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        // 示例：检查时间范围
        $currentHour = (int) now()->format('H');
        if ($currentHour < 9 || $currentHour > 17) {
            return false;
        }
        
        // 示例：检查参数
        if (empty($args)) {
            return false;
        }
        
        return true;
    }
}

/**
 * 禁用钩子示例
 * 
 * 展示如何通过 Attribute 禁用钩子
 */
#[Hook(
    name: 'example.disabled.hook',
    group: 'disabled',
    enabled: false  // 钩子被禁用
)]
class DisabledHookExample extends AbstractHook
{
    public function handle(...$args): array
    {
        // 这个钩子不会被执行，因为 enabled = false
        return ['message' => '这个钩子被禁用了'];
    }
}
<?php

namespace App\Hooks\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * 钩子认证中间件
 * 检查用户权限，控制钩子执行
 */
class AuthMiddleware
{
    /**
     * 需要认证的钩子列表
     */
    protected array $protectedHooks = [
        'user.delete',
        'admin.action',
        'system.config',
        'plugin.install',
        'plugin.uninstall',
    ];

    /**
     * 钩子权限映射
     */
    protected array $hookPermissions = [
        'user.delete' => 'delete-users',
        'admin.action' => 'admin-access',
        'system.config' => 'manage-system',
        'plugin.install' => 'manage-plugins',
        'plugin.uninstall' => 'manage-plugins',
    ];

    /**
     * 处理钩子认证检查
     * 
     * @param string $hookName 钩子名称
     * @param string $hookId 钩子ID
     * @param array $args 钩子参数
     * @return bool 是否继续执行钩子
     */
    public function __invoke(string $hookName, string $hookId, array $args): bool
    {
        // 检查是否需要认证
        if (!$this->requiresAuth($hookName)) {
            return true; // 不需要认证，继续执行
        }

        // 检查用户是否已登录
        if (!Auth::check()) {
            Log::warning('未认证用户尝试执行受保护的钩子', [
                'hook_name' => $hookName,
                'hook_id' => $hookId,
                'ip' => request()->ip(),
            ]);
            return false; // 阻止执行
        }

        // 检查用户权限
        $permission = $this->hookPermissions[$hookName] ?? null;
        if ($permission && !$this->hasPermission($permission)) {
            Log::warning('用户权限不足，无法执行钩子', [
                'hook_name' => $hookName,
                'hook_id' => $hookId,
                'user_id' => Auth::id(),
                'required_permission' => $permission,
            ]);
            return false; // 阻止执行
        }

        // 记录授权成功的日志
        Log::info('钩子权限验证通过', [
            'hook_name' => $hookName,
            'hook_id' => $hookId,
            'user_id' => Auth::id(),
        ]);

        return true; // 继续执行
    }

    /**
     * 检查钩子是否需要认证
     */
    protected function requiresAuth(string $hookName): bool
    {
        foreach ($this->protectedHooks as $pattern) {
            if (fnmatch($pattern, $hookName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查用户是否有指定权限
     */
    protected function hasPermission(string $permission): bool
    {
        $user = Auth::user();
        
        // 如果用户模型有 hasPermission 方法，使用它
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        // 如果用户模型有 can 方法，使用它
        if (method_exists($user, 'can')) {
            return $user->can($permission);
        }

        // 简单的角色检查（假设有 role 字段）
        if (isset($user->role)) {
            return $user->role === 'admin' || $user->role === 'super_admin';
        }

        return false;
    }

    /**
     * 添加受保护的钩子
     */
    public function addProtectedHook(string $hookName, ?string $permission = null): void
    {
        $this->protectedHooks[] = $hookName;
        
        if ($permission) {
            $this->hookPermissions[$hookName] = $permission;
        }
    }

    /**
     * 移除受保护的钩子
     */
    public function removeProtectedHook(string $hookName): void
    {
        $this->protectedHooks = array_filter($this->protectedHooks, fn($hook) => $hook !== $hookName);
        unset($this->hookPermissions[$hookName]);
    }
}
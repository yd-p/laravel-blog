<?php

namespace Plugins\Post\Hooks;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use App\Plugins\PluginHookInterface;

class PluginHooks implements PluginHookInterface
{
    protected $app;

    // 注入 Laravel 应用实例（可用于访问容器中的服务）
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 插件启用前触发（可用于依赖检查、前置条件验证）
     * @param string $pluginName 插件名称
     * @throws \RuntimeException 若验证失败，可抛出异常中断启用流程
     */
    public function onEnabling(string $pluginName): void
    {
        Log::info("[{$pluginName}] 开始启用（onEnabling）");
    }

    /**
     * 插件启用后触发（可用于初始化资源、注册永久化配置）
     * @param string $pluginName 插件名称
     */
    public function onEnabled(string $pluginName): void
    {
        Log::info("[{$pluginName}] 已启用（onEnabled）");
    }

    /**
     * 插件禁用前触发（可用于数据备份、资源释放）
     * @param string $pluginName 插件名称
     */
    public function onDisabling(string $pluginName): void
    {
        Log::info("[{$pluginName}] 开始禁用（onDisabling）");
    }

    /**
     * 插件禁用后触发（可用于清理临时文件、缓存）
     * @param string $pluginName 插件名称
     */
    public function onDisabled(string $pluginName): void
    {
        Log::info("[{$pluginName}] 已禁用（onDisabled）");
    }

    /**
     * 插件删除前触发（可用于最终数据备份、权限校验）
     * @param string $pluginName 插件名称
     * @throws \RuntimeException 若不允许删除，可抛出异常中断流程
     */
    public function onDeleting(string $pluginName): void
    {
        Log::info("[{$pluginName}] 开始删除（onDeleting）");
    }

    /**
     * 插件删除后触发（可用于清理残留痕迹）
     * @param string $pluginName 插件名称
     */
    public function onDeleted(string $pluginName): void
    {
        Log::info("[{$pluginName}] 已删除（onDeleted）");
    }

    public function onInstalled(string $pluginName): void
    {
        Log::info("[{$pluginName}] 安装成功（onInstalled）");
    }
    public function onUninstalling(string $pluginName): void
    {
        Log::info("[{$pluginName}] 卸载中（onUninstalling）");
    }
    public function onUninstalled(string $pluginName): void
    {
        Log::info("[{$pluginName}] 卸载成功（onUninstalled）");
    }
}

<?php

namespace App\Plugins;

interface PluginHookInterface
{
    /**
     * 插件启用前触发（可中断启用流程）
     * @param string $pluginName 插件名称
     * @throws \RuntimeException 若验证失败，可抛出异常中断启用
     */
    public function onEnabling(string $pluginName): void;

    /**
     * 插件启用后触发（执行初始化逻辑）
     * @param string $pluginName 插件名称
     */
    public function onEnabled(string $pluginName): void;

    /**
     * 插件禁用前触发（执行数据备份等逻辑）
     * @param string $pluginName 插件名称
     */
    public function onDisabling(string $pluginName): void;

    /**
     * 插件禁用后触发（执行清理逻辑）
     * @param string $pluginName 插件名称
     */
    public function onDisabled(string $pluginName): void;

    /**
     * 插件删除前触发（执行最终备份、权限校验）
     * @param string $pluginName 插件名称
     * @throws \RuntimeException 若不允许删除，可抛出异常中断
     */
    public function onDeleting(string $pluginName): void;

    /**
     * 插件删除后触发（执行残留清理）
     * @param string $pluginName 插件名称
     */
    public function onDeleted(string $pluginName): void;

    /**
     * 插件安装后触发（执行基础初始化）
     * @param string $pluginName 插件名称
     */
    public function onInstalled(string $pluginName): void;

    /**
     * 插件卸载前触发（执行数据备份）
     * @param string $pluginName 插件名称
     */
    public function onUninstalling(string $pluginName): void;

    /**
     * 插件卸载后触发（执行配置清理）
     * @param string $pluginName 插件名称
     */
    public function onUninstalled(string $pluginName): void;
}

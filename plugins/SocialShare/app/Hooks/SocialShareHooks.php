<?php

namespace Plugins\SocialShare\Hooks;

use Illuminate\Support\Facades\Log;

class SocialShareHooks
{
    public function onEnabling(string $pluginName): void
    {
        Log::info("[SocialShare] 插件正在启用: {$pluginName}");
    }

    public function onEnabled(string $pluginName): void
    {
        Log::info("[SocialShare] 插件已启用: {$pluginName}");
    }

    public function onDisabling(string $pluginName): void
    {
        Log::info("[SocialShare] 插件正在禁用: {$pluginName}");
    }

    public function onDisabled(string $pluginName): void
    {
        Log::info("[SocialShare] 插件已禁用: {$pluginName}");
    }

    public function onInstalled(string $pluginName): void
    {
        Log::info("[SocialShare] 插件已安装: {$pluginName}");
    }
}

# 插件系统指南

## 📖 目录

- [系统概述](#系统概述)
- [插件结构](#插件结构)
- [创建插件](#创建插件)
- [插件管理](#插件管理)
- [插件钩子](#插件钩子)
- [插件主题](#插件主题)

---

## 系统概述

插件系统允许开发者通过插件扩展系统功能，支持插件的安装、启用、禁用和卸载。

### 核心特性

✅ **自动加载** - PSR-4 自动加载支持  
✅ **服务提供者** - 自动注册服务提供者  
✅ **钩子系统** - 插件生命周期钩子  
✅ **主题支持** - 插件可提供自己的主题  
✅ **依赖管理** - Composer 依赖管理  

---

## 插件结构

```
plugins/YourPlugin/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Providers/
│   │   └── YourPluginServiceProvider.php
│   └── Hooks/
│       └── PluginHooks.php
├── resources/
│   ├── views/
│   └── themes/
│       └── your-theme/
│           ├── theme.json
│           ├── views/
│           └── assets/
├── routes/
│   ├── web.php
│   └── api.php
├── database/
│   ├── migrations/
│   └── seeders/
├── composer.json
└── plugin.json
```

---

## 创建插件

### 1. 创建插件目录

```bash
mkdir -p plugins/YourPlugin/{app/Providers,resources/views,routes,database/migrations}
```

### 2. 创建 composer.json

```json
{
    "name": "your-vendor/your-plugin",
    "description": "插件描述",
    "type": "laravel-plugin",
    "require": {
        "php": ">=8.2"
    },
    "autoload": {
        "psr-4": {
            "YourVendor\\YourPlugin\\": "app/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "YourVendor\\YourPlugin\\Providers\\YourPluginServiceProvider"
            ]
        },
        "hooks": {
            "plugin.enabled": [
                "YourVendor\\YourPlugin\\Hooks\\PluginHooks@onEnabled"
            ],
            "plugin.disabled": [
                "YourVendor\\YourPlugin\\Hooks\\PluginHooks@onDisabled"
            ]
        }
    }
}
```

### 3. 创建 plugin.json

```json
{
    "name": "YourPlugin",
    "version": "1.0.0",
    "description": "插件描述",
    "author": "Your Name",
    "homepage": "https://example.com",
    "keywords": ["laravel", "plugin"],
    "license": "MIT",
    "require": {
        "php": ">=8.2",
        "laravel": ">=12.0"
    }
}
```

### 4. 创建服务提供者

```php
<?php

namespace YourVendor\YourPlugin\Providers;

use Illuminate\Support\ServiceProvider;

class YourPluginServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 注册服务
    }

    public function boot()
    {
        // 加载路由
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        
        // 加载视图
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'yourplugin');
        
        // 加载迁移
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        
        // 发布资源
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/yourplugin'),
        ], 'yourplugin-views');
    }
}
```

---

## 插件管理

### 启用插件

```bash
# 方法1: 手动添加到 installed.json
echo '["YourPlugin"]' > plugins/installed.json

# 方法2: 通过代码
app(PluginsManager::class)->enable('YourPlugin');
```

### 禁用插件

```php
app(PluginsManager::class)->disable('YourPlugin');
```

### 检查插件状态

```php
$manager = app(PluginsManager::class);

// 检查是否已安装
if ($manager->isInstalled('YourPlugin')) {
    echo "插件已安装";
}

// 获取插件信息
$info = $manager->getPluginInfo('YourPlugin');
```

---

## 插件钩子

插件可以监听生命周期钩子：

### 可用钩子

- `plugin.installing` - 插件安装前
- `plugin.installed` - 插件安装后
- `plugin.enabling` - 插件启用前
- `plugin.enabled` - 插件启用后
- `plugin.disabling` - 插件禁用前
- `plugin.disabled` - 插件禁用后
- `plugin.uninstalling` - 插件卸载前
- `plugin.uninstalled` - 插件卸载后

### 实现钩子

```php
<?php

namespace YourVendor\YourPlugin\Hooks;

class PluginHooks
{
    public function onEnabled($pluginName)
    {
        // 插件启用时执行
        // - 运行迁移
        // - 发布资源
        // - 清理缓存
    }

    public function onDisabled($pluginName)
    {
        // 插件禁用时执行
        // - 清理数据
        // - 移除缓存
    }
}
```

---

## 插件主题

插件可以提供自己的主题，详见 [主题系统文档](02-theme-system.md)。

### 创建插件主题

```bash
mkdir -p plugins/YourPlugin/resources/themes/your-theme/{views,assets/css}
```

### 主题配置

```json
{
    "name": "你的主题",
    "slug": "your-theme",
    "version": "1.0.0",
    "plugin": "YourPlugin",
    "colors": {
        "primary": "#6366f1"
    }
}
```

### 激活插件主题

```bash
php artisan theme:switch YourPlugin::your-theme
php artisan theme:compile
```

---

## 示例：Post 插件

系统已包含完整的 Post 插件示例：

```
plugins/Post/
├── app/
│   ├── Hooks/
│   │   └── PluginHooks.php
│   ├── Http/Controllers/
│   ├── Models/
│   └── Providers/
├── resources/
│   ├── views/
│   └── themes/
│       └── blog/
├── routes/
├── composer.json
└── plugin.json
```

---

## 相关文档

- [快速开始指南](01-getting-started.md)
- [主题系统](02-theme-system.md)
- [钩子系统](03-hook-system.md)

---

**下一篇**: [FilamentPHP 指南](05-filament-guide.md)

# 主题系统完整指南

## 📖 目录

- [系统概述](#系统概述)
- [主题优先级](#主题优先级)
- [快速开始](#快速开始)
- [开发插件主题](#开发插件主题)
- [API 参考](#api-参考)
- [最佳实践](#最佳实践)
- [故障排除](#故障排除)

---

## 系统概述

Laravel CMS 实现了强大的**多主题架构**，支持系统主题和插件主题，允许开发者在插件中提供自己的主题，完全覆盖C端的视觉呈现。

### 核心特性

✅ **主题优先级系统** - 插件主题 → 系统主题 → 默认视图  
✅ **完全独立** - 每个插件可以提供多个主题  
✅ **视图覆盖** - 支持完全或部分覆盖  
✅ **资源管理** - 自动编译和发布  
✅ **热切换** - 无需重启即可切换主题  

---

## 主题优先级

系统按以下优先级查找视图：

```
1. 插件主题视图 (最高优先级)
   plugins/Post/resources/themes/blog/views/posts/show.blade.php

2. 系统主题视图
   resources/themes/default/views/posts/show.blade.php

3. 默认视图
   resources/views/posts/show.blade.php
```

---

## 快速开始

### 查看可用主题

```bash
# 命令行方式
php artisan theme:list

# 浏览器方式（开发环境）
http://localhost:8000/theme/list
```

### 切换主题

```bash
# 方法1: 命令行
php artisan theme:switch Post::blog

# 方法2: 环境变量
echo "THEME_CURRENT=Post::blog" >> .env

# 方法3: 浏览器（开发环境）
http://localhost:8000/theme/switch/Post::blog

# 方法4: 代码
app('theme')->setCurrentTheme('Post::blog');
```

### 编译主题资源

```bash
# 编译当前主题
php artisan theme:compile

# 编译指定主题
php artisan theme:compile Post::blog

# 编译所有主题
php artisan theme:compile --all
```

---

## 开发插件主题

### 目录结构

```
plugins/YourPlugin/resources/themes/your-theme/
├── theme.json              # 主题配置文件
├── views/                  # 视图文件
│   ├── layouts/
│   │   └── app.blade.php
│   ├── home.blade.php
│   └── posts/
│       ├── index.blade.php
│       └── show.blade.php
└── assets/                 # 资源文件
    ├── css/
    │   └── style.css
    ├── js/
    │   └── app.js
    └── images/
```

### 步骤1: 创建主题目录

```bash
mkdir -p plugins/YourPlugin/resources/themes/your-theme/{views/layouts,assets/css,assets/js}
```

### 步骤2: 创建配置文件

创建 `theme.json`:

```json
{
    "name": "你的主题",
    "slug": "your-theme",
    "version": "1.0.0",
    "description": "主题描述",
    "author": "你的名字",
    "plugin": "YourPlugin",
    "colors": {
        "primary": "#6366f1",
        "secondary": "#8b5cf6",
        "success": "#10b981",
        "danger": "#ef4444"
    },
    "fonts": {
        "body": "Inter, sans-serif",
        "heading": "Poppins, sans-serif",
        "mono": "Fira Code, monospace"
    },
    "settings": {
        "layout": "boxed",
        "sidebar": "right"
    },
    "features": [
        "responsive",
        "dark-mode",
        "accessibility"
    ]
}
```

### 步骤3: 创建布局文件

创建 `views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    
    <!-- 引用主题样式 -->
    <link href="{{ app('theme')->asset('css/style.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    <!-- 导航栏 -->
    <nav>
        <div class="container">
            <a href="/">{{ config('app.name') }}</a>
            <!-- 导航菜单 -->
        </div>
    </nav>

    <!-- 主要内容 -->
    <main>
        @yield('content')
    </main>

    <!-- 页脚 -->
    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
```

### 步骤4: 创建样式文件

创建 `assets/css/style.css`:

```css
:root {
    --color-primary: #6366f1;
    --color-secondary: #8b5cf6;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
    color: #1f2937;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .container {
        padding: 0 0.5rem;
    }
}
```

### 步骤5: 激活主题

```bash
php artisan theme:switch YourPlugin::your-theme
php artisan theme:compile
```

---

## 主题命名规范

### 系统主题
简单名称：
```
default
modern
minimal
```

### 插件主题
格式：`PluginName::theme-name`

示例：
```
Post::blog
Shop::modern
Forum::dark
```

---

## 视图覆盖

### 完全覆盖

插件主题可以完全覆盖系统主题的任何视图：

```
系统主题视图:
resources/themes/default/views/posts/show.blade.php

插件主题覆盖（优先级更高）:
plugins/Post/resources/themes/blog/views/posts/show.blade.php
```

### 部分覆盖

只覆盖需要的视图，其他自动回退：

```
plugins/Post/resources/themes/blog/views/
├── layouts/
│   └── app.blade.php        # 覆盖布局
└── posts/
    └── show.blade.php       # 只覆盖详情页

# posts/index.blade.php 会使用系统主题
```

---

## API 参考

### ThemeService 方法

```php
// 获取主题服务实例
$theme = app('theme');

// 获取当前主题
$current = $theme->getCurrentTheme();

// 设置主题
$theme->setCurrentTheme('Post::blog');

// 检查是否为插件主题
$isPlugin = $theme->isPluginTheme('Post::blog'); // true

// 解析插件主题
$parsed = $theme->parsePluginTheme('Post::blog');
// 返回: ['plugin' => 'Post', 'theme' => 'blog']

// 获取所有可用主题
$themes = $theme->getAvailableThemes();

// 获取插件主题列表
$pluginThemes = $theme->getPluginThemes();

// 获取主题配置
$config = $theme->getThemeConfig();
$colors = $theme->getColors();
$fonts = $theme->getFonts();

// 获取主题资源 URL
$assetUrl = $theme->asset('css/style.css');

// 编译主题资源
$theme->compileAssets('Post::blog');
```

### 在控制器中使用

```php
use App\Services\ThemeService;

class HomeController extends Controller
{
    public function index(ThemeService $theme)
    {
        // 获取当前主题
        $current = $theme->getCurrentTheme();
        
        // 获取主题配置
        $colors = $theme->getColors();
        $fonts = $theme->getFonts();
        
        // 获取所有主题
        $themes = $theme->getAvailableThemes();
        
        return view('home', compact('current', 'colors', 'themes'));
    }
}
```

### Blade 指令

```blade
{{-- 引用主题资源 --}}
@theme_asset('css/style.css')
@theme_asset('js/app.js')

{{-- 获取主题颜色 --}}
<div style="color: @theme_color('primary')">文本</div>

{{-- 获取主题配置 --}}
<h1>@theme_config('name')</h1>
<p>@theme_config('description', '默认描述')</p>

{{-- 使用主题布局 --}}
@extends(app('theme')->getLayout('app'))

{{-- 使用主题视图 --}}
@include(app('theme')->view('components.header'))
```

---

## 资源编译

### 自动编译

开发环境会自动编译当前主题：

```php
// ThemeServiceProvider.php
if ($this->app->environment('local')) {
    $theme->compileAssets();
}
```

### 手动编译

```bash
# 编译当前主题
php artisan theme:compile

# 编译特定主题
php artisan theme:compile Post::blog

# 编译所有主题
php artisan theme:compile --all
```

### 编译流程

```
源文件:
plugins/Post/resources/themes/blog/assets/css/style.css

↓ 编译

目标文件:
public/plugins/Post/themes/blog/css/style.css

↓ 访问

URL:
http://localhost:8000/plugins/Post/themes/blog/css/style.css
```

---

## 最佳实践

### 1. 主题独立性

✅ 不依赖外部资源  
✅ 自包含所有资源  
✅ 提供完整的配置  

```
plugins/YourPlugin/resources/themes/your-theme/
├── theme.json          # 完整配置
├── views/              # 所有视图
└── assets/             # 所有资源
    ├── css/
    ├── js/
    ├── images/
    └── fonts/
```

### 2. 响应式设计

移动优先，渐进增强：

```css
/* 移动端（默认） */
.container {
    width: 100%;
    padding: 1rem;
}

/* 平板 */
@media (min-width: 768px) {
    .container {
        max-width: 720px;
    }
}

/* 桌面 */
@media (min-width: 1024px) {
    .container {
        max-width: 960px;
    }
}
```

### 3. 性能优化

```bash
# 压缩资源
npm run build

# 启用缓存
php artisan config:cache
php artisan view:cache

# 使用 CDN（生产环境）
```

```blade
@if(app()->environment('production'))
    <link href="https://cdn.example.com/themes/{{ app('theme')->getCurrentTheme() }}/css/style.css" rel="stylesheet">
@else
    <link href="{{ app('theme')->asset('css/style.css') }}" rel="stylesheet">
@endif
```

### 4. 可访问性

```html
<!-- 语义化标签 -->
<nav aria-label="主导航">
    <ul>
        <li><a href="/">首页</a></li>
    </ul>
</nav>

<!-- 替代文本 -->
<img src="image.jpg" alt="描述性文本">

<!-- 键盘导航 -->
<button tabindex="0" aria-label="关闭">×</button>
```

### 5. 暗色模式支持

```css
/* 自动检测系统偏好 */
@media (prefers-color-scheme: dark) {
    body {
        background: #1f2937;
        color: #f3f4f6;
    }
}

/* 手动切换 */
body.dark-mode {
    background: #1f2937;
    color: #f3f4f6;
}
```

---

## 测试

### 功能测试

```php
public function test_plugin_theme_loads()
{
    app('theme')->setCurrentTheme('Post::blog');
    
    $this->assertEquals('Post::blog', app('theme')->getCurrentTheme());
    $this->assertTrue(app('theme')->isPluginTheme('Post::blog'));
}

public function test_plugin_theme_overrides_views()
{
    app('theme')->setCurrentTheme('Post::blog');
    
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertSee('博客主题');
}

public function test_theme_assets_compile()
{
    app('theme')->compileAssets('Post::blog');
    
    $this->assertTrue(
        File::exists(public_path('plugins/Post/themes/blog/css/style.css'))
    );
}
```

---

## 故障排除

### 问题1: 主题不显示

**症状**: 切换主题后页面没有变化

**解决方案**:
```bash
# 清除所有缓存
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# 重新编译主题
php artisan theme:compile
```

### 问题2: 资源404

**症状**: CSS/JS 文件无法加载

**解决方案**:
```bash
# 编译主题资源
php artisan theme:compile Post::blog

# 创建符号链接
php artisan storage:link

# 检查文件权限
chmod -R 755 public/plugins
chmod -R 755 public/themes
```

### 问题3: 视图未覆盖

**症状**: 插件主题视图没有生效

**检查文件路径**:
```
✅ 正确: plugins/Post/resources/themes/blog/views/posts/show.blade.php
❌ 错误: plugins/Post/themes/blog/views/posts/show.blade.php
```

### 问题4: 配置不生效

**症状**: theme.json 配置没有加载

**解决方案**:
```bash
# 检查 JSON 格式
cat plugins/Post/resources/themes/blog/theme.json | jq .

# 清除配置缓存
php artisan config:clear

# 重新加载主题
app('theme')->setCurrentTheme('Post::blog');
```

---

## 示例项目

系统已包含完整示例：**Post 插件 - 博客主题**

```
plugins/Post/resources/themes/blog/
├── theme.json                 # 主题配置
├── views/
│   └── layouts/
│       └── app.blade.php     # 布局文件
└── assets/
    └── css/
        └── style.css         # 样式文件
```

### 激活示例

```bash
# 切换到博客主题
php artisan theme:switch Post::blog

# 编译资源
php artisan theme:compile

# 访问网站
http://localhost:8000
```

---

## 相关文档

- [快速开始指南](01-getting-started.md)
- [钩子系统](03-hook-system.md)
- [插件系统](04-plugin-system.md)
- [FilamentPHP 指南](05-filament-guide.md)

---

**下一篇**: [钩子系统](03-hook-system.md)

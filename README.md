# Laravel CMS - 轻量级建站系统

一款基于 Laravel 构建的现代化建站系统，支持插件扩展、多主题架构、可视化管理。

![Laravel CMS](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg?style=flat-square) ![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg?style=flat-square) ![Filament](https://img.shields.io/badge/Filament-5.x-6574cd.svg?style=flat-square)

## 🌟 核心特性

- 📦 **插件机制** - 支持插件的安装、启用、禁用、卸载
- 🎨 **多主题系统** - 系统主题 + 插件主题，支持热切换
- 🚀 **FilamentPHP 后台** - 现代化的后台管理面板
- 🔗 **钩子系统** - 强大的扩展机制，支持 PHP 8.2 Attribute
- 🔄 **视图生命周期** - 插件可介入视图渲染的各个阶段
- ⚡ **高性能** - 基于 Laravel 12 + Vite 7
- 🎨 **TailwindCSS 4** - 现代化的样式框架

## 🛠 技术栈

| 分类 | 技术/组件 | 版本 |
|------|-----------|------|
| 后端核心 | Laravel | ^12.0 |
| 后台管理 | FilamentPHP | ^5.0 |
| 前端构建 | Vite | ^7.0 |
| 样式框架 | TailwindCSS | ^4.0 |
| 数据库 | MySQL | 8.0+ |

## 📚 文档

完整文档位于 `docs/` 目录：

- **[快速开始指南](docs/01-getting-started.md)** - 安装、配置和快速上手
- **[主题系统](docs/02-theme-system.md)** - 多主题架构和插件主题集成
- **[钩子系统](docs/03-hook-system.md)** - 强大的扩展机制
- **[插件系统](docs/04-plugin-system.md)** - 插件开发和管理
- **[FilamentPHP 指南](docs/05-filament-guide.md)** - 后台管理系统使用指南
- **[视图生命周期](docs/06-view-lifecycle.md)** - 视图生命周期钩子系统
- **[评论系统](docs/07-comment-system.md)** - WordPress 风格评论系统

### 快速参考

- **[评论系统快速参考](COMMENT_SYSTEM_QUICK_REFERENCE.md)** - 评论系统常用代码
- **[枚举使用指南](ENUM_USAGE_GUIDE.md)** - PHP 8.2+ 枚举使用
- **[媒体库指南](MEDIA_LIBRARY_GUIDE.md)** - 媒体库完整指南

查看 [完整文档索引](docs/INDEX.md)

## 🚀 快速开始

### 前置条件

- PHP >= 8.2
- Composer >= 2.0
- MySQL >= 8.0
- Node.js >= 18.x
- NPM >= 9.x

### 安装步骤

```bash
# 1. 克隆项目
git clone <repository-url>
cd laravel-cms

# 2. 安装依赖
composer install
npm install

# 3. 配置环境
cp .env.example .env
php artisan key:generate

# 4. 配置数据库（编辑 .env 文件）
DB_DATABASE=laravel_cms
DB_USERNAME=root
DB_PASSWORD=

# 5. 运行迁移
php artisan migrate
php artisan db:seed --class=CmsSeeder

# 6. 编译资源
npm run build

# 7. 启动服务
php artisan serve
```

### 访问地址

- 前台: http://localhost:8000
- 后台: http://localhost:8000/admin
- 主题列表: http://localhost:8000/theme/list (开发环境)

### 默认账号

```
邮箱: admin@example.com
密码: password
```

## 🎨 主题系统

### 查看可用主题

```bash
php artisan theme:list
```

### 切换主题

```bash
# 切换到系统主题
php artisan theme:switch default

# 切换到插件主题
php artisan theme:switch Post::blog
```

### 编译主题资源

```bash
# 编译当前主题
php artisan theme:compile

# 编译所有主题
php artisan theme:compile --all
```

## 🔗 钩子系统

### 创建钩子

```bash
# 创建基础钩子
php artisan make:hook MyHook

# 使用特定模板
php artisan make:hook DataProcessor --template=async
```

### 注册钩子

```bash
php artisan hook discover
```

### 查看钩子

```bash
php artisan hook list
```

## 📦 插件系统

### 示例插件

系统已包含 Post 插件示例，位于 `plugins/Post/`

### 启用插件

```php
app(PluginsManager::class)->enable('Post');
```

### 插件主题

插件可以提供自己的主题：

```bash
php artisan theme:switch Post::blog
```

## 📁 目录结构

```
laravel-cms/
├── app/
│   ├── Console/Commands/      # Artisan 命令
│   ├── Filament/              # FilamentPHP 资源
│   ├── Hooks/                 # 钩子系统
│   ├── Http/Controllers/      # 控制器
│   ├── Models/                # 数据模型
│   ├── Plugins/               # 插件管理
│   └── Services/              # 服务类
├── config/                    # 配置文件
├── database/                  # 数据库迁移和填充
├── docs/                      # 📚 文档目录
├── plugins/                   # 插件目录
├── resources/
│   ├── themes/                # 系统主题
│   └── views/                 # 视图文件
└── routes/                    # 路由文件
```

## 🔧 常用命令

### 开发命令

```bash
php artisan serve              # 启动开发服务器
npm run dev                    # 前端开发服务器
php artisan cache:clear        # 清除缓存
```

### 主题命令

```bash
php artisan theme:list         # 列出所有主题
php artisan theme:switch       # 切换主题
php artisan theme:compile      # 编译主题资源
```

### 钩子命令

```bash
php artisan hook:list          # 列出所有钩子
php artisan make:hook          # 创建新钩子
php artisan hook discover      # 发现并注册钩子
```

## 🐛 故障排除

### 权限错误

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 755 public/plugins public/themes
```

### 主题不显示

```bash
php artisan cache:clear
php artisan view:clear
php artisan theme:compile
```

### 资源404

```bash
php artisan storage:link
php artisan theme:compile --all
```

## 📊 系统要求

### 最低要求

- PHP >= 8.2
- MySQL >= 8.0 或 PostgreSQL >= 13
- Composer >= 2.0
- Node.js >= 18.x

### 推荐配置

- PHP 8.3+
- MySQL 8.0+
- Redis (缓存)
- Nginx (Web 服务器)

## 🤝 贡献

欢迎贡献代码、报告问题或提出建议！

## 📄 许可证

MIT License

## 🔗 相关链接

- [完整文档](docs/README.md)
- [Laravel 文档](https://laravel.com/docs)
- [FilamentPHP 文档](https://filamentphp.com/docs)

---

**享受使用 Laravel CMS！** 🚀

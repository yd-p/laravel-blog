# 快速开始指南

## 📋 系统要求

### 最低要求
- PHP >= 8.2
- MySQL >= 8.0 或 PostgreSQL >= 13
- Composer >= 2.0
- Node.js >= 18.x
- NPM >= 9.x

### 推荐配置
- PHP 8.3+
- MySQL 8.0+
- Redis (缓存)
- Nginx (Web 服务器)

## 🚀 安装步骤

### 1. 克隆项目

```bash
git clone <repository-url>
cd laravel-cms
```

### 2. 安装依赖

```bash
# 安装 PHP 依赖
composer install

# 安装前端依赖
npm install
```

### 3. 配置环境

```bash
# 复制环境配置文件
cp .env.example .env

# 生成应用密钥
php artisan key:generate

# 编辑 .env 文件，配置数据库连接
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_cms
DB_USERNAME=root
DB_PASSWORD=
```

### 4. 初始化数据库

```bash
# 运行数据库迁移
php artisan migrate

# 填充示例数据（可选）
php artisan db:seed --class=CmsSeeder
```

### 5. 编译前端资源

```bash
# 开发模式（带热重载）
npm run dev

# 生产模式（压缩优化）
npm run build
```

### 6. 启动服务

```bash
# 启动开发服务器
php artisan serve

# 访问网站
# 前台: http://localhost:8000
# 后台: http://localhost:8000/admin
```

## 🎨 主题配置

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
# 编辑 .env 文件
THEME_CURRENT=Post::blog

# 方法3: 浏览器（开发环境）
http://localhost:8000/theme/switch/Post::blog
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

## 👤 创建管理员账户

```bash
# 使用 tinker 创建管理员
php artisan tinker

# 在 tinker 中执行
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
]);
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
│   ├── Providers/             # 服务提供者
│   └── Services/              # 服务类
├── config/                    # 配置文件
├── database/                  # 数据库迁移和填充
├── docs/                      # 文档
├── plugins/                   # 插件目录
├── public/                    # 公共资源
├── resources/
│   ├── themes/                # 系统主题
│   └── views/                 # 视图文件
└── routes/                    # 路由文件
```

## 🔧 常用命令

### 开发命令

```bash
# 启动开发服务器
php artisan serve

# 前端开发服务器
npm run dev

# 清除缓存
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

### 主题命令

```bash
php artisan theme:list          # 列出所有主题
php artisan theme:switch        # 切换主题
php artisan theme:compile       # 编译主题资源
```

### 钩子命令

```bash
php artisan hook:list           # 列出所有钩子
php artisan make:hook           # 创建新钩子
```

### 数据库命令

```bash
php artisan migrate             # 运行迁移
php artisan migrate:fresh       # 重置数据库
php artisan db:seed             # 填充数据
```

## 🐛 故障排除

### 问题1: 权限错误

```bash
# 设置正确的权限
chmod -R 755 storage bootstrap/cache
chmod -R 755 public/plugins public/themes
```

### 问题2: 主题不显示

```bash
# 清除所有缓存
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# 重新编译主题
php artisan theme:compile
```

### 问题3: 资源404

```bash
# 创建符号链接
php artisan storage:link

# 编译主题资源
php artisan theme:compile --all
```

### 问题4: 数据库连接失败

检查 `.env` 文件中的数据库配置：
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 📚 下一步

- 阅读 [主题系统文档](02-theme-system.md) 了解如何自定义主题
- 阅读 [钩子系统文档](03-hook-system.md) 了解如何扩展功能
- 阅读 [FilamentPHP 指南](05-filament-guide.md) 了解后台管理

## 💡 提示

- 开发环境建议使用 `npm run dev` 启用热重载
- 生产环境记得运行 `php artisan config:cache` 优化性能
- 定期备份数据库
- 使用版本控制管理代码

---

**下一篇**: [主题系统](02-theme-system.md)

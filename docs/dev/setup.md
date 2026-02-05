# 开发环境安装脚本

这个目录包含了用于设置开发环境的脚本和工具。

## 安装顺序

### 1. 首先安装系统依赖
```bash
# 给脚本执行权限
chmod +x dev/install-dependencies.sh

# 安装Homebrew和基础工具
./dev/install-dependencies.sh
```

### 2. 然后安装PHP和Composer
```bash
# 安装完整PHP开发环境
./dev/install-php-composer.sh
```

## 系统依赖安装脚本 (`install-dependencies.sh`)

### 功能特性
- **Xcode Command Line Tools**: 自动检测并安装
- **Homebrew**: macOS包管理器
- **基础开发工具**: git, curl, wget, tree, jq, htop, node, yarn
- **Git配置**: 自动配置用户名和邮箱
- **Shell环境**: 设置有用的别名和环境变量
- **系统检测**: 自动检测macOS版本和架构（Intel/Apple Silicon）

### 安装的工具
- Homebrew (包管理器)
- Git (版本控制)
- Node.js & npm (JavaScript运行时)
- Yarn (JavaScript包管理器)
- curl & wget (下载工具)
- tree (目录树显示)
- jq (JSON处理)
- htop (系统监控)

### 添加的别名
```bash
# 系统别名
alias ll='ls -la'
alias la='ls -A'
alias ..='cd ..'

# Laravel别名
alias art='php artisan'
alias serve='php artisan serve'
alias migrate='php artisan migrate'

# Composer别名
alias c='composer'
alias ci='composer install'
alias cu='composer update'
```

## PHP和Composer安装脚本

### 使用方法

```bash
# 给脚本执行权限
chmod +x dev/install-php-composer.sh

# 运行安装脚本
./dev/install-php-composer.sh
```

### 功能特性

- **多平台支持**: 支持 macOS、Ubuntu/Debian、CentOS/RHEL
- **自动检测**: 自动检测操作系统并使用相应的包管理器
- **完整安装**: 安装PHP 8.3和Composer，包含常用扩展
- **开发配置**: 自动配置适合Laravel开发的PHP设置
- **全局工具**: 安装Laravel安装器、PHP-CS-Fixer、PHPStan等常用工具

### 安装内容

#### PHP 8.3 + 扩展
- php8.3-cli
- php8.3-fpm
- php8.3-mysql
- php8.3-xml
- php8.3-curl
- php8.3-zip
- php8.3-mbstring
- php8.3-gd
- php8.3-intl
- php8.3-bcmath
- php8.3-redis
- php8.3-imagick
- php8.3-sqlite3

#### Composer + 全局包
- Composer (最新版本)
- Laravel Installer
- PHP-CS-Fixer (代码格式化)
- PHPStan (静态分析)

#### PHP配置优化
- 内存限制: 512M
- 上传文件大小: 64M
- 执行时间: 300秒
- 时区: Asia/Shanghai
- 开发环境错误报告
- OPcache优化

### 系统要求

#### macOS
- 需要安装 Homebrew
- 支持 macOS 10.15+

#### Ubuntu/Debian
- Ubuntu 18.04+ 或 Debian 9+
- 需要 sudo 权限

#### CentOS/RHEL
- CentOS 8+ 或 RHEL 8+
- 需要 sudo 权限

### 故障排除

如果遇到问题，请检查：

1. **网络连接**: 确保能访问包管理器仓库
2. **权限**: 确保有sudo权限（Linux）或管理员权限（macOS）
3. **磁盘空间**: 确保有足够的磁盘空间
4. **依赖**: 确保系统包管理器正常工作

### 手动验证

安装完成后，可以手动验证：

```bash
# 检查PHP版本
php -v

# 检查PHP扩展
php -m

# 检查Composer
composer --version

# 检查Laravel安装器
laravel --version
```

### 卸载

如需卸载，请使用系统包管理器：

```bash
# macOS
brew uninstall php@8.3

# Ubuntu/Debian
sudo apt remove php8.3*

# CentOS/RHEL
sudo yum remove php*
```
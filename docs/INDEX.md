# 文档索引

## 📚 完整文档列表

### 核心文档

1. **[README - 文档导航](README.md)**
   - 文档结构说明
   - 快速链接
   - 推荐阅读顺序

2. **[01 - 快速开始指南](01-getting-started.md)**
   - 系统要求
   - 安装步骤
   - 基础配置
   - 常用命令
   - 故障排除

3. **[02 - 主题系统](02-theme-system.md)**
   - 系统概述
   - 主题优先级
   - 开发插件主题
   - API 参考
   - 最佳实践

4. **[03 - 钩子系统](03-hook-system.md)**
   - 系统概述
   - 创建钩子
   - 使用钩子
   - 视图钩子
   - 命令行工具

5. **[04 - 插件系统](04-plugin-system.md)**
   - 系统概述
   - 插件结构
   - 创建插件
   - 插件管理
   - 插件钩子

6. **[05 - FilamentPHP 指南](05-filament-guide.md)**
   - 系统概述
   - 访问后台
   - 文章管理
   - 分类管理
   - 自定义资源

7. **[06 - 视图生命周期](06-view-lifecycle.md)**
   - 生命周期概述
   - 钩子注册
   - Blade 指令
   - 实际应用场景
   - API 参考
   - 最佳实践

8. **[07 - 评论系统](07-comment-system.md)**
   - WordPress 风格评论系统
   - 评论状态管理
   - 嵌套回复
   - Filament 管理界面
   - 前端集成
   - API 参考

### 技术文档

8. **[执行顺序](EXECUTION_ORDER.md)**
   - 系统启动流程
   - 服务提供者顺序
   - 插件加载时机

### 快速参考

- **[评论系统快速参考](../COMMENT_SYSTEM_QUICK_REFERENCE.md)** - 评论系统常用代码片段
- **[枚举使用指南](../ENUM_USAGE_GUIDE.md)** - PHP 8.2+ 枚举使用
- **[媒体库指南](../MEDIA_LIBRARY_GUIDE.md)** - 媒体库完整指南
- **[媒体库快速参考](../MEDIA_LIBRARY_QUICK_REFERENCE.md)** - 媒体库常用代码
- **[视图生命周期快速参考](../app/Hooks/VIEW_LIFECYCLE_QUICK_REFERENCE.md)** - 视图钩子快速参考

### 开发文档

9. **[开发环境设置](dev/setup.md)**
   - 开发工具安装
   - 环境配置
   - 开发流程

10. **[Git 故障排除](dev/git-troubleshooting.md)**
   - Git 常见问题
   - 解决方案

## 🎯 按场景查找

### 新手入门

1. [快速开始指南](01-getting-started.md) - 从零开始
2. [主题系统](02-theme-system.md) - 了解主题
3. [FilamentPHP 指南](05-filament-guide.md) - 使用后台

### 主题开发

1. [主题系统 - 开发插件主题](02-theme-system.md#开发插件主题)
2. [主题系统 - API 参考](02-theme-system.md#api-参考)
3. [主题系统 - 最佳实践](02-theme-system.md#最佳实践)

### 插件开发

1. [插件系统 - 创建插件](04-plugin-system.md#创建插件)
2. [插件系统 - 插件钩子](04-plugin-system.md#插件钩子)
3. [插件系统 - 插件主题](04-plugin-system.md#插件主题)
4. [视图生命周期 - 插件集成](06-view-lifecycle.md#快速开始)

### 钩子开发

1. [钩子系统 - 创建钩子](03-hook-system.md#创建钩子)
2. [钩子系统 - 视图钩子](03-hook-system.md#视图钩子)
3. [钩子系统 - 最佳实践](03-hook-system.md#最佳实践)

### 后台管理

1. [FilamentPHP 指南 - 文章管理](05-filament-guide.md#文章管理)
2. [FilamentPHP 指南 - 分类管理](05-filament-guide.md#分类管理)
3. [FilamentPHP 指南 - 自定义资源](05-filament-guide.md#自定义资源)
4. [评论系统 - 管理界面](07-comment-system.md#filament-管理界面)

### 评论系统

1. [评论系统 - 快速开始](07-comment-system.md#概述)
2. [评论系统 - 模型使用](07-comment-system.md#模型使用)
3. [评论系统 - 前端集成](07-comment-system.md#前端集成)
4. [评论系统快速参考](../COMMENT_SYSTEM_QUICK_REFERENCE.md)

## 🔍 按功能查找

### 主题相关

- [查看可用主题](02-theme-system.md#快速开始)
- [切换主题](02-theme-system.md#快速开始)
- [创建主题](02-theme-system.md#开发插件主题)
- [主题配置](02-theme-system.md#开发插件主题)
- [视图覆盖](02-theme-system.md#视图覆盖)
- [资源编译](02-theme-system.md#资源编译)

### 钩子相关

- [创建钩子](03-hook-system.md#创建钩子)
- [注册钩子](03-hook-system.md#使用钩子)
- [执行钩子](03-hook-system.md#使用钩子)
- [视图钩子](03-hook-system.md#视图钩子)
- [钩子模板](03-hook-system.md#创建钩子)
- [PHP 8.2 Attribute](03-hook-system.md#创建钩子)

### 插件相关

- [插件结构](04-plugin-system.md#插件结构)
- [创建插件](04-plugin-system.md#创建插件)
- [启用插件](04-plugin-system.md#插件管理)
- [插件钩子](04-plugin-system.md#插件钩子)
- [插件主题](04-plugin-system.md#插件主题)

### 后台相关

- [访问后台](05-filament-guide.md#访问后台)
- [文章管理](05-filament-guide.md#文章管理)
- [分类管理](05-filament-guide.md#分类管理)
- [创建资源](05-filament-guide.md#自定义资源)
- [表单组件](05-filament-guide.md#自定义资源)
- [表格列](05-filament-guide.md#自定义资源)

## 📖 推荐阅读顺序

### 第一天：基础入门

1. [快速开始指南](01-getting-started.md) - 30分钟
2. [主题系统概述](02-theme-system.md#系统概述) - 15分钟
3. [FilamentPHP 后台](05-filament-guide.md) - 30分钟

### 第二天：主题开发

1. [主题系统完整指南](02-theme-system.md) - 1小时
2. [开发插件主题](02-theme-system.md#开发插件主题) - 1小时
3. 实践：创建自己的主题 - 2小时

### 第三天：钩子系统

1. [钩子系统概述](03-hook-system.md#系统概述) - 30分钟
2. [创建和使用钩子](03-hook-system.md#创建钩子) - 1小时
3. [视图钩子](03-hook-system.md#视图钩子) - 1小时
4. 实践：创建自己的钩子 - 2小时

### 第四天：插件开发

1. [插件系统概述](04-plugin-system.md#系统概述) - 30分钟
2. [创建插件](04-plugin-system.md#创建插件) - 1小时
3. [插件钩子和主题](04-plugin-system.md#插件钩子) - 1小时
4. [视图生命周期集成](06-view-lifecycle.md) - 1小时
5. 实践：创建自己的插件 - 2小时

## 🔗 外部资源

- [Laravel 官方文档](https://laravel.com/docs)
- [FilamentPHP 官方文档](https://filamentphp.com/docs)
- [TailwindCSS 文档](https://tailwindcss.com/docs)
- [Vite 文档](https://vitejs.dev/)

## 💡 获取帮助

如果你在使用过程中遇到问题：

1. 查看相关文档
2. 检查 [故障排除](01-getting-started.md#故障排除) 部分
3. 查看项目 Issues
4. 提交新的 Issue

## 📝 文档贡献

欢迎改进文档！如果你发现文档有误或需要补充，请提交 Pull Request。

---

**最后更新**: 2026-02-05  
**版本**: 1.0.0

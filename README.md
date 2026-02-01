# Laravel CMS - 轻量级建站系统（对标 WordPress）

一款基于 Laravel 构建的现代化建站系统，支持插件扩展、模板定制、可视化管理

![Laravel CMS](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg?style=flat-square) ![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg?style=flat-square) ![Filament](https://img.shields.io/badge/Filament-5.x-6574cd.svg?style=flat-square) ![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-06B6D4.svg?style=flat-square) ![Vite](https://img.shields.io/badge/Vite-7.x-646CFF.svg?style=flat-square) ![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)


## 🌟 核心特性
- 📦 **插件机制**：支持插件的安装、启用、禁用、卸载，低耦合扩展系统功能
- 🎨 **模板系统**：自定义主题模板，支持模板预览、切换、一键部署
- 🚀 **可视化管理**：基于 Filament 5 打造的后台管理面板，操作简单直观
- ⚡ **高性能**：基于 Laravel 12 核心 + Vite 7 前端构建，兼顾性能和开发体验
- 🎨 **现代化样式**：集成 TailwindCSS 4，快速定制响应式界面
- 🧪 **完善测试**：集成 Pest PHP 测试框架，保障代码稳定性
- 🐳 **容器化部署**：支持 Laravel Sail 一键启动开发环境

## 🛠 技术栈
| 分类       | 技术/组件                | 版本/作用                                                                 |
|------------|--------------------------|--------------------------------------------------------------------------|
| 后端核心   | Laravel                  | ^12.0 - 核心框架，提供路由、ORM、中间件等基础能力                        |
| 后端核心   | Filament                 | ^5.0 - 后台管理面板，快速构建可视化管
| 前端构建   | Vite                     | ^7.0.7 - 前端构建工具，替代 Webpack，编译速度更快                        |
| 样式框架   | TailwindCSS              | ^4.0.0 - 原子化 CSS 框架，快速定制界

## 🚀 快速开始

### 前置条件
- PHP >= 8.2
- Composer
- MySQL 8.0+（开发环境）
- Node.js >= 18.x（前端资源编译）
- NPM（前端包管理）
# FilamentPHP 后台管理指南

## 📖 目录

- [系统概述](#系统概述)
- [访问后台](#访问后台)
- [文章管理](#文章管理)
- [分类管理](#分类管理)
- [自定义资源](#自定义资源)

---

## 系统概述

本系统使用 **FilamentPHP 5.0** 构建现代化的后台管理界面。

### 核心特性

✅ **富文本编辑器** - 强大的内容编辑  
✅ **图片管理** - 支持图片编辑和裁剪  
✅ **批量操作** - 高效的数据管理  
✅ **高级筛选** - 灵活的数据筛选  
✅ **关系管理** - 完善的关联数据处理  

---

## 访问后台

### 访问地址

```
URL: http://localhost:8000/admin
```

### 默认账号

```
邮箱: admin@example.com
密码: password
```

### 创建管理员

```bash
# 使用 Filament 命令
php artisan make:filament-user

# 或使用 tinker
php artisan tinker
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
]);
```

---

## 文章管理

### 功能特性

- ✅ 富文本编辑器
- ✅ 文章分类
- ✅ SEO 设置
- ✅ 缩略图上传（支持图片编辑）
- ✅ 发布状态管理（草稿/已发布/回收站）
- ✅ 批量操作
- ✅ 高级筛选

### 文章状态

```php
Post::STATUS_DRAFT = 0;      // 草稿
Post::STATUS_PUBLISHED = 1;  // 已发布
Post::STATUS_TRASH = 2;      // 回收站
```

### 批量操作

- 批量发布
- 批量设为草稿
- 批量删除

### 自定义操作

- 发布文章
- 查看文章
- 编辑文章
- 删除文章

---

## 分类管理

### 功能特性

- ✅ 无限层级分类
- ✅ 父子分类关系
- ✅ SEO 优化
- ✅ 排序功能
- ✅ 启用/禁用状态

### 分类树

系统支持无限层级的分类树结构：

```
技术
├── 前端
│   ├── Vue.js
│   └── React
└── 后端
    ├── Laravel
    └── Node.js
```

---

## 自定义资源

### 创建资源

```bash
php artisan make:filament-resource Product
```

### 资源结构

```php
<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationLabel = '产品管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('产品名称')
                    ->required(),
                    
                Forms\Components\Textarea::make('description')
                    ->label('产品描述'),
                    
                Forms\Components\FileUpload::make('image')
                    ->label('产品图片')
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->searchable(),
                    
                Tables\Columns\ImageColumn::make('image')
                    ->label('图片'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
```

### 表单组件

```php
// 文本输入
Forms\Components\TextInput::make('title')
    ->required()
    ->maxLength(200),

// 富文本编辑器
Forms\Components\RichEditor::make('content')
    ->required(),

// 下拉选择
Forms\Components\Select::make('category_id')
    ->relationship('category', 'name')
    ->searchable()
    ->preload(),

// 文件上传
Forms\Components\FileUpload::make('thumbnail')
    ->image()
    ->imageEditor(),

// 日期时间选择
Forms\Components\DateTimePicker::make('published_at')
    ->native(false),
```

### 表格列

```php
// 文本列
Tables\Columns\TextColumn::make('title')
    ->searchable()
    ->sortable(),

// 徽章列
Tables\Columns\BadgeColumn::make('status')
    ->formatStateUsing(fn ($state) => match($state) {
        0 => '草稿',
        1 => '已发布',
    })
    ->colors([
        'warning' => 0,
        'success' => 1,
    ]),

// 图片列
Tables\Columns\ImageColumn::make('thumbnail'),

// 布尔列
Tables\Columns\IconColumn::make('is_active')
    ->boolean(),
```

### 筛选器

```php
Tables\Filters\SelectFilter::make('status')
    ->options([
        0 => '草稿',
        1 => '已发布',
    ]),

Tables\Filters\Filter::make('created_at')
    ->form([
        Forms\Components\DatePicker::make('created_from'),
        Forms\Components\DatePicker::make('created_until'),
    ])
    ->query(function ($query, array $data) {
        return $query
            ->when($data['created_from'], fn ($q, $date) => 
                $q->whereDate('created_at', '>=', $date))
            ->when($data['created_until'], fn ($q, $date) => 
                $q->whereDate('created_at', '<=', $date));
    }),
```

### 批量操作

```php
Tables\Actions\BulkActionGroup::make([
    Tables\Actions\DeleteBulkAction::make(),
    
    Tables\Actions\BulkAction::make('publish')
        ->label('批量发布')
        ->icon('heroicon-o-paper-airplane')
        ->action(fn ($records) => $records->each->publish())
        ->requiresConfirmation(),
]),
```

---

## 最佳实践

### 1. 使用表单构建器

充分利用 Filament 的表单组件，避免手写 HTML。

### 2. 关系管理

正确配置模型关系：

```php
Forms\Components\Select::make('category_id')
    ->relationship('category', 'name')
    ->searchable()
    ->preload()
    ->createOptionForm([
        Forms\Components\TextInput::make('name')
            ->required(),
    ]),
```

### 3. 自定义操作

添加自定义操作：

```php
Tables\Actions\Action::make('publish')
    ->label('发布')
    ->icon('heroicon-o-paper-airplane')
    ->color('success')
    ->action(fn ($record) => $record->publish())
    ->visible(fn ($record) => $record->status !== 1)
    ->requiresConfirmation(),
```

### 4. 权限控制

实现细粒度的权限管理：

```php
public static function canCreate(): bool
{
    return auth()->user()->can('create_posts');
}

public static function canEdit(Model $record): bool
{
    return auth()->user()->can('edit_posts');
}
```

---

## 故障排除

### 样式问题

```bash
# 重新发布 Filament 资源
php artisan filament:assets

# 清除并重建
php artisan filament:upgrade
```

### 权限问题

```bash
# 设置存储目录权限
chmod -R 775 storage bootstrap/cache
```

### 缓存问题

```bash
# 清除所有缓存
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

---

## 相关文档

- [快速开始指南](01-getting-started.md)
- [主题系统](02-theme-system.md)
- [钩子系统](03-hook-system.md)
- [插件系统](04-plugin-system.md)

---

**返回**: [文档首页](README.md)

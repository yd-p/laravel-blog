# 媒体库使用指南

## 概述

媒体库系统提供了完整的文件管理功能，支持图片、视频、音频和文档等多种文件类型。使用 FilamentPHP 构建的现代化管理界面。

## 功能特性

### ✅ 核心功能

- 📁 **文件管理** - 上传、查看、编辑、删除文件
- 🖼️ **图片处理** - 自动获取图片尺寸、支持图片编辑器
- 📂 **集合管理** - 按集合组织文件（默认、文章、产品、头像等）
- 🔍 **高级筛选** - 按类型、集合、上传者、日期筛选
- 📊 **文件信息** - 显示文件大小、尺寸、MIME类型等
- 🏷️ **自定义属性** - 为文件添加自定义元数据
- 👥 **用户追踪** - 记录文件上传者
- 🗑️ **软删除** - 支持文件恢复
- 📥 **批量操作** - 批量删除、更改集合

### 📋 支持的文件类型

- **图片**: JPG, PNG, GIF, WebP, SVG
- **视频**: MP4, AVI, MOV, WebM
- **音频**: MP3, WAV, OGG
- **文档**: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX

## 数据库结构

### media 表

```sql
- id: 主键
- name: 文件名称
- file_name: 原始文件名
- mime_type: MIME类型
- disk: 存储磁盘
- path: 文件路径
- collection_name: 集合名称
- size: 文件大小(字节)
- custom_properties: 自定义属性(JSON)
- responsive_images: 响应式图片(JSON)
- order_column: 排序
- width: 图片宽度
- height: 图片高度
- model_type: 关联模型类型
- model_id: 关联模型ID
- uploaded_by: 上传者ID
- created_at: 创建时间
- updated_at: 更新时间
- deleted_at: 删除时间
```

## 后台管理

### 访问媒体库

1. 登录后台: `http://localhost:8000/admin`
2. 点击侧边栏 "媒体库"

### 上传文件

1. 点击 "上传文件" 按钮
2. 填写文件信息：
   - 文件名称（必填）
   - 选择文件（必填）
   - 选择集合（可选）
   - 添加自定义属性（可选）
3. 点击 "创建" 保存

### 查看文件

- **列表视图**: 显示文件预览、名称、类型、大小等
- **详情视图**: 显示完整的文件信息和预览
- **标签页**: 按类型快速筛选（全部、图片、视频、音频、文档）

### 筛选功能

- **文件类型**: 图片、视频、音频、文档
- **集合**: 默认、文章、产品、头像、横幅、文档
- **上传者**: 按用户筛选
- **上传日期**: 日期范围筛选
- **回收站**: 查看已删除文件

### 批量操作

1. 选择多个文件
2. 点击批量操作：
   - 删除
   - 永久删除
   - 恢复
   - 更改集合

## 在模型中使用

### 1. 添加 HasMedia Trait

```php
<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasMedia;
    
    // 你的模型代码
}
```

### 2. 上传文件

```php
use Illuminate\Http\UploadedFile;

// 从上传文件添加
$post = Post::find(1);
$file = request()->file('image');
$media = $post->addMedia($file, 'posts', [
    'alt' => '文章配图',
    'featured' => true,
]);

// 从路径添加
$media = $post->addMedia('media/image.jpg', 'posts');
```

### 3. 获取媒体

```php
// 获取所有媒体
$allMedia = $post->media;

// 获取特定集合的媒体
$postImages = $post->getMedia('posts');

// 获取第一个媒体
$firstMedia = $post->getFirstMedia('posts');

// 获取第一个媒体的URL
$imageUrl = $post->getFirstMediaUrl('posts', '/default-image.jpg');
```

### 4. 检查媒体

```php
// 检查是否有媒体
if ($post->hasMedia('posts')) {
    // 有媒体文件
}

// 获取媒体数量
$count = $post->getMediaCount('posts');
```

### 5. 删除媒体

```php
// 清空特定集合
$post->clearMediaCollection('posts');

// 删除特定媒体
$post->deleteMedia($mediaId);
```

### 6. 更新顺序

```php
// 更新媒体顺序
$post->updateMediaOrder([3, 1, 2]); // 媒体ID数组
```

## 在 Filament 资源中使用

### 添加媒体字段

```php
use Filament\Forms;

Forms\Components\FileUpload::make('thumbnail')
    ->label('缩略图')
    ->disk('public')
    ->directory('media/posts')
    ->image()
    ->imageEditor()
    ->maxSize(2048),

Forms\Components\FileUpload::make('gallery')
    ->label('图片库')
    ->disk('public')
    ->directory('media/posts')
    ->image()
    ->multiple()
    ->reorderable()
    ->maxFiles(10),
```

### 显示媒体

```php
use Filament\Tables;

Tables\Columns\ImageColumn::make('thumbnail')
    ->label('缩略图')
    ->disk('public')
    ->size(60),
```

## API 使用

### Media 模型方法

```php
// 获取URL
$url = $media->url;

// 获取临时URL（私有文件）
$tempUrl = $media->getTemporaryUrl(60); // 60分钟

// 获取人类可读的文件大小
$size = $media->human_readable_size; // "1.5 MB"

// 检查文件类型
$isImage = $media->isImage();
$isVideo = $media->isVideo();
$isAudio = $media->isAudio();
$isDocument = $media->isDocument();

// 获取文件类型
$type = $media->type; // 'image', 'video', 'audio', 'document', 'other'

// 获取文件扩展名
$extension = $media->extension; // 'jpg', 'pdf', etc.

// 删除文件
$media->deleteFile();
```

### 查询作用域

```php
// 按类型筛选
$images = Media::ofType('image')->get();
$videos = Media::ofType('video')->get();

// 按集合筛选
$postMedia = Media::inCollection('posts')->get();

// 按上传者筛选
$userMedia = Media::uploadedBy(1)->get();

// 组合查询
$recentImages = Media::ofType('image')
    ->inCollection('posts')
    ->latest()
    ->take(10)
    ->get();
```

## 集合类型

系统预定义了以下集合：

- **default**: 默认集合
- **posts**: 文章相关
- **products**: 产品相关
- **avatars**: 用户头像
- **banners**: 横幅图片
- **documents**: 文档文件

你可以根据需要添加自定义集合。

## 自定义属性

为媒体文件添加额外的元数据：

```php
$media = $post->addMedia($file, 'posts', [
    'alt' => '图片描述',
    'title' => '图片标题',
    'caption' => '图片说明',
    'featured' => true,
    'seo_keywords' => ['关键词1', '关键词2'],
]);

// 访问自定义属性
$alt = $media->custom_properties['alt'] ?? '';
```

## 在视图中使用

### Blade 模板

```blade
{{-- 显示单个图片 --}}
@if($post->hasMedia('posts'))
    <img src="{{ $post->getFirstMediaUrl('posts') }}" 
         alt="{{ $post->title }}">
@endif

{{-- 显示图片库 --}}
@foreach($post->getMedia('gallery') as $media)
    <img src="{{ $media->url }}" 
         alt="{{ $media->custom_properties['alt'] ?? '' }}"
         width="{{ $media->width }}"
         height="{{ $media->height }}">
@endforeach

{{-- 显示文件信息 --}}
<div class="file-info">
    <p>文件名: {{ $media->name }}</p>
    <p>大小: {{ $media->human_readable_size }}</p>
    <p>类型: {{ $media->type }}</p>
    <a href="{{ $media->url }}" download>下载</a>
</div>
```

## 配置

### 存储配置

在 `config/filesystems.php` 中配置存储：

```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

### 创建符号链接

```bash
php artisan storage:link
```

## 最佳实践

### 1. 文件命名

- 使用描述性的文件名称
- 避免使用特殊字符
- 使用小写字母和连字符

### 2. 集合组织

- 为不同用途创建不同集合
- 保持集合命名一致
- 定期清理未使用的文件

### 3. 性能优化

- 压缩图片后再上传
- 使用适当的图片尺寸
- 考虑使用 CDN

### 4. 安全性

- 验证文件类型
- 限制文件大小
- 扫描恶意文件

### 5. 备份

- 定期备份媒体文件
- 使用云存储服务
- 保留文件版本

## 示例代码

### 完整示例：文章带图片

```php
// 创建文章并上传图片
$post = Post::create([
    'title' => '我的文章',
    'content' => '文章内容...',
]);

// 上传缩略图
if ($request->hasFile('thumbnail')) {
    $post->addMedia($request->file('thumbnail'), 'thumbnails', [
        'alt' => $post->title,
        'featured' => true,
    ]);
}

// 上传图片库
if ($request->hasFile('gallery')) {
    foreach ($request->file('gallery') as $image) {
        $post->addMedia($image, 'gallery');
    }
}

// 在视图中显示
return view('posts.show', [
    'post' => $post,
    'thumbnail' => $post->getFirstMediaUrl('thumbnails'),
    'gallery' => $post->getMedia('gallery'),
]);
```

## 故障排除

### 文件上传失败

1. 检查存储目录权限
2. 检查 PHP 上传限制
3. 检查磁盘空间

### 图片不显示

1. 运行 `php artisan storage:link`
2. 检查文件路径
3. 检查文件权限

### 性能问题

1. 优化图片大小
2. 使用缓存
3. 考虑使用队列处理

## 运行迁移

```bash
# 运行迁移
php artisan migrate

# 运行 Seeder（可选）
php artisan db:seed --class=MediaSeeder
```

## 相关文档

- [FilamentPHP 文档](https://filamentphp.com/docs)
- [Laravel 文件存储](https://laravel.com/docs/filesystem)
- [图片处理](https://laravel.com/docs/filesystem#file-uploads)

---

**版本**: 1.0.0  
**最后更新**: 2026-02-05

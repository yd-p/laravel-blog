# 媒体库快速参考

## 快速开始

### 1. 运行迁移

```bash
php artisan migrate
php artisan db:seed --class=MediaSeeder  # 可选：添加示例数据
```

### 2. 访问后台

```
URL: http://localhost:8000/admin
导航: 内容管理 → 媒体库
```

## 在模型中使用

### 添加 Trait

```php
use App\Models\Concerns\HasMedia;

class Post extends Model
{
    use HasMedia;
}
```

### 常用方法

```php
// 上传文件
$post->addMedia($file, 'posts', ['alt' => '描述']);

// 获取媒体
$media = $post->getMedia('posts');
$first = $post->getFirstMedia('posts');
$url = $post->getFirstMediaUrl('posts', '/default.jpg');

// 检查媒体
$hasMedia = $post->hasMedia('posts');
$count = $post->getMediaCount('posts');

// 删除媒体
$post->clearMediaCollection('posts');
$post->deleteMedia($mediaId);
```

## Media 模型方法

```php
// 属性
$media->url                    // 完整URL
$media->human_readable_size    // "1.5 MB"
$media->type                   // 'image', 'video', 'audio', 'document'
$media->extension              // 'jpg', 'pdf'

// 方法
$media->isImage()              // 是否为图片
$media->isVideo()              // 是否为视频
$media->isAudio()              // 是否为音频
$media->isDocument()           // 是否为文档
$media->getTemporaryUrl(60)    // 临时URL（60分钟）
$media->deleteFile()           // 删除文件
```

## 查询作用域

```php
// 按类型
Media::ofType('image')->get();

// 按集合
Media::inCollection('posts')->get();

// 按上传者
Media::uploadedBy(1)->get();

// 组合
Media::ofType('image')
    ->inCollection('posts')
    ->latest()
    ->get();
```

## Blade 模板

```blade
{{-- 显示图片 --}}
@if($post->hasMedia('posts'))
    <img src="{{ $post->getFirstMediaUrl('posts') }}" alt="{{ $post->title }}">
@endif

{{-- 图片库 --}}
@foreach($post->getMedia('gallery') as $media)
    <img src="{{ $media->url }}" alt="{{ $media->name }}">
@endforeach
```

## Filament 表单

```php
FileUpload::make('thumbnail')
    ->disk('public')
    ->directory('media/posts')
    ->image()
    ->imageEditor(),

FileUpload::make('gallery')
    ->multiple()
    ->reorderable()
    ->maxFiles(10),
```

## 集合类型

- `default` - 默认
- `posts` - 文章
- `products` - 产品
- `avatars` - 头像
- `banners` - 横幅
- `documents` - 文档

## 文件类型

- **图片**: image/*
- **视频**: video/*
- **音频**: audio/*
- **文档**: PDF, DOC, XLS, PPT

## 完整文档

查看 [MEDIA_LIBRARY_GUIDE.md](MEDIA_LIBRARY_GUIDE.md)

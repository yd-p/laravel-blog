# 枚举使用指南

## 概述

本项目已将所有常量抽取为 PHP 8.2+ 的枚举类（Enum），提供更好的类型安全和代码可维护性。

## 已创建的枚举

### 1. PostStatus - 文章状态

**位置**: `app/Enums/PostStatus.php`

**值**:
- `DRAFT` = 1 (草稿)
- `PUBLISHED` = 2 (已发布)
- `TRASH` = 3 (回收站)

**使用示例**:

```php
use App\Enums\PostStatus;

// 创建文章
$post = Post::create([
    'title' => '标题',
    'status' => PostStatus::DRAFT->value,
]);

// 检查状态
if ($post->status === PostStatus::PUBLISHED) {
    // 已发布
}

// 使用方法
$post->status->label();  // "草稿"
$post->status->color();  // "gray"
$post->status->icon();   // "heroicon-o-pencil"

// 检查方法
$post->status->isDraft();      // true/false
$post->status->isPublished();  // true/false
$post->status->isTrash();      // true/false

// 在 Filament 中使用
Forms\Components\Select::make('status')
    ->options(PostStatus::toSelectArray())
    ->default(PostStatus::DRAFT->value);
```

### 2. TagStatus - 标签状态

**位置**: `app/Enums/TagStatus.php`

**值**:
- `DISABLED` = 0 (禁用)
- `ENABLED` = 1 (启用)

**使用示例**:

```php
use App\Enums\TagStatus;

// 创建标签
$tag = Tag::create([
    'name' => '标签名',
    'status' => TagStatus::ENABLED->value,
]);

// 检查状态
if ($tag->status === TagStatus::ENABLED) {
    // 已启用
}

// 使用方法
$tag->status->label();     // "启用"
$tag->status->color();     // "success"
$tag->status->isEnabled(); // true/false
$tag->status->toBool();    // true/false

// 从布尔值创建
$status = TagStatus::fromBool(true);  // TagStatus::ENABLED

// 在 Filament 中使用
Forms\Components\Select::make('status')
    ->options(TagStatus::toSelectArray())
    ->default(TagStatus::ENABLED->value);
```

### 3. MediaType - 媒体类型

**位置**: `app/Enums/MediaType.php`

**值**:
- `IMAGE` = 'image' (图片)
- `VIDEO` = 'video' (视频)
- `AUDIO` = 'audio' (音频)
- `DOCUMENT` = 'document' (文档)
- `OTHER` = 'other' (其他)

**使用示例**:

```php
use App\Enums\MediaType;

// 从 MIME 类型判断
$type = MediaType::fromMimeType('image/jpeg');  // MediaType::IMAGE

// 使用方法
$type->label();           // "图片"
$type->color();           // "success"
$type->icon();            // "heroicon-o-photo"
$type->getMimePattern();  // "image/*"
$type->getExtensions();   // ['jpg', 'jpeg', 'png', ...]

// 在 Filament 中使用
Tables\Columns\TextColumn::make('type')
    ->badge()
    ->formatStateUsing(fn (string $state) => MediaType::from($state)->label())
    ->color(fn (string $state) => MediaType::from($state)->color());
```

### 4. MediaCollection - 媒体集合

**位置**: `app/Enums/MediaCollection.php`

**值**:
- `DEFAULT` = 'default' (默认)
- `POSTS` = 'posts' (文章)
- `PRODUCTS` = 'products' (产品)
- `AVATARS` = 'avatars' (头像)
- `BANNERS` = 'banners' (横幅)
- `DOCUMENTS` = 'documents' (文档)

**使用示例**:

```php
use App\Enums\MediaCollection;

// 使用方法
$collection = MediaCollection::POSTS;

$collection->label();           // "文章"
$collection->description();     // "文章相关的图片和附件"
$collection->color();           // "primary"
$collection->icon();            // "heroicon-o-document-text"
$collection->getStoragePath();  // "media/posts"
$collection->getMaxSize();      // 10240 (KB)
$collection->getAllowedTypes(); // ['image/*', 'video/*', ...]

// 在 Filament 中使用
Forms\Components\Select::make('collection_name')
    ->options(MediaCollection::toSelectArray())
    ->default(MediaCollection::DEFAULT->value);
```

## 模型中使用枚举

### 类型转换

在模型中使用 `$casts` 属性自动转换：

```php
class Post extends Model
{
    protected $casts = [
        'status' => PostStatus::class,
        'published_at' => 'datetime',
    ];
}

// 使用
$post = Post::find(1);
$post->status;  // PostStatus 实例，不是整数
$post->status->label();  // "已发布"
```

### 查询作用域

```php
// Post 模型
public function scopePublished($query)
{
    return $query->where('status', PostStatus::PUBLISHED);
}

// 使用
$posts = Post::published()->get();
```

### 修改器

```php
// 发布文章
public function publish(): void
{
    $this->update([
        'status' => PostStatus::PUBLISHED,
        'published_at' => now(),
    ]);
}
```

## Filament 资源中使用

### 表单字段

```php
Forms\Components\Select::make('status')
    ->label('状态')
    ->options(PostStatus::toSelectArray())
    ->required()
    ->default(PostStatus::DRAFT->value)
    ->native(false);
```

### 表格列

```php
Tables\Columns\TextColumn::make('status')
    ->label('状态')
    ->badge()
    ->formatStateUsing(fn (PostStatus $state) => $state->label())
    ->color(fn (PostStatus $state) => $state->color())
    ->icon(fn (PostStatus $state) => $state->icon());
```

### 筛选器

```php
Tables\Filters\SelectFilter::make('status')
    ->label('状态')
    ->options(PostStatus::toSelectArray());
```

### 批量操作

```php
Tables\Actions\BulkAction::make('publish')
    ->label('发布')
    ->action(function ($records) {
        $records->each->update(['status' => PostStatus::PUBLISHED]);
    });
```

## 枚举方法

所有枚举都提供以下通用方法：

### label()
返回枚举的中文标签

```php
PostStatus::DRAFT->label();  // "草稿"
```

### color()
返回 Filament 颜色（用于徽章）

```php
PostStatus::PUBLISHED->color();  // "success"
```

### icon()
返回 Heroicon 图标名称

```php
PostStatus::DRAFT->icon();  // "heroicon-o-pencil"
```

### options()
返回所有选项的数组（值 => 标签）

```php
PostStatus::options();
// [1 => '草稿', 2 => '已发布', 3 => '回收站']
```

### toSelectArray()
返回 Filament Select 组件格式的数组

```php
PostStatus::toSelectArray();
// [1 => '草稿', 2 => '已发布', 3 => '回收站']
```

### fromValue()
从值获取枚举实例

```php
$status = PostStatus::fromValue(1);  // PostStatus::DRAFT
```

## 迁移指南

### 从常量迁移到枚举

**旧代码**:
```php
// 模型
const STATUS_DRAFT = 1;
const STATUS_PUBLISHED = 2;

protected $casts = [
    'status' => 'integer',
];

// 使用
if ($post->status === Post::STATUS_PUBLISHED) {
    // ...
}
```

**新代码**:
```php
// 模型
use App\Enums\PostStatus;

protected $casts = [
    'status' => PostStatus::class,
];

// 使用
if ($post->status === PostStatus::PUBLISHED) {
    // ...
}
```

### Filament 资源迁移

**旧代码**:
```php
Forms\Components\Select::make('status')
    ->options([
        Post::STATUS_DRAFT => '草稿',
        Post::STATUS_PUBLISHED => '已发布',
    ]);
```

**新代码**:
```php
Forms\Components\Select::make('status')
    ->options(PostStatus::toSelectArray());
```

### Seeder 迁移

**旧代码**:
```php
Post::create([
    'status' => Post::STATUS_PUBLISHED,
]);
```

**新代码**:
```php
Post::create([
    'status' => PostStatus::PUBLISHED->value,
]);
```

## 优势

### 1. 类型安全

```php
// 编译时检查
$post->status = PostStatus::PUBLISHED;  // ✅ 正确
$post->status = 999;  // ❌ 类型错误
```

### 2. IDE 支持

- 自动完成
- 类型提示
- 重构支持

### 3. 集中管理

所有状态相关的逻辑都在枚举类中：
- 标签
- 颜色
- 图标
- 验证方法

### 4. 易于维护

添加新状态只需修改枚举类，所有使用的地方自动更新。

### 5. 避免魔术数字

```php
// 不好
if ($post->status === 2) { }

// 好
if ($post->status === PostStatus::PUBLISHED) { }
```

## 最佳实践

### 1. 始终使用枚举

```php
// ✅ 好
$post->status = PostStatus::DRAFT;

// ❌ 避免
$post->status = 1;
```

### 2. 在数据库中存储值

```php
// 创建时使用 ->value
Post::create([
    'status' => PostStatus::DRAFT->value,
]);
```

### 3. 比较时使用枚举

```php
// ✅ 好
if ($post->status === PostStatus::PUBLISHED) { }

// ❌ 避免
if ($post->status->value === 2) { }
```

### 4. 在 Filament 中使用辅助方法

```php
// ✅ 好
->options(PostStatus::toSelectArray())

// ❌ 避免
->options([
    PostStatus::DRAFT->value => PostStatus::DRAFT->label(),
    // ...
])
```

## 测试

### 单元测试

```php
use App\Enums\PostStatus;

test('post status enum', function () {
    expect(PostStatus::DRAFT->value)->toBe(1);
    expect(PostStatus::DRAFT->label())->toBe('草稿');
    expect(PostStatus::DRAFT->isDraft())->toBeTrue();
});
```

### 功能测试

```php
test('create post with enum status', function () {
    $post = Post::create([
        'title' => 'Test',
        'status' => PostStatus::DRAFT->value,
    ]);
    
    expect($post->status)->toBeInstanceOf(PostStatus::class);
    expect($post->status)->toBe(PostStatus::DRAFT);
});
```

## 常见问题

### Q: 如何在数据库查询中使用枚举？

```php
// 直接使用枚举
Post::where('status', PostStatus::PUBLISHED)->get();

// 或使用值
Post::where('status', PostStatus::PUBLISHED->value)->get();
```

### Q: 如何在 API 响应中序列化枚举？

```php
// 在模型中添加访问器
public function getStatusLabelAttribute(): string
{
    return $this->status->label();
}

// 在资源中使用
return [
    'status' => $this->status->value,
    'status_label' => $this->status_label,
];
```

### Q: 如何验证枚举值？

```php
use Illuminate\Validation\Rules\Enum;

$request->validate([
    'status' => ['required', new Enum(PostStatus::class)],
]);
```

## 总结

枚举提供了一种类型安全、易于维护的方式来管理常量值。通过使用枚举，代码更加清晰、可读性更强，并且减少了错误的可能性。

---

**创建日期**: 2026-02-05  
**版本**: 1.0.0

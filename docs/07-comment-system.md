# 评论系统文档

## 概述

本系统实现了一个完整的 WordPress 风格评论系统，支持评论嵌套回复、状态管理、审核流程等功能。

## 核心特性

### 1. 评论状态管理
- **待审核 (PENDING)**: 新提交的评论默认状态
- **已批准 (APPROVED)**: 通过审核的评论，前台可见
- **垃圾评论 (SPAM)**: 被标记为垃圾的评论
- **回收站 (TRASH)**: 已删除但可恢复的评论

### 2. 评论嵌套回复
- 支持无限层级的评论回复
- 自动维护父子关系
- 自动统计回复数量

### 3. 评论者信息
- 支持注册用户评论（关联 user_id）
- 支持游客评论（记录姓名、邮箱、网址）
- 自动记录 IP 地址和 User Agent
- 集成 Gravatar 头像

### 4. 审核功能
- 记录审核人和审核时间
- 批量审核操作
- 评论评分（karma）系统

## 数据库结构

### comments 表字段

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    
    // 评论者信息
    $table->string('author_name', 100);
    $table->string('author_email', 100);
    $table->string('author_url', 200)->nullable();
    $table->string('author_ip', 45)->nullable();
    $table->text('author_user_agent')->nullable();
    
    // 评论内容
    $table->text('content');
    
    // 状态管理
    $table->integer('status')->default(0); // CommentStatus enum
    $table->string('type', 20)->default('comment');
    
    // 审核信息
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    
    // 统计信息
    $table->integer('karma')->default(0);
    $table->integer('reply_count')->default(0);
    
    $table->timestamps();
    $table->softDeletes();
    
    // 索引
    $table->index(['post_id', 'status']);
    $table->index('parent_id');
    $table->index('author_email');
});
```

## 模型使用

### Comment 模型

```php
use App\Models\Comment;
use App\Enums\CommentStatus;

// 创建评论
$comment = Comment::create([
    'post_id' => 1,
    'author_name' => '张三',
    'author_email' => 'zhangsan@example.com',
    'content' => '这是一条评论',
    'status' => CommentStatus::PENDING,
]);

// 批准评论
$comment->approve();

// 标记为垃圾
$comment->markAsSpam();

// 移至回收站
$comment->moveToTrash();

// 添加回复
$reply = Comment::create([
    'post_id' => 1,
    'parent_id' => $comment->id,
    'author_name' => '李四',
    'author_email' => 'lisi@example.com',
    'content' => '这是一条回复',
]);

// 获取评论的所有回复
$replies = $comment->replies;

// 获取评论的所有回复（递归）
$allReplies = $comment->allReplies;

// 检查是否为顶级评论
if ($comment->isTopLevel()) {
    // ...
}

// 检查是否为回复
if ($comment->isReply()) {
    // ...
}
```

### 使用 HasComments Trait

```php
use App\Models\Post;

$post = Post::find(1);

// 获取所有评论
$comments = $post->comments;

// 获取已批准的评论
$approvedComments = $post->approvedComments;

// 获取待审核的评论
$pendingComments = $post->pendingComments;

// 获取顶级评论（不包括回复）
$topLevelComments = $post->topLevelComments;

// 获取评论数量
$count = $post->comment_count;

// 获取待审核评论数量
$pendingCount = $post->pending_comment_count;

// 检查是否有评论
if ($post->hasComments()) {
    // ...
}

// 检查是否允许评论
if ($post->allowsComments()) {
    // ...
}

// 添加评论
$comment = $post->addComment([
    'author_name' => '王五',
    'author_email' => 'wangwu@example.com',
    'content' => '这是一条新评论',
]);

// 获取最新评论
$latestComments = $post->getLatestComments(5);

// 获取评论统计
$stats = $post->getCommentStats();
// 返回: ['total' => 10, 'approved' => 8, 'pending' => 2, 'spam' => 0]
```

## Filament 管理界面

### 功能特性

1. **列表页面**
   - 按状态分组的标签页（全部、待审核、已批准、垃圾、回收站）
   - 显示评论者头像（Gravatar）
   - 显示评论内容预览
   - 显示所属文章
   - 显示回复数量
   - 状态徽章显示

2. **筛选功能**
   - 按状态筛选
   - 按文章筛选
   - 按评论类型筛选（顶级评论/回复）
   - 按日期范围筛选
   - 软删除筛选

3. **操作功能**
   - 批准评论
   - 标记为垃圾
   - 回复评论
   - 查看详情
   - 编辑评论
   - 删除评论
   - 批量操作

4. **导航徽章**
   - 显示待审核评论数量
   - 黄色警告颜色

### 创建评论

```php
// 在 Filament 中创建评论时，会自动填充：
// - author_ip: 当前请求的 IP 地址
// - author_user_agent: 当前请求的 User Agent
// - status: 默认为 PENDING（待审核）
```

## 前端集成

### 显示评论列表

```blade
{{-- 在文章详情页显示评论 --}}
@if($post->hasComments())
    <div class="comments">
        <h3>评论 ({{ $post->comment_count }})</h3>
        
        @foreach($post->topLevelComments as $comment)
            <div class="comment" id="comment-{{ $comment->id }}">
                <img src="{{ $comment->author_avatar }}" alt="{{ $comment->author_display_name }}">
                <div class="comment-content">
                    <strong>{{ $comment->author_display_name }}</strong>
                    <time>{{ $comment->created_at->diffForHumans() }}</time>
                    <p>{{ $comment->content }}</p>
                    
                    {{-- 显示回复 --}}
                    @if($comment->replies->count() > 0)
                        <div class="replies">
                            @foreach($comment->replies as $reply)
                                <div class="reply">
                                    <img src="{{ $reply->author_avatar }}" alt="{{ $reply->author_display_name }}">
                                    <strong>{{ $reply->author_display_name }}</strong>
                                    <p>{{ $reply->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
```

### 评论表单

```blade
<form action="{{ route('comments.store') }}" method="POST">
    @csrf
    <input type="hidden" name="post_id" value="{{ $post->id }}">
    <input type="hidden" name="parent_id" value="{{ $parentId ?? null }}">
    
    @guest
        <input type="text" name="author_name" placeholder="姓名" required>
        <input type="email" name="author_email" placeholder="邮箱" required>
        <input type="url" name="author_url" placeholder="网址（可选）">
    @endguest
    
    <textarea name="content" placeholder="发表评论..." required></textarea>
    <button type="submit">提交评论</button>
</form>
```

## API 端点（待实现）

### 创建评论

```http
POST /api/posts/{post}/comments
Content-Type: application/json

{
    "author_name": "张三",
    "author_email": "zhangsan@example.com",
    "author_url": "https://example.com",
    "content": "这是一条评论",
    "parent_id": null
}
```

### 获取评论列表

```http
GET /api/posts/{post}/comments?status=approved&page=1
```

### 回复评论

```http
POST /api/comments/{comment}/replies
Content-Type: application/json

{
    "author_name": "李四",
    "author_email": "lisi@example.com",
    "content": "这是一条回复"
}
```

## 查询作用域

```php
// 获取已批准的评论
Comment::approved()->get();

// 获取待审核的评论
Comment::pending()->get();

// 获取垃圾评论
Comment::spam()->get();

// 获取顶级评论
Comment::topLevel()->get();

// 获取回复
Comment::replies()->get();

// 获取指定文章的评论
Comment::forPost(1)->get();

// 获取最新评论
Comment::latest()->get();

// 组合使用
Comment::forPost(1)
    ->approved()
    ->topLevel()
    ->latest()
    ->get();
```

## 事件处理

Comment 模型会自动处理以下事件：

1. **创建评论时**
   - 自动更新父评论的回复数量

2. **删除评论时**
   - 级联删除所有子评论
   - 更新父评论的回复数量

3. **更新评论状态时**
   - 更新父评论的回复数量（仅统计已批准的回复）

## 最佳实践

### 1. 评论审核

```php
// 批准评论时记录审核人
$comment->approve(auth()->id());

// 批量批准评论
Comment::pending()->each(function ($comment) {
    $comment->approve();
});
```

### 2. 防止垃圾评论

```php
// 可以集成 Akismet 或其他反垃圾服务
// 在创建评论时检查
if ($spamDetector->isSpam($comment)) {
    $comment->markAsSpam();
}
```

### 3. 评论通知

```php
// 在评论被批准时发送通知
$comment->approve();

// 通知文章作者
$comment->post->author->notify(new NewCommentNotification($comment));

// 如果是回复，通知父评论作者
if ($comment->parent) {
    $comment->parent->user?->notify(new CommentReplyNotification($comment));
}
```

### 4. 评论缓存

```php
// 缓存文章的评论数量
$commentCount = Cache::remember(
    "post.{$post->id}.comments.count",
    3600,
    fn () => $post->approvedComments()->count()
);
```

## 安全考虑

1. **XSS 防护**: 在显示评论内容时使用 `{{ }}` 而不是 `{!! !!}`
2. **SQL 注入**: 使用 Eloquent ORM 自动防护
3. **CSRF 保护**: 表单中包含 `@csrf` 令牌
4. **速率限制**: 对评论提交接口应用速率限制
5. **内容过滤**: 过滤敏感词和恶意内容

## 性能优化

1. **预加载关系**
```php
$comments = Comment::with(['post', 'user', 'replies'])->get();
```

2. **分页加载**
```php
$comments = Comment::approved()->paginate(20);
```

3. **索引优化**
- post_id + status 复合索引
- parent_id 索引
- author_email 索引

4. **缓存策略**
- 缓存评论数量
- 缓存热门评论
- 使用 Redis 存储实时评论

## 扩展功能

### 1. 评论点赞

```php
// 在 comments 表添加 likes_count 字段
$comment->increment('likes_count');
```

### 2. 评论举报

```php
// 创建 comment_reports 表
CommentReport::create([
    'comment_id' => $comment->id,
    'user_id' => auth()->id(),
    'reason' => '不当内容',
]);
```

### 3. 评论编辑历史

```php
// 创建 comment_revisions 表
$comment->revisions()->create([
    'content' => $comment->content,
    'edited_by' => auth()->id(),
]);
```

### 4. 评论订阅

```php
// 用户订阅评论更新
$post->commentSubscribers()->attach(auth()->id());
```

## 故障排查

### 评论不显示
1. 检查评论状态是否为 APPROVED
2. 检查文章是否允许评论
3. 检查软删除状态

### 回复数量不正确
```php
// 手动重新计算回复数量
$comment->updateReplyCount();
```

### 性能问题
1. 添加数据库索引
2. 使用预加载避免 N+1 查询
3. 实施缓存策略
4. 考虑使用队列处理通知

## 相关文件

- 主资源: `app/Filament/Resources/Comments/CommentResource.php`
- 表单配置: `app/Filament/Resources/Comments/Schemas/CommentForm.php`
- 表格配置: `app/Filament/Resources/Comments/Tables/CommentsTable.php`
- 页面: `app/Filament/Resources/Comments/Pages/*.php`
- 模型: `app/Models/Comment.php`
- Trait: `app/Models/Concerns/HasComments.php`
- 枚举: `app/Enums/CommentStatus.php`
- 迁移: `database/migrations/2024_01_01_000005_create_comments_table.php`
- 填充: `database/seeders/CommentSeeder.php`

## 版本兼容性

- Laravel: 12.x
- FilamentPHP: 5.x
- PHP: 8.2+

## 更新日志

- 2024-01-01: 初始版本，实现基础评论功能
- 支持嵌套回复
- 支持状态管理
- 集成 Filament 管理界面

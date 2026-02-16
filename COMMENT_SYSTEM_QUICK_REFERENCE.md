# 评论系统快速参考

## 快速开始

### 1. 运行迁移
```bash
php artisan migrate
```

### 2. 填充测试数据
```bash
php artisan db:seed --class=CommentSeeder
```

### 3. 访问管理界面
访问 Filament 后台 → 内容管理 → 评论管理

## 常用代码片段

### 获取文章评论

```php
use App\Models\Post;

$post = Post::find(1);

// 所有评论
$comments = $post->comments;

// 已批准的评论
$approved = $post->approvedComments;

// 顶级评论（带回复）
$topLevel = $post->topLevelComments;

// 评论数量
$count = $post->comment_count;
```

### 创建评论

```php
use App\Models\Comment;
use App\Enums\CommentStatus;

// 方式 1: 直接创建
$comment = Comment::create([
    'post_id' => 1,
    'author_name' => '张三',
    'author_email' => 'zhangsan@example.com',
    'content' => '这是一条评论',
    'status' => CommentStatus::PENDING,
]);

// 方式 2: 通过文章创建
$comment = $post->addComment([
    'author_name' => '李四',
    'author_email' => 'lisi@example.com',
    'content' => '这是另一条评论',
]);
```

### 创建回复

```php
$reply = Comment::create([
    'post_id' => 1,
    'parent_id' => $comment->id,  // 父评论 ID
    'author_name' => '王五',
    'author_email' => 'wangwu@example.com',
    'content' => '这是一条回复',
]);
```

### 评论操作

```php
// 批准评论
$comment->approve();

// 标记为垃圾
$comment->markAsSpam();

// 移至回收站
$comment->moveToTrash();

// 恢复评论
$comment->restore();
```

### 查询评论

```php
use App\Models\Comment;

// 已批准的评论
$approved = Comment::approved()->get();

// 待审核的评论
$pending = Comment::pending()->get();

// 垃圾评论
$spam = Comment::spam()->get();

// 顶级评论
$topLevel = Comment::topLevel()->get();

// 指定文章的评论
$postComments = Comment::forPost(1)->approved()->get();

// 最新评论
$latest = Comment::latest()->take(10)->get();
```

### 评论关系

```php
// 获取评论的文章
$post = $comment->post;

// 获取评论者（如果是注册用户）
$user = $comment->user;

// 获取父评论
$parent = $comment->parent;

// 获取所有回复
$replies = $comment->replies;

// 获取所有回复（递归）
$allReplies = $comment->allReplies;

// 获取审核人
$approver = $comment->approvedBy;
```

### 评论检查

```php
// 是否为顶级评论
if ($comment->isTopLevel()) {
    // ...
}

// 是否为回复
if ($comment->isReply()) {
    // ...
}

// 文章是否有评论
if ($post->hasComments()) {
    // ...
}

// 文章是否允许评论
if ($post->allowsComments()) {
    // ...
}
```

### 评论统计

```php
// 获取文章评论统计
$stats = $post->getCommentStats();
// 返回: [
//     'total' => 10,
//     'approved' => 8,
//     'pending' => 2,
//     'spam' => 0
// ]

// 获取最新评论
$latest = $post->getLatestComments(5);

// 待审核评论数量
$pendingCount = $post->pending_comment_count;
```

## 评论状态

| 状态 | 值 | 说明 | 颜色 | 图标 |
|------|-----|------|------|------|
| PENDING | 0 | 待审核 | warning | clock |
| APPROVED | 1 | 已批准 | success | check-circle |
| SPAM | 2 | 垃圾评论 | danger | exclamation-triangle |
| TRASH | 3 | 回收站 | gray | trash |

## 前端显示示例

### Blade 模板

```blade
{{-- 显示评论列表 --}}
<div class="comments">
    <h3>评论 ({{ $post->comment_count }})</h3>
    
    @foreach($post->topLevelComments as $comment)
        <div class="comment">
            <img src="{{ $comment->author_avatar }}" alt="{{ $comment->author_display_name }}">
            <div class="comment-body">
                <strong>{{ $comment->author_display_name }}</strong>
                <time>{{ $comment->created_at->diffForHumans() }}</time>
                <p>{{ $comment->content }}</p>
                
                {{-- 回复 --}}
                @if($comment->replies->count() > 0)
                    <div class="replies">
                        @foreach($comment->replies as $reply)
                            <div class="reply">
                                <img src="{{ $reply->author_avatar }}">
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

{{-- 评论表单 --}}
<form action="{{ route('comments.store') }}" method="POST">
    @csrf
    <input type="hidden" name="post_id" value="{{ $post->id }}">
    
    @guest
        <input type="text" name="author_name" placeholder="姓名" required>
        <input type="email" name="author_email" placeholder="邮箱" required>
        <input type="url" name="author_url" placeholder="网址（可选）">
    @endguest
    
    <textarea name="content" placeholder="发表评论..." required></textarea>
    <button type="submit">提交评论</button>
</form>
```

## 控制器示例（待实现）

```php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'author_name' => 'required|max:100',
            'author_email' => 'required|email|max:100',
            'author_url' => 'nullable|url|max:200',
            'content' => 'required',
            'parent_id' => 'nullable|exists:comments,id',
        ]);
        
        $comment = $post->addComment($validated);
        
        return redirect()->back()->with('success', '评论已提交，等待审核');
    }
}
```

## Filament 管理功能

### 列表页标签
- 全部评论
- 待审核 (带徽章数量)
- 已批准
- 垃圾评论
- 回收站

### 筛选器
- 按状态筛选
- 按文章筛选
- 按类型筛选（顶级/回复）
- 按日期范围筛选

### 操作
- 批准评论
- 标记为垃圾
- 回复评论
- 查看详情
- 编辑评论
- 删除评论

### 批量操作
- 批量批准
- 批量标记为垃圾
- 批量删除

## 数据库索引

```php
// 已创建的索引
$table->index(['post_id', 'status']);  // 查询文章的已批准评论
$table->index('parent_id');            // 查询回复
$table->index('author_email');         // 查询用户的所有评论
```

## 性能优化建议

### 1. 预加载关系
```php
$comments = Comment::with(['post', 'user', 'replies'])->get();
```

### 2. 分页
```php
$comments = Comment::approved()->paginate(20);
```

### 3. 缓存
```php
$count = Cache::remember(
    "post.{$post->id}.comments.count",
    3600,
    fn () => $post->approvedComments()->count()
);
```

## 安全提示

1. ✅ 使用 `{{ }}` 显示评论内容（防 XSS）
2. ✅ 表单包含 `@csrf` 令牌
3. ✅ 验证所有用户输入
4. ✅ 对评论提交接口应用速率限制
5. ✅ 过滤敏感词和恶意内容

## 常见问题

### Q: 评论不显示？
A: 检查评论状态是否为 APPROVED

### Q: 回复数量不正确？
A: 运行 `$comment->updateReplyCount()`

### Q: 如何禁用某篇文章的评论？
A: 在 Post 模型的 `allowsComments()` 方法中添加逻辑

### Q: 如何自动批准注册用户的评论？
A: 在创建评论时检查用户状态：
```php
$status = auth()->check() 
    ? CommentStatus::APPROVED 
    : CommentStatus::PENDING;
```

## 相关文档

- 完整文档: `docs/07-comment-system.md`
- 枚举使用: `ENUM_USAGE_GUIDE.md`
- 模型文件: `app/Models/Comment.php`
- Trait 文件: `app/Models/Concerns/HasComments.php`

## 下一步

1. 实现前端评论控制器
2. 创建评论显示视图
3. 添加评论通知功能
4. 集成反垃圾服务
5. 添加评论点赞功能
6. 实现评论举报功能

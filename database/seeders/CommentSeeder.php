<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $posts = Post::limit(3)->get();

        if ($posts->isEmpty()) {
            $this->command->warn('没有找到文章，请先运行 CmsSeeder');
            return;
        }

        $comments = [
            // 第一篇文章的评论
            [
                'post_id' => $posts[0]->id,
                'user_id' => $user?->id,
                'author_name' => $user?->name ?? '张三',
                'author_email' => $user?->email ?? 'zhangsan@example.com',
                'author_url' => 'https://example.com',
                'author_ip' => '127.0.0.1',
                'author_user_agent' => 'Mozilla/5.0',
                'content' => '这篇文章写得非常好，对我帮助很大！特别是关于Laravel的部分，讲解得很详细。',
                'status' => CommentStatus::APPROVED->value,
                'approved_by' => $user?->id,
                'approved_at' => now(),
                'karma' => 5,
            ],
            [
                'post_id' => $posts[0]->id,
                'author_name' => '李四',
                'author_email' => 'lisi@example.com',
                'author_url' => null,
                'author_ip' => '192.168.1.1',
                'author_user_agent' => 'Mozilla/5.0',
                'content' => '感谢分享！请问有没有完整的示例代码可以参考？',
                'status' => CommentStatus::APPROVED->value,
                'approved_by' => $user?->id,
                'approved_at' => now(),
                'karma' => 3,
            ],
            [
                'post_id' => $posts[0]->id,
                'author_name' => '王五',
                'author_email' => 'wangwu@example.com',
                'author_url' => 'https://wangwu.com',
                'author_ip' => '192.168.1.2',
                'author_user_agent' => 'Mozilla/5.0',
                'content' => '这个方法我试过了，确实很有效！',
                'status' => CommentStatus::PENDING->value,
                'karma' => 0,
            ],
            
            // 第二篇文章的评论
            [
                'post_id' => $posts[1]->id,
                'author_name' => '赵六',
                'author_email' => 'zhaoliu@example.com',
                'author_url' => null,
                'author_ip' => '192.168.1.3',
                'author_user_agent' => 'Mozilla/5.0',
                'content' => '程序员的生活确实如此，深有同感！',
                'status' => CommentStatus::APPROVED->value,
                'approved_by' => $user?->id,
                'approved_at' => now(),
                'karma' => 4,
            ],
            [
                'post_id' => $posts[1]->id,
                'author_name' => '孙七',
                'author_email' => 'sunqi@example.com',
                'author_url' => null,
                'author_ip' => '192.168.1.4',
                'author_user_agent' => 'Mozilla/5.0',
                'content' => '每天都在写代码，但乐在其中！',
                'status' => CommentStatus::APPROVED->value,
                'approved_by' => $user?->id,
                'approved_at' => now(),
                'karma' => 2,
            ],
            
            // 第三篇文章的评论
            [
                'post_id' => $posts[2]->id,
                'author_name' => '周八',
                'author_email' => 'zhouba@example.com',
                'author_url' => 'https://zhouba.dev',
                'author_ip' => '192.168.1.5',
                'author_user_agent' => 'Mozilla/5.0',
                'content' => 'Vue 3.0 的 Composition API 确实很强大，学习曲线有点陡峭。',
                'status' => CommentStatus::APPROVED->value,
                'approved_by' => $user?->id,
                'approved_at' => now(),
                'karma' => 6,
            ],
            [
                'post_id' => $posts[2]->id,
                'author_name' => 'Spammer',
                'author_email' => 'spam@spam.com',
                'author_url' => 'https://spam.com',
                'author_ip' => '1.2.3.4',
                'author_user_agent' => 'Bot/1.0',
                'content' => 'Buy cheap products here! Click this link!!!',
                'status' => CommentStatus::SPAM->value,
                'karma' => -10,
            ],
        ];

        foreach ($comments as $commentData) {
            $comment = Comment::create($commentData);
            
            // 为第一条评论添加回复
            if ($comment->id === 1) {
                Comment::create([
                    'post_id' => $comment->post_id,
                    'parent_id' => $comment->id,
                    'user_id' => $user?->id,
                    'author_name' => $user?->name ?? '作者',
                    'author_email' => $user?->email ?? 'author@example.com',
                    'author_url' => null,
                    'author_ip' => '127.0.0.1',
                    'author_user_agent' => 'Mozilla/5.0',
                    'content' => '感谢您的支持！如果有任何问题欢迎随时提问。',
                    'status' => CommentStatus::APPROVED->value,
                    'approved_by' => $user?->id,
                    'approved_at' => now(),
                    'karma' => 0,
                ]);
                
                // 更新父评论的回复数
                $comment->updateReplyCount();
            }
            
            // 为第二条评论添加回复
            if ($comment->id === 2) {
                Comment::create([
                    'post_id' => $comment->post_id,
                    'parent_id' => $comment->id,
                    'user_id' => $user?->id,
                    'author_name' => $user?->name ?? '作者',
                    'author_email' => $user?->email ?? 'author@example.com',
                    'author_url' => null,
                    'author_ip' => '127.0.0.1',
                    'author_user_agent' => 'Mozilla/5.0',
                    'content' => '示例代码已经上传到 GitHub，链接在文章末尾。',
                    'status' => CommentStatus::APPROVED->value,
                    'approved_by' => $user?->id,
                    'approved_at' => now(),
                    'karma' => 0,
                ]);
                
                $comment->updateReplyCount();
            }
        }

        $this->command->info('评论示例数据创建成功！');
        $this->command->info('- 已批准: ' . Comment::where('status', CommentStatus::APPROVED)->count());
        $this->command->info('- 待审核: ' . Comment::where('status', CommentStatus::PENDING)->count());
        $this->command->info('- 垃圾评论: ' . Comment::where('status', CommentStatus::SPAM)->count());
        $this->command->info('- 回复: ' . Comment::whereNotNull('parent_id')->count());
    }
}

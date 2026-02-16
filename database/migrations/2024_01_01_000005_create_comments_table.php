<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            
            // 关联文章
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete()
                ->comment('文章ID');
            
            // 父评论（用于回复）
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->cascadeOnDelete()
                ->comment('父评论ID');
            
            // 评论者信息
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('用户ID（登录用户）');
            
            $table->string('author_name', 100)->comment('评论者姓名');
            $table->string('author_email', 100)->comment('评论者邮箱');
            $table->string('author_url', 200)->nullable()->comment('评论者网址');
            $table->string('author_ip', 45)->nullable()->comment('评论者IP');
            $table->text('author_user_agent')->nullable()->comment('用户代理');
            
            // 评论内容
            $table->text('content')->comment('评论内容');
            
            // 状态和元数据
            $table->tinyInteger('status')
                ->default(0)
                ->comment('状态：0待审核 1已批准 2垃圾评论 3回收站');
            
            $table->string('type', 20)
                ->default('comment')
                ->comment('类型：comment评论 pingback引用 trackback追踪');
            
            // 审核信息
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('审核人ID');
            
            $table->timestamp('approved_at')->nullable()->comment('审核时间');
            
            // 统计
            $table->unsignedInteger('karma')->default(0)->comment('评分');
            $table->unsignedInteger('reply_count')->default(0)->comment('回复数量');
            
            $table->timestamps();
            $table->softDeletes()->comment('软删除');
            
            // 索引
            $table->index('post_id');
            $table->index('parent_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('author_email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};

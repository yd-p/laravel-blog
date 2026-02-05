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
        Schema::create('posts', function (Blueprint $table) {
            // 主键
            $table->id();
            // 关联分类ID（外键）
            $table->unsignedBigInteger('category_id')->comment('分类ID');
            // 文章标题
            $table->string('title', 200)->comment('文章标题');
            // 文章别名（URL友好，唯一）
            $table->string('slug', 250)->unique()->comment('文章别名（URL）');
            // 文章摘要
            $table->text('excerpt')->nullable()->comment('文章摘要');
            // 文章内容（长文本，支持富文本）
            $table->longText('content')->comment('文章内容');
            // 缩略图
            $table->string('thumbnail', 255)->nullable()->comment('文章缩略图');
            // 文章状态（1=草稿，2=已发布，3=回收站）
            $table->tinyInteger('status')->default(1)->comment('状态：1草稿 2已发布 3回收站');
            // 发布时间（可手动指定）
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            // 阅读量
            $table->integer('view_count')->default(0)->comment('阅读量');
            // SEO相关
            $table->string('seo_title', 200)->nullable()->comment('SEO标题');
            $table->string('seo_keywords', 200)->nullable()->comment('SEO关键词');
            $table->text('seo_description')->nullable()->comment('SEO描述');
            // 作者ID（关联用户表，默认关联Laravel的users表）
            $table->unsignedBigInteger('author_id')->comment('作者ID');
            // Laravel默认时间戳
            $table->timestamps();
            // 软删除
            $table->softDeletes();

            // 索引（优化查询）
            $table->index('category_id');
            $table->index('status');
            $table->index('published_at');
            $table->index('author_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 先删除外键约束再删表
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['author_id']);
        });
        Schema::dropIfExists('posts');
    }
};
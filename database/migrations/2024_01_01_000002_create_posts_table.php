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
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('category_id')->comment('分类ID');
            $table->string('title', 200)->comment('文章标题');
            $table->string('slug', 250)->unique()->comment('文章别名（URL）');
            $table->text('excerpt')->nullable()->comment('文章摘要');
            $table->longText('content')->comment('文章内容');
            $table->string('thumbnail')->nullable()->comment('文章缩略图');
            $table->tinyInteger('status')->unsigned()->default(1)->comment('状态：1草稿 2已发布 3回收站');
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            $table->integer('view_count')->default(0)->comment('阅读量');
            $table->string('seo_title', 200)->nullable()->comment('SEO标题');
            $table->string('seo_keywords', 200)->nullable()->comment('SEO关键词');
            $table->text('seo_description')->nullable()->comment('SEO描述');
            $table->unsignedBigInteger('author_id')->comment('作者ID');
            $table->timestamps();
            $table->softDeletes()->comment('软删除');
            
            $table->index('category_id');
            $table->index('status');
            $table->index('published_at');
            $table->index('author_id');
            
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('author_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
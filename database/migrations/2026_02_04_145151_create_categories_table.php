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
        Schema::create('categories', function (Blueprint $table) {
            // 主键
            $table->id();
            // 父分类ID（0表示顶级分类，实现无限层级）
            $table->unsignedBigInteger('parent_id')->default(0);
            // 分类名称
            $table->string('name', 100)->comment('分类名称');
            // 分类别名（URL友好，唯一）
            $table->string('slug', 150)->unique()->comment('分类别名（URL）');
            // 分类描述
            $table->text('description')->nullable()->comment('分类描述');
            // SEO相关
            $table->string('seo_title', 200)->nullable()->comment('SEO标题');
            $table->string('seo_keywords', 200)->nullable()->comment('SEO关键词');
            $table->text('seo_description')->nullable()->comment('SEO描述');
            // 排序（数字越大越靠前）
            $table->integer('sort')->default(0)->comment('排序值');
            // 状态（1=启用，0=禁用）
            $table->tinyInteger('status')->default(1)->comment('状态：1启用 0禁用');
            // Laravel默认时间戳
            $table->timestamps();
            // 软删除
            $table->softDeletes();

            // 索引（优化查询）
            $table->index('parent_id');
            $table->index('status');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

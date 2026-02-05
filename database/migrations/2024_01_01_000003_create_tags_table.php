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
        // 创建标签表
        Schema::create('tags', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('name', 100)->comment('标签名称');
            $table->string('slug', 150)->unique()->comment('标签别名（URL）');
            $table->text('description')->nullable()->comment('标签描述');
            $table->string('color', 20)->nullable()->comment('标签颜色');
            $table->unsignedInteger('post_count')->default(0)->comment('文章数量');
            $table->tinyInteger('status')->unsigned()->default(1)->comment('状态：0禁用 1启用');
            $table->timestamps();
            
            $table->index('status');
            $table->index('post_count');
        });
        
        // 创建文章标签关联表（多对多）
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('post_id')->comment('文章ID');
            $table->unsignedBigInteger('tag_id')->comment('标签ID');
            $table->timestamps();
            
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            
            $table->unique(['post_id', 'tag_id']);
            $table->index('post_id');
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tags');
    }
};

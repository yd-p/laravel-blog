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
        Schema::create('media', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('name')->comment('文件名称');
            $table->string('file_name')->comment('原始文件名');
            $table->string('mime_type')->comment('MIME类型');
            $table->string('disk')->default('public')->comment('存储磁盘');
            $table->string('path')->comment('文件路径');
            $table->string('collection_name')->nullable()->comment('集合名称');
            $table->unsignedBigInteger('size')->comment('文件大小(字节)');
            $table->json('custom_properties')->nullable()->comment('自定义属性');
            $table->json('responsive_images')->nullable()->comment('响应式图片');
            $table->unsignedInteger('order_column')->nullable()->comment('排序');
            
            // 图片特定字段
            $table->unsignedInteger('width')->nullable()->comment('图片宽度');
            $table->unsignedInteger('height')->nullable()->comment('图片高度');
            
            // 关联字段
            $table->morphs('model'); // model_type, model_id
            
            // 用户关联
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete()->comment('上传者ID');
            
            $table->timestamps();
            $table->softDeletes()->comment('软删除');
            
            // 索引
            $table->index(['model_type', 'model_id']);
            $table->index('collection_name');
            $table->index('mime_type');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

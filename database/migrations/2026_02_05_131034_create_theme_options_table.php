<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_options', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('theme_id')
                  ->constrained('themes')     // 外键关联 themes 表
                  ->onDelete('cascade');      // 主题删除时，自动删除所有选项
            
            $table->string('key');            // 配置键名（如：primary_color, logo_path）
            $table->text('value')->nullable(); // 配置值（支持较长内容）
            
            $table->timestamps();
            
            // 同一个主题下 key 应该唯一
            $table->unique(['theme_id', 'key'], 'theme_options_theme_id_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_options');
    }
};
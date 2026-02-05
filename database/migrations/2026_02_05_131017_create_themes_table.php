<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            
            $table->string('name');           // 主题名称
            $table->string('folder');         // 主题文件夹名（通常唯一）
            $table->string('version')->nullable();     // 版本号，可选
            $table->boolean('default')->default(false); // 是否默认主题
            $table->string('author')->nullable();      // 作者
            $table->string('link')->nullable();        // 作者链接或官网
            $table->text('description')->nullable();   // 描述
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
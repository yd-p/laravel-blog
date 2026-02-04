<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        // 钩子执行日志表
        Schema::create('hook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('hook_name')->index();
            $table->string('hook_id')->index();
            $table->string('action')->default('executed');
            $table->json('args')->nullable();
            $table->json('result')->nullable();
            $table->json('error')->nullable();
            $table->decimal('execution_time', 8, 4)->nullable();
            $table->integer('memory_usage')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['hook_name', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // 模型审计表
        Schema::create('model_audits', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->index();
            $table->string('record_id')->index();
            $table->string('action'); // created, updated, deleted
            $table->json('changes');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['table_name', 'record_id']);
            $table->index(['user_id', 'created_at']);
        });

        // 插件操作日志表
        Schema::create('plugin_logs', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_name')->index();
            $table->string('action'); // installed, enabled, disabled, uninstalled, deleted
            $table->json('plugin_info')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['plugin_name', 'action']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_logs');
        Schema::dropIfExists('model_audits');
        Schema::dropIfExists('hook_logs');
    }
};
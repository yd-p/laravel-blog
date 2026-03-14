<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('任务名称');
            $table->string('command')->comment('Artisan 命令，如 inspire 或 cache:clear');
            $table->string('cron')->comment('Cron 表达式，如 * * * * *');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->boolean('without_overlapping')->default(true)->comment('禁止重叠执行');
            $table->boolean('run_in_background')->default(false)->comment('后台运行');
            $table->text('description')->nullable()->comment('备注');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_jobs');
    }
};

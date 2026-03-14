<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugin_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 64)->unique()->comment('订单号');
            $table->unsignedBigInteger('user_id')->comment('购买用户');
            $table->string('plugin_id', 128)->comment('插件ID');
            $table->string('plugin_name', 255)->comment('插件名称');
            $table->string('plugin_folder', 128)->comment('插件目录名');
            $table->string('plugin_version', 32)->default('')->comment('插件版本');
            $table->unsignedInteger('amount')->comment('金额（分）');
            $table->string('currency', 8)->default('CNY');
            $table->tinyInteger('status')->default(0)->comment('0待支付 1已支付 2已退款 3已取消');
            $table->string('payment_method', 32)->default('')->comment('alipay/wechat');
            $table->string('payment_trade_no', 128)->default('')->comment('第三方交易号');
            $table->string('download_url', 512)->default('')->comment('支付后下载地址');
            $table->timestamp('paid_at')->nullable();
            $table->json('payment_raw')->nullable()->comment('原始回调数据');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('plugin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_orders');
    }
};

<?php

use App\Order;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned()->comment('客户');
            $table->timestamp('scene_time')->nullable()->comment('场次时间');
            $table->tinyInteger('type')->unsigned()->comment('类型');
            $table->decimal('amount', 20)->unsigned()->comment('金额');
            $table->decimal('rate')->unsigned()->comment('赔率');
            $table->softDeletes();
            $table->timestamps();
        });
        table_comment('orders', '订单');

        Schema::create('order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->decimal('price', 20)->unsigned()->comment('价格');
            $table->integer('count')->unsigned()->comment('数量');
            $table->softDeletes();
            $table->timestamps();
        });
        table_comment('order_details', '订单详情');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_details');
    }
}

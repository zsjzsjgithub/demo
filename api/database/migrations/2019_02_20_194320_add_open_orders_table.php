<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOpenOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('open', 6, 5)->unsigned()->after('scene_time')->default(0)->comment('参考开盘价');
            $table->integer('forex_data_id')->unsigned()->after('rate')->default(0)->comment('结算时参照的汇率数据');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('open', 'forex_data_id');
        });
    }
}

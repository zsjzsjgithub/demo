<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForexdataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forex_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('time')->unique()->comment('数据时间');
            $table->decimal('open', 6, 5)->comment('开盘价');
            $table->decimal('close', 6, 5)->comment('收盘价');
            $table->decimal('high', 6, 5)->comment('最高价');
            $table->decimal('low', 6, 5)->comment('最低价');
        });
        DB::statement('ALTER TABLE `forex_datas` comment "外汇数据"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forex_datas');
    }
}

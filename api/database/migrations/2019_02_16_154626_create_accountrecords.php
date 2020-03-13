<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountrecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('客户');
            $table->tinyInteger('type')->unsigned()->comment('类型');
            $table->decimal('amount')->comment('金额');
            $table->decimal('balance')->comment('余额');
            $table->tinyInteger('status')->unsigned()->comment('状态');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE `account_records` comment "账户记录"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_records');
    }
}

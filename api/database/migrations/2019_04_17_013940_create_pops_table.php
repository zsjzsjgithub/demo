<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pops', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 200)->comment('弹窗标题');
            $table->text('content')->comment('内容');
            $table->integer('width')->unsigned()->default(0)->comment('宽度');
            $table->integer('height')->unsigned()->default(0)->comment('高度');
            $table->string('xt')->default('left')->comment('x轴相对方向');
            $table->integer('x')->unsigned()->default(0)->comment('x轴距离');
            $table->string('yt')->default('top')->comment('y轴相对方向');
            $table->integer('y')->unsigned()->default(0)->comment('y轴距离');
            $table->boolean('is_enable')->default(true)->comment('启用状态');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pops');
    }
}

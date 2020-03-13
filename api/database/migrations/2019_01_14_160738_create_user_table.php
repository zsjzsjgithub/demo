<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 20);
            $table->string('nickname', 50);
            $table->string('tel')->default('');
            $table->string('real_name')->default('')->comment('真实姓名');
            $table->string('bank_name')->default('')->comment('银行名称');
            $table->string('bank_number')->default('')->comment('银行卡号');
            $table->string('password');
            $table->integer('agent_id')->unsigned()->default(0)->comment('代理人ID');
            $table->tinyInteger('type')->unsigned()->comment('用户类型：1.管理员 2.代理 3.会员');
            $table->boolean('is_enabled')->default(true)->comment('是否启用');
            $table->decimal('deposit', 20)->default(0.00)->comment('存款');
            $table->decimal('withdrawal', 20)->default(0.00)->comment('取款');
            $table->decimal('balance', 20)->default(0.00)->comment('余额');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE `users` comment "用户"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

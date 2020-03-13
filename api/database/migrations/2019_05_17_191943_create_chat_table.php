<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('author_id')->unsigned()->comment('作者');
            $table->integer('member_id')->unsigned()->comment('会员');
            $table->string('content', 500);
            $table->tinyInteger('type')->unsigned()->comment('类型');
            $table->boolean('is_read')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        table_comment('chats', '聊天消息');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chats');
    }
}

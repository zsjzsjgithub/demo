<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type')->unsigned()->comment('类型');
            $table->string('title')->default('')->comment('主题');
            $table->text('content')->comment('内容');
            $table->integer('author_id')->unsigned()->comment('作者');
            $table->integer('pageviews')->unsigned()->default(0)->comment('浏览量');
            $table->integer('message_id')->unsigned()->default(0)->comment('主题ID（回复）');
            $table->boolean('has_question')->default(false)->comment('是否有新问题');
            $table->boolean('has_answer')->default(false)->comment('是否有新回复');
            $table->boolean('is_solved')->default(false)->comment('是否已解决');
            $table->timestamp('replied_at')->nullable()->comment('回复时间');
            $table->timestamps();
            $table->softDeletes();
        });
        table_comment('messages', '信息');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}

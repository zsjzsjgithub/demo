<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeForexdattas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forex_datas', function (Blueprint $table) {
            $table->tinyInteger('type')->unsigned()->default(1)->after('id')->comment('汇率类型');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forex_datas', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}

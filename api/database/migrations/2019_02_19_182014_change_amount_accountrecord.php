<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAmountAccountrecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_records', function (Blueprint $table) {
            $table->decimal('amount', 20)->change();
            $table->decimal('balance', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_records', function (Blueprint $table) {
            $table->decimal('amount')->change();
            $table->decimal('balance')->change();
        });
    }
}

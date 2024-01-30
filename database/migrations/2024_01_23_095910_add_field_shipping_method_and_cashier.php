<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldShippingMethodAndCashier extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_doli', function (Blueprint $table) {
            $table->string('shipping_method')->after('user_id')->nullable();
            $table->integer('cashier')->after('seller')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_doli', function (Blueprint $table) {
            //
        });
    }
}

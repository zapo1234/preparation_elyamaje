<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldBilling extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_doli', function (Blueprint $table) {
            $table->string('billing_name')->nullable()->default(null);
            $table->string('billing_pname')->nullable()->default(null);
            $table->string('billing_adresse')->nullable()->default(null);
            $table->string('billing_city')->nullable()->default(null);
            $table->string('billing_company')->nullable()->default(null);
            $table->string('billing_country')->nullable()->default(null);
            $table->string('billing_phone')->nullable()->default(null);
            $table->integer('seller')->nullable()->default(null);
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('order_woocommerce_id')->unique();
            $table->string('coupons');
            $table->float('discount');
            $table->integer('discount_amount');
            $table->integer('customer_id');
            $table->string('billing_customer_first_name');
            $table->string('billing_customer_last_name');
            $table->string('billing_customer_company');
            $table->string('billing_customer_address_1');
            $table->string('billing_customer_address_2');
            $table->string('billing_customer_city');
            $table->string('billing_customer_state');
            $table->string('billing_customer_postcode');
            $table->string('billing_customer_country');
            $table->string('billing_customer_email');
            $table->string('billing_customer_phone');
            $table->string('shipping_customer_first_name');
            $table->string('shipping_customer_last_name');
            $table->string('shipping_customer_company');
            $table->string('shipping_customer_address_1');
            $table->string('shipping_customer_address_2');
            $table->string('shipping_customer_city');
            $table->string('shipping_customer_state');
            $table->string('shipping_customer_postcode');
            $table->string('shipping_customer_country');
            $table->string('shipping_customer_phone');
            $table->datetime('date');
            $table->float('total_tax_order');
            $table->float('total_order');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('status');
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
        Schema::dropIfExists('orders');
    }
}

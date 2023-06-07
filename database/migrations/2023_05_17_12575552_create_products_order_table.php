<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_order', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('product_woocommerce_id');
            $table->string('category');
            $table->integer('category_id');
            $table->string('name');
            $table->integer('quantity');
            $table->float('cost');
            $table->float('subtotal_tax');
            $table->float('total_tax');
            $table->float('total_price');
            $table->float('weight');
            $table->integer('pick'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_order');
    }
}

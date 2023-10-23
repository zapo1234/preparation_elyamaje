<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsDolibarr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_dolibarr', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('label')->default(NULL);
            $table->float('price_ttc');
            $table->string('barcode')->default(NULL);
            $table->float('poids')->default(NULL);
            $table->json('warehouse_array_list');
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
        Schema::dropIfExists('products_dolibarr');
    }
}

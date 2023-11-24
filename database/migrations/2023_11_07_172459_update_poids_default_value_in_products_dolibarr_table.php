<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePoidsDefaultValueInProductsDolibarrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_dolibarr', function (Blueprint $table) {
            $table->float('poids', 8, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products_dolibarr', function (Blueprint $table) {
            $table->float('poids', 8, 2)->default(null)->change();
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_code', function (Blueprint $table) {
            $table->id();
            $table->integer("order_id")->unique();
            $table->string("first_name");
            $table->string("last_name");
            $table->string("phone");
            $table->string("email");
            $table->string("code");
            $table->float("total_ht");
            $table->float("total_ttc");
            $table->string("status");
            $table->datetime("status_updated");
            $table->datetime("order_date");
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
        Schema::dropIfExists('discount_code');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistReassort extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hist_reassort', function (Blueprint $table) {
            $table->id();
            $table->string('libelle_reassort');
            $table->integer('id_reassort');
            $table->integer('product_id');
            $table->integer('warehouse_id');
            $table->integer('qty');
            $table->integer('type');
            $table->string('movementcode')->nullable();
            $table->string('movementlabel');
            $table->float('price');
            $table->datetime('datem');
            $table->datetime('dlc');
            $table->datetime('dluo');
            $table->timestamps(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hist_reassort');
    }
}

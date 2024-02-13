<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTerminalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terminal', function (Blueprint $table) {
            $table->id();
            $table->string('ip_adress')->unique();
            $table->string('poiId')->unique();
            $table->string('serviceId')->default("1"); 
            $table->string('saleId')->default("2"); 
            $table->string('operatorId')->default("3"); 
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
        Schema::dropIfExists('terminal');
    }
}

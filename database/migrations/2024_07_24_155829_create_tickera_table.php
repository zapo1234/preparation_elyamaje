<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTickeraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickera', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('email');
            $table->string('ticket_id');
            $table->string('montant_attribue');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations..
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickera');
    }
}

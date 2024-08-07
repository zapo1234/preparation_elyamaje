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
            $table->string('adresse')->nullable();
            $table->string('zip')->nullable();
            $table->string('city')->nullable();
            $table->string('email');
            $table->string('ticket_id');
            $table->string('montant_attribue');
            $table->string('code_reduction');
            $table->integer('amount_wheel')->default(0); // Montant gagné à la roue de la fortune
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

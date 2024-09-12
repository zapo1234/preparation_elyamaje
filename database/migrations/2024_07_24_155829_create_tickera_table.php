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
            $table->string('nom');
            $table->string('prenom');
            $table->string('socid')->nullable();
            $table->string('code_client')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('adresse');
            $table->string('zip_code');
            $table->string('ville');
            $table->string('date_created');
            $table->string('montant_attribue');
            $table->string('gift_card');
            $table->string('code_reduction');
            $table->string('ticket_id');
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersDoli extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_doli', function (Blueprint $table) {
            $table->id();

            $table->string('ref_order');
            $table->integer('fk_commande');
            $table->string('socid');
            $table->string('name');
            $table->string('pname');
            $table->string('adresse');
            $table->string('city');
            $table->string('company');
            $table->string('code_postal');
            $table->string('contry');
            $table->string('email');
            $table->string('phone');
            $table->datetime('date');
            $table->float('total_tax');
            $table->float('total_order_ttc');
            $table->integer('user_id')->default(0);
            $table->string('vendeuse');
            $table->string('payment_methode');
            $table->string('statut')->default("processing");

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
        Schema::dropIfExists('orders_doli');
    }
}

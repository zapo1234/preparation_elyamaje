<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinesCommandeDoli extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lines_commande_doli', function (Blueprint $table) {
            $table->id();

            $table->integer('id_commande');
            $table->string('libelle');
            $table->string('id_product');
            $table->string('barcode');
            $table->float('price');
            $table->integer('qte');
            $table->integer('remise_percent')->default(0);

            $table->float('total_ht');
            $table->float('total_tva');
            $table->float('total_ttc');


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
        Schema::dropIfExists('lines_commande_doli');
    }
}

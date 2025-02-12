<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdCountry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('id_country', function (Blueprint $table) {
            $table->id();
            $table->integer('rowid');
            $table->string('code');
            $table->string('code_iso');
            $table->string('label');
            $table->integer('active');
            $table->integer('eec');
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
        //
        Schema::dropIfExists('id_country');
    }
}

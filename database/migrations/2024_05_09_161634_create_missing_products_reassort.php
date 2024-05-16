<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMissingProductsReassort extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missing_products_reassort', function (Blueprint $table) {
            $table->id();
            $table->integer('identifiant_reassort')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE prepa_missing_products_reassort ADD missing MEDIUMBLOB AFTER identifiant_reassort");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('missing_products_reassort');
    }
}

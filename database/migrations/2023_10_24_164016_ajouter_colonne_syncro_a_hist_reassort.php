<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AjouterColonneSyncroAHistReassort extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hist_reassort', function (Blueprint $table) {
            $table->integer('syncro')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hist_reassort', function (Blueprint $table) {
            $table->dropColumn('syncro');
        });
    }
}

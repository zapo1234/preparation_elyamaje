<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OriginIdReassortToHistReassort extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hist_reassort', function (Blueprint $table) {
            $table->string('origin_id_reassort')->nullable();
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
            $table->dropColumn('origin_id_reassort');
        });
    }
}




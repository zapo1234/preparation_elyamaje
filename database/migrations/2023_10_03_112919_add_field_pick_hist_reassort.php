<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldPickHistReassort extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hist_reassort', function (Blueprint $table) {
            $table->integer('pick')->after('qty')->default(0);
            $table->string('status')->after('pick')->default("processing");
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
            //
        });
    }
}

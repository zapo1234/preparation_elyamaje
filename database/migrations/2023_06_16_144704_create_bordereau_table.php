<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBordereauTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bordereau', function (Blueprint $table) {
            $table->id();
            $table->integer('parcel_number');
            $table->timestamps();

        });


        DB::statement("ALTER TABLE prepa_bordereau ADD bordereau MEDIUMBLOB AFTER parcel_number");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bordereau');
    }
}

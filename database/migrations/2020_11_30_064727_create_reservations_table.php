<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->increments('id');
	    $table->dateTime('session_start_time');
	    $table->dateTime('session_end_time');
	    $table->unsignedInteger('masseur_id');
            $table->timestamps();
	    //relation to masseurs table
	    $table->foreign('masseur_id')->references('id')->on('masseurs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations');
    }
}

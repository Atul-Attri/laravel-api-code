<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
             $table->unsignedBigInteger('user_id');
            $table->enum('type', ['company', 'permanent']);
            $table->string('street');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('pincode');
            $table->timestamps();

            $table->unique(['user_id', 'type']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}

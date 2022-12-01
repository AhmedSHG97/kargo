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
            $table->id();
            $table->string("address");
            $table->string("name");
            $table->string("phone");
            $table->string("mobile")->nullable();
            $table->string("email");
            $table->boolean("mine");
            $table->integer("user_id");
            $table->integer("country_id");
            $table->integer("state_id");
            $table->integer("county_id");
            $table->string("shipping_address_id");
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
        Schema::dropIfExists('addresses');
    }
}

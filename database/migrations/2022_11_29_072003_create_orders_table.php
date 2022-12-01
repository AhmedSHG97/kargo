<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer("shipment_type_id");
            $table->integer("sender_address_id");
            $table->integer("receiver_address_id");
            $table->json("shipment_option_id");
            $table->double("piece_count");
            $table->integer("user_id");
            $table->enum("order_type",["incoming", "outgoing"]);
            $table->double("weight")->nullable();
            $table->double("size")->nullable();
            $table->integer("additional_service_id")->nullable();
            $table->integer("delivery_service_id")->nullable();
            $table->integer("other_service_id")->nullable();
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
        Schema::dropIfExists('orders');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToShipmentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipment_types', function (Blueprint $table) {
            $table->string("name")->nullable()->after("id");
            $table->boolean("status")->default(0)->after("id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipment_types', function (Blueprint $table) {
            $table->dropColumn("name");
            $table->dropColumn("status");
        });
    }
}

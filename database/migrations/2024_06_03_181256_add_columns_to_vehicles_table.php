<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('capacity')->nullable();
            $table->decimal('fuel_consumption', 8, 2)->nullable();
            $table->decimal('fuel_cost', 8, 2)->nullable();
            $table->integer('speed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('capacity');
            $table->dropColumn('fuel_consumption');
            $table->dropColumn('fuel_cost');
            $table->dropColumn('speed');
        });
    }
}

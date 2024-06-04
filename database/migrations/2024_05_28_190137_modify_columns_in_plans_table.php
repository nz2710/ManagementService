<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsInPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('total_demand', 20, 10)->change();
            $table->decimal('total_distance', 20, 10)->change();
            $table->decimal('total_time_serving', 20, 10)->change();
            $table->decimal('total_demand_without_allocating_vehicles', 20, 10)->change();
            $table->decimal('total_distance_without_allocating_vehicles', 20, 10)->change();
            $table->decimal('total_time_serving_without_allocating_vehicles', 20, 10)->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('total_demand', 20, 15)->change();
            $table->decimal('total_distance', 20, 15)->change();
            $table->decimal('total_time_serving', 20, 15)->change();
            $table->decimal('total_demand_without_allocating_vehicles', 20, 15)->change();
            $table->decimal('total_distance_without_allocating_vehicles', 20, 15)->change();
            $table->decimal('total_time_serving_without_allocating_vehicles', 20, 15)->change();
        });
    }
}

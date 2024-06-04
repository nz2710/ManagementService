<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('total_demand', 20, 15);
            $table->decimal('total_distance', 20, 15);
            $table->decimal('total_time_serving', 20, 15);
            $table->decimal('total_demand_without_allocating_vehicles', 20, 15);
            $table->decimal('total_distance_without_allocating_vehicles', 20, 15);
            $table->decimal('total_time_serving_without_allocating_vehicles', 20, 15);
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
        Schema::dropIfExists('plans');
    }
}

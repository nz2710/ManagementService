<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCostColumnsToPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('moving_cost', 20, 10)->nullable();
            $table->decimal('labor_cost', 20, 10)->nullable();
            $table->decimal('unloading_cost', 20, 10)->nullable();
            $table->decimal('total_order_value', 20, 10)->nullable();
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
            $table->dropColumn('moving_cost');
            $table->dropColumn('labor_cost');
            $table->dropColumn('unloading_cost');
            $table->dropColumn('total_order_value');
        });
    }
}

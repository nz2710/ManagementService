<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('fee', 20,10 )->nullable();
            $table->integer('total_vehicle_used')->nullable();
            $table->integer('total_num_customer_served')->nullable();
            $table->integer('total_num_customer_not_served')->nullable();
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
            $table->dropColumn('fee');
            $table->dropColumn('total_vehicle_used');
            $table->dropColumn('total_num_customer_served');
            $table->dropColumn('total_num_customer_not_served');
        });
    }
}

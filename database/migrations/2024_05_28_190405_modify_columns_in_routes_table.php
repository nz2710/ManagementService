<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsInRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->decimal('total_demand',20,10)->change();
            $table->decimal('total_distance',20,10)->change();
            $table->decimal('total_time_serving',20,10)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->decimal('total_demand',20,15)->change();
            $table->decimal('total_distance',20,15)->change();
            $table->decimal('total_time_serving',20,15)->change();
        });
    }
}

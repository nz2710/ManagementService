<?php

use App\Models\Vehicle;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['name', 'capacity', 'velocity', 'driver_name', 'vehicle_type', 'status']);
            $table->integer('total_vehicles')->default(0);
        });

        // Tạo một bản ghi mới với total_vehicles = 0
        Vehicle::create([
            'total_vehicles' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('name');
            $table->integer('capacity')->nullable();
            $table->integer('velocity')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('status')->nullable()->default(1);
            $table->dropColumn('total_vehicles');
        });
    }
}

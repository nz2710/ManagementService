<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjustLongitudeLatitudePrecision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Thay đổi độ chính xác của cột longitude
            $table->decimal('longitude', 20, 16)->change();
            // Thay đổi độ chính xác của cột latitude nếu cần
            $table->decimal('latitude', 20, 16)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Trả về độ chính xác cũ
            $table->decimal('longitude', 18, 16)->change();
            $table->decimal('latitude', 18, 16)->change();
        });
    }
}

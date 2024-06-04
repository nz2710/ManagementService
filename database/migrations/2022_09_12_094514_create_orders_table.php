<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->bigIncrements('id');
            $table->string('code_order')->nullable();
            $table->string('customer_name')->index()->nullable();
            $table->decimal('price', 16, 2)->default(0);
            $table->decimal('mass_of_order', 16, 2)->nullable();
            $table->string('address')->nullable();
            $table->decimal('longitude',18,16)->nullable();
            $table->decimal('latitude',18,16)->nullable();
            // $table->time('time_open')->nullable();
            // $table->time('time_close')->nullable();
            $table->time('time_service')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
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

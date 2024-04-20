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
            $table->string('customer_name')->index();
            $table->decimal('price', 16, 2)->default(0);
            $table->decimal('mass_of_order', 16, 2)->nullable();
            $table->string('address')->nullable();
            $table->decimal('longitude')->nullable();
            $table->decimal('latitude')->nullable();
            $table->time('time_open')->nullable();
            $table->time('time_close')->nullable();
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

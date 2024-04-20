<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->index();
            $table->date('register_date')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->integer('number_of_order')->default(0);
            $table->decimal('discount', 8, 2)->default(0.00);
            $table->decimal('revenue', 10, 2)->default(0.00);
            $table->string('status')->default(1);
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
        Schema::dropIfExists('partners');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->json('preview')->nullable();
            $table->string('name');
            $table->text('description');
            $table->text('compatible')->nullable();
            $table->decimal('price', 16, 2)->default(0);
            $table->decimal('support_price', 16, 2)->default(0);
            $table->decimal('discount_price', 16, 2)->default(0);
            $table->text('attachments')->nullable();
            $table->string('approve_status')->default('pending');
            $table->integer('replace_id')->nullable();
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
        Schema::dropIfExists('products');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Thêm trường sku
            $table->string('sku')->unique()->after('name');

            // Thay đổi kiểu dữ liệu của trường price và cost
            $table->decimal('price', 10, 2)->change();
            $table->decimal('cost', 10, 2)->after('price');

            // Thêm giá trị mặc định cho trường quantity
            $table->integer('quantity')->default(0)->change();

            // Thêm trường image
            $table->string('image')->nullable()->after('quantity');

            // Thêm trường status
            $table->enum('status', ['active', 'inactive'])->default('active')->after('image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Xóa các trường đã thêm
            $table->dropColumn('sku');
            $table->dropColumn('cost');
            $table->dropColumn('image');
            $table->dropColumn('status');

            // Khôi phục kiểu dữ liệu và ràng buộc của các trường
            $table->decimal('price', 8, 2)->change();
            $table->integer('quantity')->change();
        });
    }
}

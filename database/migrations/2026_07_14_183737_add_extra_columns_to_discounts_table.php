<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('discounts', function (Blueprint $table) {
            // Thêm cột type - loại chiết khấu
            $table->enum('type', ['quantity_based', 'amount_based'])
                  ->default('quantity_based')
                  ->after('order_code')
                  ->comment('Loại chiết khấu: quantity_based hoặc amount_based');
            
            // Thêm cột min_amount - giá trị đơn hàng tối thiểu
            $table->decimal('min_amount', 15, 2)
                  ->nullable()
                  ->after('type')
                  ->comment('Số tiền tối thiểu cho loại amount_based');
            
            // Thêm cột is_active - trạng thái kích hoạt
            $table->boolean('is_active')
                  ->default(false)
                  ->after('min_amount')
                  ->comment('Trạng thái kích hoạt: true = hoạt động, false = tắt');
        });
    }

    public function down()
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['type', 'min_amount', 'is_active']);
        });
    }
};
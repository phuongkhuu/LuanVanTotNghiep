<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Thêm các cột mới, không dùng after('payment_method')
            $table->decimal('deposit_amount', 15, 0)->default(0)->after('final_amount');
            $table->decimal('remaining_amount', 15, 0)->default(0)->after('deposit_amount');
            $table->enum('payment_status', ['pending', 'deposit_paid', 'paid', 'failed'])->default('pending')->after('remaining_amount');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'remaining_amount', 'payment_status']);
        });
    }
};
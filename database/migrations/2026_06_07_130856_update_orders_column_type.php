<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Đổi tên cột order_code thành type
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('order_code', 'type');
        });

        // 2. Cập nhật dữ liệu: chuyển 'normal' -> 'retail' (vẫn dùng ENUM cũ)
        DB::table('orders')->where('type', 'normal')->update(['type' => 'retail']);

        // 3. Bây giờ mới thay đổi ENUM, vì không còn giá trị 'normal'
        DB::statement("ALTER TABLE orders MODIFY type ENUM('retail', 'wholesale', 'preorder') NOT NULL DEFAULT 'retail'");
    }

    public function down()
    {
        // 1. Xử lý các bản ghi có type = 'preorder' (nếu có) trước khi chuyển về ENUM cũ
        DB::table('orders')->where('type', 'preorder')->update(['type' => 'retail']);

        // 2. Quay lại ENUM cũ ('wholesale', 'normal')
        DB::statement("ALTER TABLE orders MODIFY type ENUM('wholesale', 'normal') NOT NULL DEFAULT 'normal'");

        // 3. Cập nhật dữ liệu: 'retail' -> 'normal'
        DB::table('orders')->where('type', 'retail')->update(['type' => 'normal']);

        // 4. Đổi tên cột lại thành order_code
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('type', 'order_code');
        });
    }
};
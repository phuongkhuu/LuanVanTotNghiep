<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Sửa cột order_code để thêm giá trị 'customize'
        DB::statement("ALTER TABLE orders MODIFY order_code ENUM('retail','wholesale','preorder','customize') NOT NULL DEFAULT 'retail'");
    }

    public function down()
    {
        // Rollback về trạng thái cũ
        DB::statement("ALTER TABLE orders MODIFY order_code ENUM('retail','wholesale','preorder') NOT NULL DEFAULT 'retail'");
    }
};
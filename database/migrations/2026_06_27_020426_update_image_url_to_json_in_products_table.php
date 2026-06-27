<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Đổi kiểu cột từ string sang json, giữ nguyên nullable
            $table->json('image_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Quay lại kiểu string (có thể bị lỗi nếu dữ liệu không phải chuỗi thuần)
            $table->string('image_url')->nullable()->change();
        });
    }
};
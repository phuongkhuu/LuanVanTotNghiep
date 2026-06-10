<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Nếu bảng colors chưa tồn tại, tạo mới
        if (!Schema::hasTable('colors')) {
            Schema::create('colors', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code', 50)->nullable();
                $table->timestamps();
            });
        } else {
            // Nếu đã có, chỉ thêm cột code
            Schema::table('colors', function (Blueprint $table) {
                $table->string('code', 50)->nullable()->after('name');
            });
        }

        // Gán mã hex mặc định cho các màu hiện có (nếu cột code vừa được thêm)
        $colors = \App\Models\Color::all();
        $colorMap = [
            'Đen' => '#000000',
            'Xám' => '#9ca3af',
            'Xanh Navy' => '#1e3a8a',
            'Nâu' => '#78350f',
            'Đỏ' => '#dc2626',
            'Vàng' => '#fbbf24',
            'Trắng' => '#ffffff',
            'Hồng' => '#fbcfe8',
            'Cam' => '#f97316',
            'Tím' => '#a855f7',
        ];
        foreach ($colors as $color) {
            if (empty($color->code)) {
                $color->code = $colorMap[$color->name] ?? '#cccccc';
                $color->save();
            }
        }
    }

    public function down()
    {
        Schema::table('colors', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
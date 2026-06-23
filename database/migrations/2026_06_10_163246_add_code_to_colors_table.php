<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        if (!Schema::hasTable('colors')) {
            Schema::create('colors', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code', 50)->nullable();
                $table->timestamps();
            });
        } else {

            if (!Schema::hasColumn('colors', 'code')) {
                Schema::table('colors', function (Blueprint $table) {
                    $table->string('code', 50)->nullable()->after('name');
                });
            }
        }


        if (class_exists('App\Models\Color')) {
            $colors = \App\Models\Color::all();
            $colorMap = [
                'Đen' => '#000000', 'den' => '#000000', 'black' => '#000000',
                'Trắng' => '#FFFFFF', 'trang' => '#FFFFFF', 'white' => '#FFFFFF',
                'Xám' => '#808080', 'xam' => '#808080', 'gray' => '#808080',
                'Đỏ' => '#FF0000', 'do' => '#FF0000', 'red' => '#FF0000',
                'Hồng' => '#FFC0CB', 'hong' => '#FFC0CB', 'pink' => '#FFC0CB',
                'Cam' => '#FFA500', 'orange' => '#FFA500',
                'Vàng' => '#FFD700', 'vang' => '#FFD700', 'yellow' => '#FFD700',
                'Xanh lá' => '#008000', 'green' => '#008000',
                'Xanh dương' => '#0000FF', 'blue' => '#0000FF',
                'Xanh navy' => '#000080', 'navy' => '#000080',
                'Tím' => '#800080', 'tim' => '#800080', 'purple' => '#800080',
                'Nâu' => '#8B4513', 'nau' => '#8B4513', 'brown' => '#8B4513',
                'Be' => '#F5F5DC', 'beige' => '#F5F5DC',
                'Bạc' => '#C0C0C0', 'silver' => '#C0C0C0',
            ];
            
            foreach ($colors as $color) {
                if (empty($color->code)) {
                    $key = strtolower($color->name);
                    $color->code = $colorMap[$key] ?? '#CCCCCC';
                    $color->save();
                }
            }
        }
    }

    public function down()
    {
        Schema::table('colors', function (Blueprint $table) {
            if (Schema::hasColumn('colors', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};
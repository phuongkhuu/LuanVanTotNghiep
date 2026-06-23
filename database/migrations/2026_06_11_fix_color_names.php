<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Color;

return new class extends Migration
{
    public function up()
    {

        $colors = Color::all();
        
        $fixMap = [
            'Màu #490C42' => 'Tím than',
            'Màu #490c42' => 'Tím than',
            'Mẫu #490C42' => 'Tím than',
            'cam' => 'Cam',
        ];
        
        foreach ($colors as $color) {
            if (isset($fixMap[$color->name])) {
                $color->name = $fixMap[$color->name];
                $color->save();
            }
            

            if ($color->code) {
                $color->code = strtoupper($color->code);
                $color->save();
            }
        }
    }

    public function down()
    {

    }
};
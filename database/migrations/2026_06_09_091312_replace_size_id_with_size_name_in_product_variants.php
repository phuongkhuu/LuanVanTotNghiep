<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ProductVariant;
use App\Models\Size;

return new class extends Migration
{
    public function up()
    {
        // 1. Thêm cột size_name tạm thời
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('size_name', 100)->nullable()->after('size_id');
        });

        // 2. Copy dữ liệu từ bảng sizes sang size_name
        $variants = ProductVariant::with('size')->get();
        foreach ($variants as $variant) {
            if ($variant->size) {
                $variant->size_name = $variant->size->name;
                $variant->save();
            }
        }

        // 3. Xóa khóa ngoại và cột size_id
        Schema::table('product_variants', function (Blueprint $table) {
            // Tên foreign key thường là 'product_variants_size_id_foreign'
            $table->dropForeign(['size_id']);
            $table->dropColumn('size_id');
        });

        // 4. Xóa bảng sizes
        Schema::dropIfExists('sizes');

        // 5. (Khuyến nghị) Đặt size_name là NOT NULL nếu bắt buộc phải có size
        // Schema::table('product_variants', function (Blueprint $table) {
        //     $table->string('size_name', 100)->nullable(false)->change();
        // });
    }

    public function down()
    {
        // Rollback phức tạp, có thể bỏ qua hoặc implement nếu cần
    }
};
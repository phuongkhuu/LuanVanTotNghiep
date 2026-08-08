<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('import_quantity')->default(0)->after('stock');
            $table->decimal('import_price', 12, 0)->nullable()->after('import_quantity');
            $table->timestamp('last_import_date')->nullable()->after('import_price');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['import_quantity', 'import_price', 'last_import_date']);
        });
    }
};
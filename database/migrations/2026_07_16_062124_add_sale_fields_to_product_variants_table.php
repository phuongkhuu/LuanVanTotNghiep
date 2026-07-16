<?php
// database/migrations/2026_07_16_000000_add_sale_fields_to_product_variants_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'sale_price')) {
                $table->decimal('sale_price', 12, 0)->nullable()->after('price');
            }
            if (!Schema::hasColumn('product_variants', 'is_on_sale')) {
                $table->boolean('is_on_sale')->default(false)->after('sale_price');
            }
            if (!Schema::hasColumn('product_variants', 'sale_type')) {
                $table->enum('sale_type', ['campaign', 'preorder'])->nullable()->after('is_on_sale');
            }
            if (!Schema::hasColumn('product_variants', 'sale_campaign_id')) {
                $table->foreignId('sale_campaign_id')->nullable()->constrained('campaigns')->nullOnDelete()->after('sale_type');
            }
        });
    }

    public function down()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign(['sale_campaign_id']);
            $table->dropColumn(['sale_price', 'is_on_sale', 'sale_type', 'sale_campaign_id']);
        });
    }
};
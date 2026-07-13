<?php
// database/migrations/2026_07_13_000000_remove_sale_fields_from_product_variants.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn([
                'original_price',
                'sale_price',
                'sale_type',
                'sale_campaign_id',
                'sale_start',
                'sale_end',
                'is_on_sale'
            ]);
        });
    }

    public function down()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('original_price', 15, 2)->nullable()->after('price');
            $table->decimal('sale_price', 15, 2)->nullable()->after('original_price');
            $table->string('sale_type')->nullable()->after('sale_price');
            $table->unsignedBigInteger('sale_campaign_id')->nullable()->after('sale_type');
            $table->timestamp('sale_start')->nullable()->after('sale_campaign_id');
            $table->timestamp('sale_end')->nullable()->after('sale_start');
            $table->boolean('is_on_sale')->default(false)->after('sale_end');
        });
    }
};

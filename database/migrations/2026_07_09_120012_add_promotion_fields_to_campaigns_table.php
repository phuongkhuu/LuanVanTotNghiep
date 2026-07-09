<?php
// database/migrations/2026_07_09_000000_add_promotion_fields_to_campaigns_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Thêm cột để phân biệt loại
            if (!Schema::hasColumn('campaigns', 'type')) {
                $table->string('type')->default('campaign')->after('name');
            }
            
            // Các cột cho voucher
            if (!Schema::hasColumn('campaigns', 'code')) {
                $table->string('code')->nullable()->unique()->after('type');
            }
            if (!Schema::hasColumn('campaigns', 'target_type')) {
                $table->enum('target_type', ['retail', 'wholesale', 'preorder', 'all'])->default('all')->after('code');
            }
            if (!Schema::hasColumn('campaigns', 'discount_type')) {
                $table->enum('discount_type', ['fixed', 'percent', 'freeship'])->nullable()->after('target_type');
            }
            if (!Schema::hasColumn('campaigns', 'discount_value')) {
                $table->decimal('discount_value', 15, 0)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('campaigns', 'min_order')) {
                $table->decimal('min_order', 15, 0)->default(0)->after('discount_value');
            }
            if (!Schema::hasColumn('campaigns', 'limit')) {
                $table->integer('limit')->default(100)->after('min_order');
            }
            if (!Schema::hasColumn('campaigns', 'used')) {
                $table->integer('used')->default(0)->after('limit');
            }
            if (!Schema::hasColumn('campaigns', 'expiry')) {
                $table->date('expiry')->nullable()->after('used');
            }
            
            // Các cột cho pre-order
            if (!Schema::hasColumn('campaigns', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->after('expiry');
            }
            if (!Schema::hasColumn('campaigns', 'tiers')) {
                $table->json('tiers')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('campaigns', 'current_buyers')) {
                $table->integer('current_buyers')->default(0)->after('tiers');
            }
            
            // Các cột khác
            if (!Schema::hasColumn('campaigns', 'banner')) {
                $table->string('banner')->nullable()->after('featured');
            }
            if (!Schema::hasColumn('campaigns', 'description')) {
                $table->text('description')->nullable()->after('banner');
            }
            if (!Schema::hasColumn('campaigns', 'priority')) {
                $table->integer('priority')->default(0)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $columns = ['code', 'target_type', 'discount_type', 'discount_value', 'min_order', 
                        'limit', 'used', 'expiry', 'product_id', 'tiers', 'current_buyers', 
                        'banner', 'description', 'priority'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('campaigns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
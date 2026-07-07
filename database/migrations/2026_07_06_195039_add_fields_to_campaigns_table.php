<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Thêm các cột nếu chưa có
            if (!Schema::hasColumn('campaigns', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('campaigns', 'type')) {
                $table->string('type')->default('seasonal')->after('name');
            }
            if (!Schema::hasColumn('campaigns', 'description')) {
                $table->text('description')->nullable()->after('type');
            }
            if (!Schema::hasColumn('campaigns', 'status')) {
                $table->enum('status', ['scheduled', 'active', 'ended'])->default('scheduled')->after('end_time');
            }
            if (!Schema::hasColumn('campaigns', 'banner')) {
                $table->string('banner')->nullable()->after('status');
            }
            if (!Schema::hasColumn('campaigns', 'priority')) {
                $table->integer('priority')->default(0)->after('banner');
            }
            if (!Schema::hasColumn('campaigns', 'featured')) {
                $table->boolean('featured')->default(false)->after('priority');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $columns = ['name', 'type', 'description', 'status', 'banner', 'priority', 'featured'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('campaigns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
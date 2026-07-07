<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Thêm campaign_id vào bảng banners
        Schema::table('banners', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable()->after('id')->constrained('campaigns')->onDelete('set null');
            $table->index('campaign_id');
        });

        // Xóa banner_id khỏi bảng campaigns (nếu tồn tại)
        if (Schema::hasColumn('campaigns', 'banner_id')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->dropForeign(['banner_id']);
                $table->dropColumn('banner_id');
            });
        }
    }

    public function down()
    {
        // Khôi phục
        Schema::table('banners', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropIndex(['campaign_id']);
            $table->dropColumn('campaign_id');
        });

        if (!Schema::hasColumn('campaigns', 'banner_id')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->foreignId('banner_id')->nullable()->constrained('banners')->onDelete('set null');
            });
        }
    }
};
<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up(): void
//     {
//         Schema::table('banners', function (Blueprint $table) {
//              $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('set null');
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::table('banners', function (Blueprint $table) {
//             $table->dropColumn('campaign_id');
//         });
//     }
// };



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('banners', function (Blueprint $table) {
            // Kiểm tra bảng banners có tồn tại không
            if (Schema::hasTable('banners')) {
                // Kiểm tra cột campaign_id đã tồn tại chưa
                if (!Schema::hasColumn('banners', 'campaign_id')) {
                    $table->unsignedBigInteger('campaign_id')->nullable()->after('id');
                    $table->foreign('campaign_id')
                          ->references('id')
                          ->on('campaigns')
                          ->onDelete('set null');
                }
            }
        });
    }

    public function down()
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasTable('banners')) {
                if (Schema::hasColumn('banners', 'campaign_id')) {
                    $table->dropForeign(['campaign_id']);
                    $table->dropColumn('campaign_id');
                }
            }
        });
    }
};
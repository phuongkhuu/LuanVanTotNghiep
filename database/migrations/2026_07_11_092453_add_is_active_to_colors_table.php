<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('colors', function (Blueprint $table) {
            if (!Schema::hasColumn('colors', 'is_active')) {
                $table->boolean('is_active')->default(1)->after('code');
            }
        });
    }

    public function down()
    {
        Schema::table('colors', function (Blueprint $table) {
            if (Schema::hasColumn('colors', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
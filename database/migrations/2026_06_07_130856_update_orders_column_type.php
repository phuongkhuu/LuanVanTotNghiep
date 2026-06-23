<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('order_code', 'type');
        });


        DB::table('orders')->where('type', 'normal')->update(['type' => 'retail']);


        DB::statement("ALTER TABLE orders MODIFY type ENUM('retail', 'wholesale', 'preorder') NOT NULL DEFAULT 'retail'");
    }

    public function down()
    {

        DB::table('orders')->where('type', 'preorder')->update(['type' => 'retail']);


        DB::statement("ALTER TABLE orders MODIFY type ENUM('wholesale', 'normal') NOT NULL DEFAULT 'normal'");


        DB::table('orders')->where('type', 'retail')->update(['type' => 'normal']);


        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('type', 'order_code');
        });
    }
};
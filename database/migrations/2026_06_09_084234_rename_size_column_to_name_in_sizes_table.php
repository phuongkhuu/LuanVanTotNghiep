<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sizes', function (Blueprint $table) {
            $table->renameColumn('size', 'name');
        });
    }

    public function down()
    {
        Schema::table('sizes', function (Blueprint $table) {
            $table->renameColumn('name', 'size');
        });
    }
};
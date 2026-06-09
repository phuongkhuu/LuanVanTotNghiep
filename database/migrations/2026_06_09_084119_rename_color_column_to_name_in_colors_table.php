<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('colors', function (Blueprint $table) {
            $table->renameColumn('color', 'name');
        });
    }

    public function down()
    {
        Schema::table('colors', function (Blueprint $table) {
            $table->renameColumn('name', 'color');
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('role', ['admin', 'user'])->default('user')->after('avatar');
            $table->tinyInteger('status')->default(1)->after('role'); // 1: active, 0: inactive
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'avatar', 'role', 'status']);
            $table->dropSoftDeletes();
        });
    }
};
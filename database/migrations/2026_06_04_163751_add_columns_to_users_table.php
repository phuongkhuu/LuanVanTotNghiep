<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->after('id')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('role', ['admin', 'staff', 'user'])->default('user');
            $table->tinyInteger('status')->default(1);
            $table->softDeletes(); // thêm deleted_at
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'phone', 'avatar', 'role', 'status', 'deleted_at']);
        });
    }
};
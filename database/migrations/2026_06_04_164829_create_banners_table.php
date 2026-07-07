<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('order')->default(0); // Đã có cột order ở đây
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
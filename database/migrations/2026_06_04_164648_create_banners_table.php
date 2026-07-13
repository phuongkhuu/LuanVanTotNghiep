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
            $table->integer('status')->default(1); // Mặc định là Hoạt động
            $table->integer('order')->default(0);
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null'); // Đã có ở đây
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type')->default('seasonal');
            $table->text('description')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('status')->default('scheduled');
            $table->foreignId('banner_id')->nullable()->constrained('banners')->onDelete('set null');
            $table->integer('priority')->default(0);
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
};
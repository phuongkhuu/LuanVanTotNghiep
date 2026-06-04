<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('campaign_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('discount_percent');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_configs');
    }
};
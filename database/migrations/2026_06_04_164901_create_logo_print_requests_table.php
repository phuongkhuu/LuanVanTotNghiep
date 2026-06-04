<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logo_print_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_detail_id')->constrained()->onDelete('cascade');
            $table->string('logo_image');
            $table->string('print_position')->nullable();
            $table->string('print_size')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('logo_print_requests');
    }
};
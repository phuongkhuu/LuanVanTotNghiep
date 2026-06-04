<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('discount_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('order_code', ['wholesale', 'normal'])->default('normal');
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->decimal('shipping_fee', 12, 0)->default(0);
            $table->decimal('total_amount', 12, 0);
            $table->decimal('discount_amount', 12, 0)->default(0);
            $table->decimal('final_amount', 12, 0);
            $table->tinyInteger('order_status')->default(0);
            $table->text('shipping_address');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
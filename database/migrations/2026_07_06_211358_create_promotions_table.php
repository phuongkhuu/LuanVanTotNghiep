<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->enum('type', ['voucher', 'preorder_tier']);
        $table->enum('target_type', ['retail', 'wholesale', 'preorder', 'all'])->default('all');
        $table->enum('discount_type', ['fixed', 'percent', 'freeship'])->nullable();
        $table->decimal('discount_value', 15, 0)->default(0);
        $table->decimal('min_order', 15, 0)->default(0);
        $table->integer('limit')->default(100);
        $table->integer('used')->default(0);
        $table->date('expiry')->nullable();
        $table->boolean('active')->default(true);
        $table->text('description')->nullable();
        $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
        $table->string('banner')->nullable(); // THÊM
        $table->foreignId('banner_id')->nullable()->constrained('banners')->nullOnDelete(); // THÊM
        $table->json('tiers')->nullable();
        $table->integer('current_buyers')->default(0);
        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();
        $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
        $table->timestamps();
    });
    }

    public function down()
    {
        Schema::dropIfExists('promotions');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('company_name');
            $table->string('email');
            $table->string('phone');
            $table->integer('total_quantity');
            $table->decimal('total', 12, 0)->nullable();
            $table->text('requirement')->nullable();
            $table->string('logo_file')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quote_requests');
    }
};
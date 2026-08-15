<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('confirmation_token')->nullable()->unique()->after('order_number');
            $table->timestamp('token_expires_at')->nullable()->after('confirmation_token');
            $table->boolean('is_confirmed')->default(false)->after('token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['confirmation_token', 'token_expires_at', 'is_confirmed']);
        });
    }
};
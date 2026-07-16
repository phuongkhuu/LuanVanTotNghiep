<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->integer('min_quantity');
            $table->decimal('discount_percent', 5, 2);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down() 
    {
         Schema::dropIfExists('discounts');
    }
};
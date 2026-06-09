<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name')->nullable();
        });

        // Populate slug for existing brands
        $brands = \App\Models\Brand::all();
        foreach ($brands as $brand) {
            $brand->slug = Str::slug($brand->name);
            $brand->save();
        }

        // Now make slug not nullable
        Schema::table('brands', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
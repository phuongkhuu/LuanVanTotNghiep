<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuoteRequestDetailSeeder extends Seeder
{
    public function run()
    {
        $quoteRequestId = DB::table('quote_requests')->value('id');
        $productVariants = DB::table('product_variants')->get();

        if (!$quoteRequestId || $productVariants->isEmpty()) {
            return;
        }

        foreach ($productVariants as $variant) {
            DB::table('quote_request_details')->insert([
                'quote_request_id' => $quoteRequestId,
                'product_variant_id' => $variant->id,
                'quantity' => rand(10, 50),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
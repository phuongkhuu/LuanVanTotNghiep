<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatbotMessageSeeder extends Seeder
{
    public function run()
    {
        $userId = DB::table('users')->where('role', 'user')->value('id') ?? 1;

        DB::table('chatbot_messages')->insert([
            [
                'user_id' => $userId,
                'message' => 'Balo này có chống nước không?',
                'sender' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'message' => 'Có, sản phẩm được làm từ vải Ballistic Nylon chống nước tuyệt đối.',
                'sender' => 'bot',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChatbotMessageController extends Controller
{
    public function chat(Request $request, ChatbotService $service)
    {
        $userMessage = $request->input('message');
        if (empty($userMessage)) {
            return response()->json(['reply' => 'Vui lòng nhập câu hỏi.'], 400);
        }

        // Lấy ID người dùng (có thể null nếu chưa đăng nhập)
        $userId = Auth::id();

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('GEMINI_API_KEY chưa được cấu hình trong .env');
            return response()->json(['reply' => 'Lỗi cấu hình hệ thống.'], 500);
        }

        $model = env('GEMINI_MODEL', 'gemini-1.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $tools = $service->getTools();
        $geminiTools = $this->convertToGeminiTools($tools);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $userMessage]
                    ]
                ]
            ],
            'tools' => $geminiTools,
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 4096,
            ]
        ];

        try {
            $response = Http::timeout(30)->post($url, $payload);

            if (!$response->successful()) {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json([
                    'reply' => 'Xin lỗi, hệ thống AI đang gặp sự cố. Vui lòng thử lại sau.'
                ], 500);
            }

            $data = $response->json();
            $candidate = $data['candidates'][0] ?? null;
            if (!$candidate) {
                return response()->json(['reply' => 'Không nhận được phản hồi từ AI.'], 500);
            }

            $content = $candidate['content']['parts'][0] ?? null;
            if (!$content) {
                return response()->json(['reply' => 'Không có nội dung phản hồi.'], 500);
            }

            if (isset($content['functionCall'])) {
                $functionCall = $content['functionCall'];
                $functionName = $functionCall['name'];
                $arguments = is_array($functionCall['args']) ? $functionCall['args'] : [];
                
                Log::info("Tool called: {$functionName}", $arguments);

                $result = $service->executeTool($functionName, $arguments);
                $reply = $this->sendToolResult($apiKey, $model, $userMessage, $functionName, $result);
            } else {
                $reply = $content['text'] ?? 'Xin lỗi, tôi chưa hiểu câu hỏi.';
            }

            // Lưu lịch sử chat vào database
            $this->saveChatHistory($userId, $userMessage, $reply);

            return response()->json(['reply' => $reply]);

        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage());
            return response()->json([
                'reply' => 'Đã có lỗi xảy ra. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    /**
     * Chuyển đổi tools từ định dạng Laravel AI sang định dạng Gemini
     */
    private function convertToGeminiTools(array $tools): array
    {
        $geminiTools = [];
        foreach ($tools as $tool) {
            if (isset($tool['function'])) {
                $func = $tool['function'];
                
                $parameters = [
                    'type' => 'object',
                    'properties' => new \stdClass(),
                ];
                
                if (isset($func['parameters']['properties']) && is_array($func['parameters']['properties'])) {
                    $props = $func['parameters']['properties'];
                    if (array_keys($props) !== range(0, count($props) - 1)) {
                        $parameters['properties'] = $props;
                    }
                }
                
                if (isset($func['parameters']['required'])) {
                    $parameters['required'] = $func['parameters']['required'];
                }
                
                $geminiTools[] = [
                    'functionDeclarations' => [
                        [
                            'name' => $func['name'],
                            'description' => $func['description'],
                            'parameters' => $parameters,
                        ]
                    ]
                ];
            }
        }
        return $geminiTools;
    }

    /**
     * Chuẩn bị dữ liệu kết quả tool để gửi cho Gemini
     */
    private function prepareToolData(string $functionName, array $result): string
    {
        if (isset($result['message'])) {
            return $result['message'];
        }

        if (empty($result)) {
            return 'Không tìm thấy dữ liệu.';
        }

        switch ($functionName) {
            case 'get_products_by_filters':
                $limited = array_slice($result, 0, 5);
                return json_encode($limited, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            case 'get_active_campaigns':
                $count = count($result);
                $summaries = array_map(function($item) {
                    $discount = $item['discount_value'] ?? 0;
                    $type = $item['discount_type'] === 'percent' ? '%' : ' VND';
                    return "{$item['name']} (giảm {$discount}{$type})";
                }, array_slice($result, 0, 3));
                $extra = $count > 3 ? " và " . ($count - 3) . " chương trình khác" : "";
                return "Có {$count} chương trình khuyến mãi: " . implode(', ', $summaries) . $extra;

            case 'get_vouchers':
                $count = count($result);
                $summaries = array_map(function($item) {
                    return "Mã {$item['code']} (giảm {$item['discount_text']})";
                }, array_slice($result, 0, 3));
                $extra = $count > 3 ? " và " . ($count - 3) . " voucher khác" : "";
                return "Có {$count} voucher: " . implode(', ', $summaries) . $extra;

            case 'get_preorder_info':
                $count = count($result);
                $summaries = array_map(function($item) {
                    return "{$item['product_name']} (giảm {$item['current_discount']})";
                }, array_slice($result, 0, 3));
                $extra = $count > 3 ? " và " . ($count - 3) . " sản phẩm khác" : "";
                return "Có {$count} sản phẩm preorder: " . implode(', ', $summaries) . $extra;

            case 'get_order_status':
                if (isset($result['error'])) {
                    return $result['error'];
                }
                return "Đơn hàng #{$result['order_id']} - Trạng thái: {$result['status']} - Tổng tiền: {$result['total_amount']}";

            case 'get_product_by_slug':
                if (isset($result['error'])) {
                    return $result['error'];
                }
                return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            default:
                return json_encode(array_slice($result, 0, 5), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    /**
     * Gửi kết quả tool trở lại Gemini để tổng hợp câu trả lời
     */
    private function sendToolResult(string $apiKey, string $model, string $userMessage, string $functionName, array $result): string
    {
        $data = $this->prepareToolData($functionName, $result);

        $instruction = '';
        if ($functionName === 'get_products_by_filters' || $functionName === 'get_product_by_slug') {
            $instruction = " Hãy trình bày thông tin sản phẩm một cách trực quan. Với mỗi sản phẩm, hiển thị ảnh (dùng thẻ <img src='...' alt='tên sản phẩm' style='max-width:120px; height:auto; border-radius:8px;'>) và các thông tin: tên, thương hiệu, giá, khuyến mãi (nếu có).";
        } elseif ($functionName === 'get_vouchers') {
            $instruction = " Hãy liệt kê các voucher kèm mã, mức giảm, điều kiện và hạn sử dụng.";
        } elseif ($functionName === 'get_preorder_info') {
            $instruction = " Hãy giải thích chương trình preorder, hiển thị mức giảm hiện tại và các mức giảm tiếp theo.";
        } elseif ($functionName === 'get_active_campaigns') {
            $instruction = " Hãy mô tả các chương trình khuyến mãi, bao gồm giảm giá và điều kiện áp dụng.";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $userMessage],
                        ['text' => "Kết quả truy vấn từ tool {$functionName}: " . $data . $instruction]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 4096,
            ]
        ];

        try {
            $response = Http::timeout(30)->post($url, $payload);
            $data = $response->json();

            if (!$response->successful()) {
                Log::error('Gemini API Error (tool result): ' . $response->body());
                return 'Xin lỗi, không thể tổng hợp kết quả. Vui lòng thử lại sau.';
            }

            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Không thể tổng hợp kết quả.';
        } catch (\Exception $e) {
            Log::error('sendToolResult Error: ' . $e->getMessage());
            return 'Đã có lỗi xảy ra khi tổng hợp kết quả.';
        }
    }

    /**
     * Lưu lịch sử chat vào database
     */
    private function saveChatHistory($userId, string $userMessage, string $botReply)
    {
        try {
            // Lưu tin nhắn của người dùng
            \App\Models\ChatbotMessage::create([
                'user_id' => $userId,
                'message' => $userMessage,
                'sender' => 'user',
            ]);

            // Lưu tin nhắn của bot
            \App\Models\ChatbotMessage::create([
                'user_id' => $userId,
                'message' => $botReply,
                'sender' => 'bot',
            ]);

            Log::info('Lưu lịch sử chat thành công cho user_id: ' . ($userId ?? 'guest'));
        } catch (\Exception $e) {
            // Không làm gián đoạn luồng chính, chỉ log lỗi
            Log::error('Không thể lưu lịch sử chat: ' . $e->getMessage());
        }
    }
}
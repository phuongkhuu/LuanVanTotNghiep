<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChatbotMessageController extends Controller
{
    /**
     * Danh sách từ khóa liên quan đến nội dung chatbot được phép
     */
    private const ALLOWED_KEYWORDS = [
        'balo', 'túi', 'ba lô', 'sản phẩm', 'hàng', 'mua', 'bán', 'giá', 'còn hàng',
        'khuyến mãi', 'voucher', 'mã giảm', 'preorder', 'đặt trước', 'đặt hàng',
        'đơn hàng', 'tra cứu', 'mã đơn', 'hóa đơn', 'giao hàng', 'vận chuyển',
        'bigbag', 'samsonite', 'solo', 'kingbag', 'everki', 'targus',
        'laptop', 'du lịch', 'phượt', 'thời trang', 'chống sốc',
        'chất liệu', 'nylon', 'polyester', 'vải', 'da',
        'màu', 'size', 'cỡ', 'mẫu', 'thiết kế', 'thương hiệu',
        'ưu đãi', 'giảm giá', 'khuyến mãi', 'sale', 'promo',
        'in stock', 'có hàng', 'hết hàng', 'tồn kho',
    ];

    public function chat(Request $request, ChatbotService $service)
    {
        $userMessage = $request->input('message');
        if (empty($userMessage)) {
            return response()->json(['reply' => 'Vui lòng nhập câu hỏi.'], 400);
        }

        // ===== BỘ LỌC TỪ KHÓA =====
        if (!$this->isRelevantQuery($userMessage)) {
            return response()->json([
                'reply' => 'Xin lỗi, tôi chỉ hỗ trợ các câu hỏi về sản phẩm, khuyến mãi, voucher, preorder và tra cứu đơn hàng của cửa hàng. Bạn vui lòng đặt câu hỏi về các nội dung đó nhé.'
            ]);
        }

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

            $this->saveChatHistory($userId, $userMessage, $reply);

            return response()->json(['reply' => $reply]);

        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage());
            return response()->json([
                'reply' => 'Đã có lỗi xảy ra. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    // ==================== HÀM KIỂM TRA TỪ KHÓA ====================

    private function isRelevantQuery(string $message): bool
    {
        $messageLower = mb_strtolower($message, 'UTF-8');

        $greetings = ['chào', 'xin chào', 'hello', 'hi', 'chúc', 'cảm ơn', 'thank'];
        if (preg_match('/^(' . implode('|', $greetings) . ')\s*$/u', $messageLower)) {
            return false;
        }

        foreach (self::ALLOWED_KEYWORDS as $keyword) {
            if (mb_strpos($messageLower, $keyword) !== false) {
                return true;
            }
        }

        if (preg_match('/\d+/', $message)) {
            return true;
        }

        return false;
    }

    // ==================== CÁC PHƯƠNG THỨC XỬ LÝ TOOL ====================

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

    private function sendToolResult(string $apiKey, string $model, string $userMessage, string $functionName, array $result): string
    {
        $data = $this->prepareToolData($functionName, $result);

        $instruction = '';
        if ($functionName === 'get_products_by_filters' || $functionName === 'get_product_by_slug') {
            // Giữ nguyên yêu cầu chèn ảnh, bổ sung hướng dẫn viết tự nhiên, mềm mại
            $instruction = " Hãy trình bày thông tin sản phẩm một cách trực quan và tự nhiên, như đang trò chuyện thân mật với khách hàng. Với mỗi sản phẩm, hãy chèn thẻ <img> để hiển thị ảnh (src từ trường thumbnail, alt là tên sản phẩm, style='max-width:120px; height:auto; border-radius:8px;'). Sau đó mô tả ngắn gọn: tên, thương hiệu, giá, khuyến mãi (nếu có) và đặc điểm nổi bật. Trình bày thành một đoạn văn hoặc các câu liền mạch, không dùng dấu đầu dòng hay định dạng đặc biệt.";
        } elseif ($functionName === 'get_vouchers') {
            $instruction = " Hãy liệt kê các voucher bằng văn bản tự nhiên, mỗi voucher nêu mã, mức giảm, điều kiện và hạn sử dụng.";
        } elseif ($functionName === 'get_preorder_info') {
            $instruction = " Hãy giải thích chương trình preorder bằng văn bản tự nhiên, nêu rõ sản phẩm, mức giảm hiện tại và các mức giảm tiếp theo.";
        } elseif ($functionName === 'get_active_campaigns') {
            $instruction = " Hãy mô tả các chương trình khuyến mãi bằng văn bản tự nhiên, bao gồm giảm giá và điều kiện áp dụng.";
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

    private function saveChatHistory($userId, string $userMessage, string $botReply)
    {
        try {
            \App\Models\ChatbotMessage::create([
                'user_id' => $userId,
                'message' => $userMessage,
                'sender' => 'user',
            ]);

            \App\Models\ChatbotMessage::create([
                'user_id' => $userId,
                'message' => $botReply,
                'sender' => 'bot',
            ]);

            Log::info('Lưu lịch sử chat thành công cho user_id: ' . ($userId ?? 'guest'));
        } catch (\Exception $e) {
            Log::error('Không thể lưu lịch sử chat: ' . $e->getMessage());
        }
    }
}
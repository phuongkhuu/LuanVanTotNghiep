<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MultiSheetOrdersExport implements WithMultipleSheets
{
    protected $type;
    
    public function __construct($type = 'retail')
    {
        $this->type = $type;
    }
    
    public function sheets(): array
    {
        $sheets = [];
        $statuses = $this->getStatuses();
        
        foreach ($statuses as $status => $label) {
            $orders = Order::with(['details.productVariant.product', 'payment'])
                ->where('order_code', $this->type)
                ->where('order_status', $status)
                ->latest()
                ->get();
                
            if ($orders->isNotEmpty()) {
                $sheets[] = new OrdersSheet($orders, $label);
            }
        }
        
        // Thêm sheet tổng hợp tất cả
        $allOrders = Order::with(['details.productVariant.product', 'payment'])
            ->where('order_code', $this->type)
            ->latest()
            ->get();
            
        $sheets[] = new OrdersSheet($allOrders, 'Tất cả đơn hàng');
        
        return $sheets;
    }
    
    protected function getStatuses()
    {
        $maps = [
            'retail' => [
                0 => 'Chờ xử lý',
                1 => 'Đang xử lý',
                2 => 'Đang giao',
                3 => 'Hoàn thành',
                4 => 'Đã hủy',
            ],
            'wholesale' => [
                0 => 'Chờ xác nhận',
                1 => 'Đã duyệt',
                2 => 'Đang sản xuất',
                3 => 'Đang giao',
                4 => 'Hoàn thành',
                5 => 'Đã hủy',
            ],
            'preorder' => [
                0 => 'Chờ xác nhận',
                1 => 'Đã xác nhận',
                2 => 'Chờ hàng',
                3 => 'Đang giao',
                4 => 'Hoàn thành',
                5 => 'Đã hủy',
            ],
        ];
        
        return $maps[$this->type] ?? $maps['retail'];
    }
}
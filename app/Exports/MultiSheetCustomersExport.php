<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MultiSheetCustomersExport implements WithMultipleSheets
{
    protected $type;
    protected $customers;
    
    public function __construct($type = 'all', $customers = null)
    {
        $this->type = $type;
        $this->customers = $customers;
    }
    
    public function sheets(): array
    {
        $sheets = [];
        
        if ($this->customers && count($this->customers) > 0) {
            // Tạo 1 sheet duy nhất với tên phù hợp
            $typeLabels = [
                'retail' => 'Khách lẻ',
                'wholesale' => 'Khách doanh nghiệp',
                'preorder' => 'Pre-order',
                'all' => 'Tất cả khách hàng'
            ];
            
            $label = $typeLabels[$this->type] ?? 'Khách hàng';
            $sheets[] = new CustomerExportSheet(collect($this->customers), $label);
            
            return $sheets;
        }
        
        // Fallback: nếu không có dữ liệu truyền vào
        $orderCodes = $this->getOrderCodes();
        $customersData = $this->getCustomersData($orderCodes);
        
        if ($customersData->isNotEmpty()) {
            $typeLabels = [
                'retail' => 'Khách lẻ',
                'wholesale' => 'Khách doanh nghiệp',
                'preorder' => 'Pre-order',
                'all' => 'Tất cả khách hàng'
            ];
            $label = $typeLabels[$this->type] ?? 'Khách hàng';
            $sheets[] = new CustomerExportSheet($customersData, $label);
        }
        
        return $sheets;
    }
    
    protected function getOrderCodes()
    {
        return match ($this->type) {
            'retail' => ['retail'],
            'wholesale' => ['wholesale'],
            'preorder' => ['preorder'],
            'all' => ['retail', 'wholesale', 'preorder'],
            default => ['retail', 'wholesale', 'preorder'],
        };
    }
    
    protected function getCustomersData($orderCodes)
    {
        $customers = Order::select(
            'customer_phone',
            DB::raw('MAX(customer_name) as name'),
            DB::raw('MAX(shipping_address) as address'),
            DB::raw('MAX(created_at) as last_order_date'),
            DB::raw('COUNT(*) as orders_count'),
            DB::raw('SUM(
                COALESCE((SELECT SUM(subtotal) FROM order_details WHERE order_details.order_id = orders.id), 0)
                + COALESCE(shipping_fee, 0)
                - COALESCE(discount_amount, 0)
            ) as total_spent'),
            DB::raw('MIN(created_at) as join_date')
        )
            ->whereNotNull('customer_phone')
            ->whereIn('order_code', $orderCodes)
            ->groupBy('customer_phone')
            ->orderByDesc('total_spent')
            ->get();
        
        return $customers->map(function ($item) {
            $orderTypes = Order::where('customer_phone', $item->customer_phone)
                ->distinct('order_code')
                ->pluck('order_code')
                ->toArray();
            
            $type = 'retail';
            if (in_array('preorder', $orderTypes)) {
                $type = 'preorder';
            } elseif (in_array('wholesale', $orderTypes)) {
                $type = 'wholesale';
            }
            
            return [
                'phone' => $item->customer_phone ?? '',
                'name' => $item->name ?? 'Khách hàng',
                'address' => $item->address ?? '',
                'last_order_date' => $item->last_order_date ? Carbon::parse($item->last_order_date)->format('d/m/Y') : '',
                'orders_count' => (int) ($item->orders_count ?? 0),
                'total_spent' => (float) ($item->total_spent ?? 0),
                'join_date' => $item->join_date ? Carbon::parse($item->join_date)->format('d/m/Y') : '',
                'type' => $type,
            ];
        });
    }
}
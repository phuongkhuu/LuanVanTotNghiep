<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $type;
    protected $orders;

    public function __construct($type = 'retail', $orders = null)
    {
        $this->type = $type;
        $this->orders = $orders;
    }

    public function collection()
    {
        if ($this->orders) {
            return $this->orders;
        }

        $query = Order::with(['details.productVariant.product', 'payment']);
        
        // Nếu type là 'all', lấy tất cả, không filter
        if ($this->type !== 'all') {
            $query->where('order_code', $this->type);
        }
        
        return $query->latest()
            ->get()
            ->map(function ($order) {
                return $this->formatOrder($order);
            });
    }

    protected function formatOrder($order)
    {
        $products = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            return [
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'quantity' => $detail->quantity,
                'price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        }

        // Lấy danh sách sản phẩm dạng text
        $productList = $products->map(function ($item) {
            return $item['name'] . ' x' . $item['quantity'] . ' = ' . number_format($item['subtotal']) . 'đ';
        })->implode('; ');

        return (object) [
            'id' => $order->id,
            'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'type' => $order->order_code ?? 'retail',
            'customer_name' => $order->customer_name ?? $order->receiver_name,
            'customer_phone' => $order->customer_phone ?? $order->receiver_phone,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'created_date' => $order->created_at->format('d/m/Y H:i'),
            'products' => $productList,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping,
            'discount_amount' => $discount,
            'final_amount' => $final,
            'payment_method' => $payment,
            'status' => $order->getStatusLabel(),
            'note' => $order->note ?? '',
        ];
    }

    public function headings(): array
    {
        $typeLabels = [
            'retail' => 'Bán lẻ',
            'wholesale' => 'Bán sỉ',
            'preorder' => 'Pre-order',
            'all' => 'TẤT CẢ'
        ];

        $typeLabel = $typeLabels[$this->type] ?? 'Đơn hàng';

        return [
            ['DANH SÁCH ĐƠN HÀNG ' . strtoupper($typeLabel)],
            [''],
            [
                'STT',
                'Mã đơn hàng',
                'Loại',
                'Người đặt',
                'SĐT đặt',
                'Người nhận',
                'SĐT nhận',
                'Địa chỉ giao',
                'Ngày đặt',
                'Sản phẩm',
                'Tạm tính',
                'Phí ship',
                'Giảm giá',
                'Tổng cộng',
                'Hình thức TT',
                'Trạng thái',
                'Ghi chú'
            ]
        ];
    }

    public function map($order): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $order->code,
            $this->getTypeLabel($order->type),
            $order->customer_name,
            $order->customer_phone,
            $order->receiver_name,
            $order->receiver_phone,
            $order->shipping_address,
            $order->created_date,
            $order->products,
            number_format($order->subtotal) . 'đ',
            number_format($order->shipping_fee) . 'đ',
            number_format($order->discount_amount) . 'đ',
            number_format($order->final_amount) . 'đ',
            $order->payment_method,
            $order->status,
            $order->note,
        ];
    }

    protected function getTypeLabel($type)
    {
        $labels = [
            'retail' => 'Bán lẻ',
            'wholesale' => 'Bán sỉ',
            'preorder' => 'Pre-order'
        ];
        return $labels[$type] ?? $type;
    }

    public function styles(Worksheet $sheet)
    {
        // Merge title row
        $sheet->mergeCells('A1:Q1');
        
        // Style title
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF6B35'], // Orange
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Style header row
        $sheet->getStyle('A3:Q3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'], // Dark blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '34495E'],
                ],
            ],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Style data rows
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A4:Q' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'BDC3C7'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Zebra striping
        for ($row = 4; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ]);
            }
        }

        // Style status column
        $statusColumn = 'P';
        $statusColors = [
            'Chờ xử lý' => 'F39C12',
            'Đang xử lý' => '3498DB',
            'Đang giao' => '9B59B6',
            'Hoàn thành' => '27AE60',
            'Đã hủy' => 'E74C3C',
            'Chờ xác nhận' => 'F39C12',
            'Đã duyệt' => '27AE60',
            'Đang sản xuất' => 'E67E22',
            'Đã xác nhận' => '3498DB',
            'Chờ hàng' => 'F1C40F',
        ];

        for ($row = 4; $row <= $lastRow; $row++) {
            $status = $sheet->getCell($statusColumn . $row)->getValue();
            if (isset($statusColors[$status])) {
                $sheet->getStyle($statusColumn . $row)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => $statusColors[$status]],
                        'bold' => true,
                    ],
                ]);
            }
        }

        // Style total column
        $totalColumn = 'N';
        for ($row = 4; $row <= $lastRow; $row++) {
            $sheet->getStyle($totalColumn . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FF6B35'],
                ],
            ]);
        }

        // Style "Loại" column - in đậm
        $typeColumn = 'C';
        for ($row = 4; $row <= $lastRow; $row++) {
            $sheet->getStyle($typeColumn . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '2C3E50'],
                ],
            ]);
        }

        // Wrap text for Products and Address columns
        $sheet->getStyle('J4:J' . $lastRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('H4:H' . $lastRow)->getAlignment()->setWrapText(true);

        // Auto-size columns
        foreach (range('A', 'Q') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A4');

        // Add auto filter
        $sheet->setAutoFilter('A3:Q3');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // STT
            'B' => 15,  // Mã đơn
            'C' => 12,  // Loại
            'D' => 20,  // Người đặt
            'E' => 15,  // SĐT đặt
            'F' => 20,  // Người nhận
            'G' => 15,  // SĐT nhận
            'H' => 35,  // Địa chỉ giao
            'I' => 18,  // Ngày đặt
            'J' => 50,  // Sản phẩm
            'K' => 15,  // Tạm tính
            'L' => 12,  // Phí ship
            'M' => 15,  // Giảm giá
            'N' => 15,  // Tổng cộng
            'O' => 15,  // Hình thức TT
            'P' => 15,  // Trạng thái
            'Q' => 30,  // Ghi chú
        ];
    }
}
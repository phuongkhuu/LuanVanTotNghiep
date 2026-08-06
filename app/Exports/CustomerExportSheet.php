<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CustomerExportSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize, WithTitle
{
    protected $customers;
    protected $title;
    
    public function __construct($customers, $title = 'Khách hàng')
    {
        $this->customers = $customers;
        $this->title = $title;
    }
    
    public function collection()
    {
        return $this->customers;
    }
    
    public function headings(): array
    {
        return [
            'STT',
            'Tên khách hàng',
            'Số điện thoại',
            'Địa chỉ',
            'Số đơn hàng',
            'Tổng chi tiêu',
            'Ngày tham gia',
            'Lần mua cuối',
        ];
    }
    
    public function map($customer): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        
        return [
            $rowNumber,
            $customer['name'] ?? 'Khách hàng',
            $customer['phone'] ?? '',
            $customer['address'] ?? '',
            $customer['orders_count'] ?? 0,
            number_format($customer['total_spent'] ?? 0) . 'đ',
            $customer['join_date'] ?? '',
            $customer['last_order_date'] ?? '',
        ];
    }
    
    public function title(): string
    {
        return mb_substr($this->title, 0, 31);
    }
    
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
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
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 2) {
            $sheet->getStyle('A2:H' . $lastRow)->applyFromArray([
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
            
            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8F9FA'],
                        ],
                    ]);
                }
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FF6B35'],
                    ],
                ]);
                $sheet->getStyle('E' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '2C3E50'],
                    ],
                ]);
            }
            
            $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C2:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E2:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D2:D' . $lastRow)->getAlignment()->setWrapText(true);
        }
        
        $sheet->setAutoFilter('A1:H1');
        $sheet->freezePane('A2');
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 25,
            'C' => 15,
            'D' => 35,
            'E' => 12,
            'F' => 18,
            'G' => 15,
            'H' => 15,
        ];
    }
}
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class ReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $period;
    protected $reportData;

    public function __construct($period, $reportData)
    {
        $this->period = $period;
        $this->reportData = $reportData;
    }

    /**
     * Trả về tên file xuất theo định dạng ngày/tháng/năm
     */
    public function getFileName(): string
    {
        $now = Carbon::now();
        return 'bao_cao_' . $now->format('Ymd') . '.xlsx';
    }

    public function title(): string
    {
        return 'Báo cáo thống kê';
    }

    public function headings(): array
    {
        return [
            'Chỉ tiêu',
            'Bán lẻ',
            'Bán sỉ',
            'Pre-order'
        ];
    }

    public function array(): array
    {
        $summary = $this->reportData['summary'];
        $chartData = $this->reportData['chartData'];
        $topProducts = $this->reportData['topProducts'];
        $topCustomers = $this->reportData['topCustomers'];
        $categoryDistribution = $this->reportData['categoryDistribution'];

        $rows = [];

        // 1. Tiêu đề thời gian và ngày xuất
        $periodLabel = $this->getPeriodLabel($this->period);
        $exportDate = Carbon::now()->format('d/m/Y H:i');
        $rows[] = ['BÁO CÁO THỐNG KÊ - ' . strtoupper($periodLabel)];
        $rows[] = ['Ngày xuất: ' . $exportDate];
        $rows[] = []; // dòng trống

        // 2. Tổng quan doanh thu
        $rows[] = ['I. TỔNG QUAN DOANH THU'];
        $rows[] = ['Doanh thu hôm nay',
            number_format($summary['retail']['revenue']) . '₫',
            number_format($summary['wholesale']['revenue']) . '₫',
            number_format($summary['preorder']['revenue']) . '₫'
        ];
        $rows[] = ['Tăng trưởng so với hôm qua',
            $summary['retail']['growth'] . '%',
            $summary['wholesale']['growth'] . '%',
            $summary['preorder']['growth'] . '%'
        ];
        $rows[] = []; // dòng trống

        // 3. Biểu đồ doanh thu theo ngày/tuần/tháng
        $rows[] = ['II. DOANH THU THEO THỜI GIAN'];
        $labels = $chartData['labels'];
        $retail = $chartData['retail'];
        $wholesale = $chartData['wholesale'];
        $preorder = $chartData['preorder'];

        // Header cho biểu đồ
        $header = ['Kỳ'];
        foreach ($labels as $label) {
            $header[] = $label;
        }
        $rows[] = $header;

        // Dòng dữ liệu: Bán lẻ
        $rowRetail = ['Bán lẻ'];
        foreach ($retail as $value) {
            $rowRetail[] = number_format($value) . '₫';
        }
        $rows[] = $rowRetail;

        // Bán sỉ
        $rowWholesale = ['Bán sỉ'];
        foreach ($wholesale as $value) {
            $rowWholesale[] = number_format($value) . '₫';
        }
        $rows[] = $rowWholesale;

        // Pre-order
        $rowPreorder = ['Pre-order'];
        foreach ($preorder as $value) {
            $rowPreorder[] = number_format($value) . '₫';
        }
        $rows[] = $rowPreorder;
        $rows[] = []; // dòng trống

        // 4. Top sản phẩm
        $rows[] = ['III. TOP SẢN PHẨM BÁN CHẠY'];
        $rows[] = ['STT', 'Tên sản phẩm', 'Số lượng bán', 'Doanh thu'];
        foreach ($topProducts as $index => $product) {
            $rows[] = [
                $index + 1,
                $product['name'],
                $product['sold'],
                number_format($product['revenue']) . '₫'
            ];
        }
        $rows[] = []; // dòng trống

        // 5. Top khách hàng
        $rows[] = ['IV. TOP KHÁCH HÀNG THÂN THIẾT'];
        $rows[] = ['STT', 'Tên khách hàng', 'Số đơn hàng', 'Tổng chi tiêu'];
        foreach ($topCustomers as $index => $customer) {
            $rows[] = [
                $index + 1,
                $customer['name'],
                $customer['orders'],
                number_format($customer['total']) . '₫'
            ];
        }
        $rows[] = []; // dòng trống

        // 6. Phân bố danh mục
        $rows[] = ['V. PHÂN BỐ DANH MỤC'];
        $rows[] = ['Danh mục', 'Tỷ lệ (%)'];
        foreach ($categoryDistribution as $category) {
            $rows[] = [
                $category['label'] ?? 'Khác',
                $category['value'] ?? 0
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Merge tiêu đề
        $sheet->mergeCells('A1:' . $highestColumn . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF6B35'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Các tiêu đề phần (I, II, III...)
        $sectionRows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellValue = $sheet->getCell('A' . $row->getRowIndex())->getValue();
            if (preg_match('/^[I,V]+\./', $cellValue)) {
                $sectionRows[] = $row->getRowIndex();
            }
        }

        foreach ($sectionRows as $rowIndex) {
            $sheet->getStyle('A' . $rowIndex . ':' . $highestColumn . $rowIndex)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => '2C3E50'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F5F5'],
                ],
            ]);
        }

        // Style header bảng (dòng có STT, Tên sản phẩm...)
        $headerRows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellValue = $sheet->getCell('A' . $row->getRowIndex())->getValue();
            if (in_array($cellValue, ['STT', 'Kỳ', 'Danh mục'])) {
                $headerRows[] = $row->getRowIndex();
            }
        }

        foreach ($headerRows as $rowIndex) {
            $sheet->getStyle('A' . $rowIndex . ':' . $highestColumn . $rowIndex)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '34495E'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Kẻ khung cho tất cả dữ liệu
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'BDC3C7'],
                ],
            ],
        ];

        $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray($styleArray);

        // Tự động căn chỉnh cột
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    private function getPeriodLabel($period)
    {
        $labels = [
            'week' => 'Tuần',
            'month' => 'Tháng',
            'year' => 'Năm'
        ];
        return $labels[$period] ?? $period;
    }
}
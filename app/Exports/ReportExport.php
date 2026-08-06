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
    protected $periodName;

    public function __construct($period, $reportData)
    {
        $this->period = $period;
        $this->reportData = $reportData;
        $this->periodName = $this->getPeriodName($this->period);
        $this->periodName = $this->toUpperCase($this->periodName);
    }

    private function toUpperCase($str)
    {
        $map = [
            'Ngày' => 'NGÀY',
            'Tuần' => 'TUẦN',
            'Tháng' => 'THÁNG',
            'Năm' => 'NĂM'
        ];
        return $map[$str] ?? strtoupper($str);
    }

    public function getFileName(): string
    {
        $now = Carbon::now();
        $periodLabel = $this->getPeriodLabel($this->period);
        return 'bao_cao_' . $periodLabel . '_' . $now->format('Ymd') . '.xlsx';
    }

    public function title(): string
    {
        return 'Báo cáo thống kê';
    }

    public function headings(): array
    {
        return ['BẢNG BÁO CÁO DOANH THU THEO ' . $this->periodName];
    }

    public function array(): array
    {
        $summary = $this->reportData['summary'];
        $chartData = $this->reportData['chartData'];
        $topProducts = $this->reportData['topProducts'];
        $topCustomers = $this->reportData['topCustomers'];
        $categoryDistribution = $this->reportData['categoryDistribution'];

        $labels = $chartData['labels'];
        $numColumns = count($labels) + 1;

        $rows = [];

        // I. TỔNG QUAN DOANH THU
        $rows[] = ['I. TỔNG QUAN DOANH THU'];
        $typeRow = ['Loại doanh thu', 'Bán lẻ', 'Bán sỉ', 'Pre-order'];
        for ($i = 4; $i < $numColumns; $i++) {
            $typeRow[] = '';
        }
        $rows[] = $typeRow;

        $revenueLabel = $this->getRevenueLabel($this->period);
        $revenueRow = [$revenueLabel];
        $revenueRow[] = number_format($summary['retail']['revenue']) . '₫';
        $revenueRow[] = number_format($summary['wholesale']['revenue']) . '₫';
        $revenueRow[] = number_format($summary['preorder']['revenue']) . '₫';
        for ($i = 4; $i < $numColumns; $i++) {
            $revenueRow[] = '';
        }
        $rows[] = $revenueRow;

        $growthLabel = $this->getGrowthLabel($this->period);
        $growthRow = [$growthLabel];
        $growthRow[] = $summary['retail']['growth'] . '%';
        $growthRow[] = $summary['wholesale']['growth'] . '%';
        $growthRow[] = $summary['preorder']['growth'] . '%';
        for ($i = 4; $i < $numColumns; $i++) {
            $growthRow[] = '';
        }
        $rows[] = $growthRow;
        $rows[] = [];

        // II. DOANH THU THEO THỜI GIAN
        $rows[] = ['II. DOANH THU THEO ' . $this->periodName];
        $header = [$this->periodName];
        foreach ($labels as $label) {
            $header[] = $label;
        }
        $rows[] = $header;

        $rowRetail = ['Doanh thu bán lẻ'];
        foreach ($chartData['retail'] as $value) {
            $rowRetail[] = number_format($value) . '₫';
        }
        $rows[] = $rowRetail;

        $rowWholesale = ['Doanh thu bán sỉ'];
        foreach ($chartData['wholesale'] as $value) {
            $rowWholesale[] = number_format($value) . '₫';
        }
        $rows[] = $rowWholesale;

        $rowPreorder = ['Doanh thu pre-order'];
        foreach ($chartData['preorder'] as $value) {
            $rowPreorder[] = number_format($value) . '₫';
        }
        $rows[] = $rowPreorder;
        $rows[] = [];

        // III. TOP SẢN PHẨM BÁN CHẠY
        $rows[] = ['III. TOP SẢN PHẨM BÁN CHẠY'];
        $headerTop = ['STT', 'Tên sản phẩm', 'Số lượng bán', 'Doanh thu'];
        for ($i = 4; $i < $numColumns; $i++) {
            $headerTop[] = '';
        }
        $rows[] = $headerTop;

        foreach ($topProducts as $index => $product) {
            $row = [
                $index + 1,
                $product['name'],
                $product['sold'],
                number_format($product['revenue']) . '₫'
            ];
            for ($i = 4; $i < $numColumns; $i++) {
                $row[] = '';
            }
            $rows[] = $row;
        }
        $rows[] = [];

        // IV. TOP KHÁCH HÀNG THÂN THIẾT
        $rows[] = ['IV. TOP KHÁCH HÀNG THÂN THIẾT'];
        $headerCustomer = ['STT', 'Tên khách hàng', 'Số đơn hàng', 'Tổng doanh thu'];
        for ($i = 4; $i < $numColumns; $i++) {
            $headerCustomer[] = '';
        }
        $rows[] = $headerCustomer;

        foreach ($topCustomers as $index => $customer) {
            $row = [
                $index + 1,
                $customer['name'],
                $customer['orders'],
                number_format($customer['total']) . '₫'
            ];
            for ($i = 4; $i < $numColumns; $i++) {
                $row[] = '';
            }
            $rows[] = $row;
        }
        $rows[] = [];

        // V. PHÂN BỐ DANH MỤC
        $rows[] = ['V. PHÂN BỐ DANH MỤC'];
        $headerCategory = ['Danh mục', 'Tỷ lệ (%)'];
        for ($i = 2; $i < $numColumns; $i++) {
            $headerCategory[] = '';
        }
        $rows[] = $headerCategory;

        foreach ($categoryDistribution as $category) {
            $row = [
                $category['label'] ?? 'Khác',
                $category['value'] ?? 0
            ];
            for ($i = 2; $i < $numColumns; $i++) {
                $row[] = '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Canh giữa tất cả các ô
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Tiêu đề chính (dòng 1)
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
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Merge các tiêu đề phần (I, II, III, IV, V)
        $sectionRows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellValue = $sheet->getCell('A' . $row->getRowIndex())->getValue();
            if (preg_match('/^[I,V]+\./', $cellValue)) {
                $sectionRows[] = $row->getRowIndex();
            }
        }

        foreach ($sectionRows as $rowIndex) {
            $sheet->mergeCells('A' . $rowIndex . ':' . $highestColumn . $rowIndex);
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

        // Tìm header row của bảng II (dòng ngay sau tiêu đề "II. DOANH THU THEO ...")
        $headerRowII = null;
        foreach ($sheet->getRowIterator() as $row) {
            $cellValue = $sheet->getCell('A' . $row->getRowIndex())->getValue();
            if (strpos($cellValue, 'II. DOANH THU THEO') !== false) {
                $headerRowII = $row->getRowIndex() + 1;
                break;
            }
        }

        // Các header rows cho các bảng khác (III, IV, V) dựa trên từ khóa
        $headerRows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellValue = $sheet->getCell('A' . $row->getRowIndex())->getValue();
            if (in_array($cellValue, ['STT', 'Danh mục'])) {
                $headerRows[] = $row->getRowIndex();
            }
        }

        // Thêm header row của bảng II vào danh sách
        if ($headerRowII) {
            $headerRows[] = $headerRowII;
        }

        // Tô xanh các header rows, chỉ tô các ô có nội dung
        foreach ($headerRows as $rowIndex) {
            $lastColWithContent = 'A';
            // Tìm cột cuối cùng có nội dung trong dòng này
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $val = $sheet->getCell($col . $rowIndex)->getValue();
                if ($val !== null && $val !== '') {
                    $lastColWithContent = $col;
                }
            }
            // Chỉ tô từ A đến cột có nội dung cuối cùng
            $sheet->getStyle('A' . $rowIndex . ':' . $lastColWithContent . $rowIndex)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '34495E'],
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
            'day' => 'Ngày',
            'week' => 'Tuần',
            'month' => 'Tháng',
            'year' => 'Năm'
        ];
        return $labels[$period] ?? $period;
    }

    private function getPeriodName($period)
    {
        $names = [
            'day' => 'Ngày',
            'week' => 'Tuần',
            'month' => 'Tháng',
            'year' => 'Năm'
        ];
        return $names[$period] ?? $period;
    }

    private function getRevenueLabel($period)
    {
        $labels = [
            'day' => 'Doanh thu hôm nay',
            'week' => 'Doanh thu tuần này',
            'month' => 'Doanh thu tháng này',
            'year' => 'Doanh thu năm nay'
        ];
        return $labels[$period] ?? 'Doanh thu trong kỳ';
    }

    private function getGrowthLabel($period)
    {
        $labels = [
            'day' => 'Tăng trưởng so với hôm qua',
            'week' => 'Tăng trưởng so với tuần trước',
            'month' => 'Tăng trưởng so với tháng trước',
            'year' => 'Tăng trưởng so với năm ngoái'
        ];
        return $labels[$period] ?? 'Tăng trưởng so với kỳ trước';
    }
}
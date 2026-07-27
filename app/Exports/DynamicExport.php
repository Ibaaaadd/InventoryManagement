<?php

namespace App\Exports;

use App\Support\ExportableFieldsConfig;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DynamicExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Collection $data;
    protected array $fields;
    protected string $model;

    public function __construct(Collection $data, array $fields, string $model)
    {
        $this->data = $data;
        $this->fields = $fields;
        $this->model = $model;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $config = ExportableFieldsConfig::getFieldsForModel($this->model);
        $headings = [];

        foreach ($this->fields as $field) {
            $headings[] = $config[$field]['label'] ?? $field;
        }

        return $headings;
    }

    public function map($row): array
    {
        $mapped = [];

        foreach ($this->fields as $field) {
            if ($field === 'role' && $this->model === 'user') {
                $mapped[] = $row->role?->name ?? '';
            } elseif ($field === 'is_active' && $this->model === 'user') {
                $mapped[] = $row->is_active ? 'Aktif' : 'Tidak Aktif';
            } elseif ($field === 'created_at') {
                $mapped[] = $row->created_at?->format('Y-m-d H:i:s') ?? '';
            } else {
                $mapped[] = $row->$field ?? '';
            }
        }

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F81BD']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);
        
        $sheet->getRowDimension(1)->setRowHeight(30);

        if ($highestRow > 1) {
            $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            
            // Zebra striping
            for ($row = 2; $row <= $highestRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF2F2F2']
                        ]
                    ]);
                }
            }
        }

        return [];
    }
}

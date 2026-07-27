<?php

namespace App\Exports;

use App\Support\ExportableFieldsConfig;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DynamicExport implements FromCollection, WithHeadings, WithMapping
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
}

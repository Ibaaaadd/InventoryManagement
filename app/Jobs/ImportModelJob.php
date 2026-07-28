<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\ExportImportJob;
use App\Models\Item;
use App\Models\Role;
use App\Models\User;
use App\Models\StockMutation;
use App\Support\ItemCodeGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ExportImportJob $jobRecord;

    protected array $errors = [];

    protected int $processedRows = 0;

    protected int $failedRows = 0;

    public function __construct(
        protected string $jobId,
        protected string $model,
        protected string $filePath,
        protected array $mapping
    ) {}

    public function handle(): void
    {
        $this->jobRecord = ExportImportJob::findOrFail($this->jobId);

        try {
            $this->jobRecord->update(['status' => 'processing']);

            $fullPath = Storage::path($this->filePath);
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                throw new \Exception('File is empty');
            }

            $headers = array_shift($rows);
            $totalRows = count($rows);

            $this->jobRecord->update(['total_rows' => $totalRows]);

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    $data = $this->mapRowData($headers, $row);

                    if (empty(array_filter($data))) {
                        continue;
                    }

                    $data = $this->processData($data);

                    $validation = $this->validateRow($data);

                    if ($validation->fails()) {
                        $this->errors[] = [
                            'row' => $rowNumber,
                            'errors' => $validation->errors()->toArray(),
                        ];
                        $this->failedRows++;

                        continue;
                    }

                    $this->insertRow($data);
                    $this->processedRows++;

                } catch (\Exception $e) {
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'errors' => ['general' => [$e->getMessage()]],
                    ];
                    $this->failedRows++;
                }
            }

            $this->jobRecord->update([
                'status' => 'completed',
                'processed_rows' => $this->processedRows,
                'failed_rows' => $this->failedRows,
                'error_log' => $this->errors,
            ]);

        } catch (\Exception $e) {
            Log::error('Import job failed', [
                'job_id' => $this->jobId,
                'model' => $this->model,
                'error' => $e->getMessage(),
            ]);

            $this->jobRecord->update([
                'status' => 'failed',
                'error_log' => [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            ]);
        }
    }

    protected function mapRowData(array $headers, array $row): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            if (isset($this->mapping[$header]) && $this->mapping[$header]) {
                $fieldKey = $this->mapping[$header];
                $data[$fieldKey] = $row[$index] ?? null;
            }
        }

        return $data;
    }

    protected function processData(array $data): array
    {
        if ($this->model === 'user') {
            if (isset($data['role'])) {
                $roleName = $data['role'];
                $role = Role::where('name', $roleName)->first();

                if (! $role) {
                    throw new \Exception("Role tidak ditemukan: {$roleName}");
                }

                $data['role_id'] = $role->id;
                unset($data['role']);
            }

            if (empty($data['password'])) {
                $data['password'] = Str::random(10);
            }

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            if (isset($data['is_active'])) {
                $value = strtolower(trim($data['is_active']));
                $data['is_active'] = in_array($value, ['1', 'true', 'yes', 'ya', 'aktif']);
            }
        }

        if ($this->model === 'category') {
            if (isset($data['code'])) {
                $data['code'] = strtoupper($data['code']);
            }
        }

        if ($this->model === 'item') {
            if (isset($data['category'])) {
                $categoryName = $data['category'];
                $category = Category::where('name', $categoryName)->first();

                if (! $category) {
                    throw new \Exception("Kategori tidak ditemukan: {$categoryName}");
                }

                $data['category_id'] = $category->id;
                unset($data['category']);
            }

            unset($data['sku']);

            if (! isset($data['category_id']) || empty($data['category_id'])) {
                throw new \Exception('Category ID diperlukan untuk generate SKU');
            }

            $data['sku'] = ItemCodeGenerator::generate($data['category_id']);

            if (isset($data['metadata']) && is_string($data['metadata'])) {
                $decoded = json_decode($data['metadata'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('Metadata tidak valid, diabaikan untuk row ini', [
                        'metadata' => $data['metadata'],
                    ]);
                    $data['metadata'] = null;
                } else {
                    $data['metadata'] = $decoded;
                }
            }

            if (isset($data['is_active'])) {
                $value = strtolower(trim($data['is_active']));
                $data['is_active'] = in_array($value, ['1', 'true', 'yes', 'ya', 'aktif']);
            } else {
                $data['is_active'] = true;
            }
        }

        if ($this->model === 'stock-mutation') {
            if (isset($data['item_name']) || isset($data['item_sku'])) {
                $itemQuery = Item::query();
                
                if (isset($data['item_sku'])) {
                    $itemQuery->where('sku', $data['item_sku']);
                } elseif (isset($data['item_name'])) {
                    $itemQuery->where('name', $data['item_name']);
                }
                
                $item = $itemQuery->first();
                
                if (!$item) {
                    $identifier = $data['item_sku'] ?? $data['item_name'];
                    throw new \Exception("Item tidak ditemukan: {$identifier}");
                }
                
                $data['item_id'] = $item->id;
                unset($data['item_name'], $data['item_sku']);
            }

            if (isset($data['type'])) {
                $data['type'] = strtolower($data['type']);
            }

            $data['user_id'] = $this->jobRecord->user_id;
            $data['status'] = 'pending';
            $data['attachment_path'] = null;

            if (isset($data['transaction_date'])) {
                $data['transaction_date'] = date('Y-m-d', strtotime($data['transaction_date']));
            }
        }

        return $data;
    }

    protected function validateRow(array $data): \Illuminate\Validation\Validator
    {
        $rules = match ($this->model) {
            'role' => [
                'name' => 'required|string|min:3|unique:roles,name',
                'description' => 'nullable|string',
            ],
            'user' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'role_id' => 'required|exists:roles,id',
                'is_active' => 'sometimes|boolean',
            ],
            'category' => [
                'name' => 'required|string|unique:categories,name',
                'code' => 'required|string|unique:categories,code',
            ],
            'item' => [
                'name' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'metadata' => 'nullable|array',
                'is_active' => 'sometimes|boolean',
            ],
            'stock-mutation' => [
                'item_id' => 'required|exists:items,id',
                'type' => 'required|in:in,out',
                'quantity' => 'required|integer|min:1',
                'transaction_date' => 'required|date',
                'notes' => 'nullable|string',
            ],
            default => [],
        };

        return Validator::make($data, $rules);
    }

    protected function insertRow(array $data): void
    {
        match ($this->model) {
            'role' => Role::create($data),
            'user' => User::create($data),
            'category' => Category::create($data),
            'item' => Item::create($data),
            'stock-mutation' => StockMutation::create($data),
            default => null,
        };
    }
}

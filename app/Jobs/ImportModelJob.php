<?php

namespace App\Jobs;

use App\Models\ExportImportJob;
use App\Models\Role;
use App\Models\User;
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
                
                if (!$role) {
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

        return $data;
    }

    protected function validateRow(array $data): \Illuminate\Validation\Validator
    {
        $rules = match($this->model) {
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
            default => [],
        };

        return Validator::make($data, $rules);
    }

    protected function insertRow(array $data): void
    {
        match($this->model) {
            'role' => Role::create($data),
            'user' => User::create($data),
            default => null,
        };
    }
}

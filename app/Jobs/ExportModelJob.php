<?php

namespace App\Jobs;

use App\Exports\DynamicExport;
use App\Models\ExportImportJob;
use App\Models\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExportModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $jobId,
        protected string $model,
        protected array $fields
    ) {}

    public function handle(): void
    {
        $job = ExportImportJob::findOrFail($this->jobId);

        try {
            $job->update(['status' => 'processing']);

            $data = $this->fetchData();
            $totalRows = $data->count();

            $filename = $this->model . '_export_' . now()->format('YmdHis') . '.xlsx';
            $filePath = 'exports/' . $filename;

            Excel::store(
                new DynamicExport($data, $this->fields, $this->model),
                $filePath
            );

            $job->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'total_rows' => $totalRows,
                'processed_rows' => $totalRows,
            ]);

        } catch (\Exception $e) {
            Log::error('Export job failed', [
                'job_id' => $this->jobId,
                'model' => $this->model,
                'error' => $e->getMessage(),
            ]);

            $job->update([
                'status' => 'failed',
                'error_log' => [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            ]);
        }
    }

    protected function fetchData()
    {
        return match($this->model) {
            'role' => $this->fetchRoles(),
            'user' => $this->fetchUsers(),
            default => collect([]),
        };
    }

    protected function fetchRoles()
    {
        $query = Role::query();

        if (in_array('users_count', $this->fields)) {
            $query->withCount('users');
        }

        return $query->get();
    }

    protected function fetchUsers()
    {
        $query = User::query();

        if (in_array('role', $this->fields)) {
            $query->with('role');
        }

        return $query->get();
    }
}

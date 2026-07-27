<?php

namespace App\Http\Controllers;

use App\Jobs\ExportModelJob;
use App\Models\ExportImportJob;
use App\Support\ExportableFieldsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function exportModel(Request $request, string $model)
    {
        if (!in_array($model, ['role', 'user'])) {
            return response()->json(['message' => 'Invalid model'], 400);
        }

        $availableFields = ExportableFieldsConfig::getExportableFields($model);
        
        $validated = $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*' => 'required|string',
        ]);

        foreach ($validated['fields'] as $field) {
            if (!isset($availableFields[$field])) {
                return response()->json([
                    'message' => "Field '{$field}' is not exportable for model '{$model}'"
                ], 400);
            }
        }

        $job = ExportImportJob::create([
            'user_id' => auth()->id(),
            'type' => 'export',
            'model' => $model,
            'status' => 'pending',
        ]);

        ExportModelJob::dispatch($job->id, $model, $validated['fields']);

        return response()->json([
            'job_id' => $job->id,
            'message' => 'Export job created successfully'
        ], 201);
    }

    public function getExportableFields(string $model)
    {
        if (!in_array($model, ['role', 'user'])) {
            return response()->json(['message' => 'Invalid model'], 400);
        }

        $fields = ExportableFieldsConfig::getExportableFields($model);

        return response()->json(['fields' => $fields]);
    }

    public function getJobs(Request $request)
    {
        $query = ExportImportJob::with('user')
            ->orderBy('created_at', 'desc');

        if (auth()->user()->role->name !== 'Administrator') {
            $query->where('user_id', auth()->id());
        }

        $jobs = $query->paginate(15);

        return response()->json($jobs);
    }

    public function getJob(string $id)
    {
        $job = ExportImportJob::with('user')->findOrFail($id);

        if (auth()->user()->role->name !== 'Administrator' && $job->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($job);
    }

    public function downloadExport(string $id)
    {
        $job = ExportImportJob::findOrFail($id);

        if (auth()->user()->role->name !== 'Administrator' && $job->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($job->type !== 'export') {
            return response()->json(['message' => 'Job is not an export'], 400);
        }

        if ($job->status !== 'completed') {
            return response()->json(['message' => 'Export not completed yet'], 400);
        }

        if (!$job->file_path || !Storage::exists($job->file_path)) {
            return response()->json(['message' => 'Export file not found'], 404);
        }

        return Storage::download($job->file_path);
    }
}

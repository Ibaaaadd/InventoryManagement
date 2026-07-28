<?php

namespace App\Http\Controllers;

use App\Jobs\ImportModelJob;
use App\Models\ExportImportJob;
use App\Support\ExportableFieldsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function preview(Request $request, string $model)
    {
        if (!in_array($model, ['role', 'user', 'category', 'item', 'stock-mutation'])) {
            return response()->json(['message' => 'Invalid model'], 400);
        }

        if ($model === 'stock-mutation' && !auth()->user()->approver_id) {
            return response()->json([
                'message' => 'Anda belum memiliki approver yang ditunjuk. Hubungi administrator untuk mengatur approver Anda terlebih dahulu.'
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $tempPath = 'temp/' . uniqid() . '_' . $file->getClientOriginalName();
        $file->storeAs('', $tempPath);

        try {
            $fullPath = Storage::path($tempPath);
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $headers = [];
            
            foreach ($worksheet->getRowIterator(1, 1) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                foreach ($cellIterator as $cell) {
                    $value = $cell->getValue();
                    if ($value) {
                        $headers[] = trim($value);
                    }
                }
            }

            if (empty($headers)) {
                Storage::delete($tempPath);
                return response()->json(['message' => 'No headers found in file'], 400);
            }

            $autoMapping = $this->autoMatchHeaders($model, $headers);
            $availableFields = ExportableFieldsConfig::getImportableFields($model);

            return response()->json([
                'temp_file_path' => $tempPath,
                'headers' => $headers,
                'auto_mapping' => $autoMapping,
                'available_fields' => $availableFields,
            ]);

        } catch (\Exception $e) {
            Storage::delete($tempPath);
            return response()->json(['message' => 'Failed to read file: ' . $e->getMessage()], 400);
        }
    }

    public function confirm(Request $request, string $model)
    {
        if (!in_array($model, ['role', 'user', 'category', 'item', 'stock-mutation'])) {
            return response()->json(['message' => 'Invalid model'], 400);
        }

        if ($model === 'stock-mutation' && !auth()->user()->approver_id) {
            return response()->json([
                'message' => 'Anda belum memiliki approver yang ditunjuk. Hubungi administrator untuk mengatur approver Anda terlebih dahulu.'
            ], 422);
        }

        $validated = $request->validate([
            'temp_file_path' => 'required|string',
            'mapping' => 'required|array',
        ]);

        if (!Storage::exists($validated['temp_file_path'])) {
            return response()->json(['message' => 'Temporary file not found'], 404);
        }

        $availableFields = ExportableFieldsConfig::getImportableFields($model);
        foreach ($validated['mapping'] as $excelHeader => $fieldKey) {
            if ($fieldKey && !isset($availableFields[$fieldKey])) {
                return response()->json([
                    'message' => "Field '{$fieldKey}' is not importable for model '{$model}'"
                ], 400);
            }
        }

        $permanentPath = 'imports/' . uniqid() . '_' . basename($validated['temp_file_path']);
        Storage::move($validated['temp_file_path'], $permanentPath);

        $job = ExportImportJob::create([
            'user_id' => auth()->id(),
            'type' => 'import',
            'model' => $model,
            'status' => 'pending',
            'file_path' => $permanentPath,
        ]);

        ImportModelJob::dispatch($job->id, $model, $permanentPath, $validated['mapping']);

        return response()->json([
            'job_id' => $job->id,
            'message' => 'Import job created successfully'
        ], 201);
    }

    public function getImportableFields(string $model)
    {
        if (!in_array($model, ['role', 'user', 'category', 'item'])) {
            return response()->json(['message' => 'Invalid model'], 400);
        }

        $fields = ExportableFieldsConfig::getImportableFields($model);

        return response()->json(['fields' => $fields]);
    }

    private function autoMatchHeaders(string $model, array $headers): array
    {
        $mapping = [];
        $importableFields = ExportableFieldsConfig::getImportableFields($model);

        foreach ($headers as $header) {
            $fieldKey = ExportableFieldsConfig::findFieldByAlias($model, $header);
            
            if ($fieldKey && isset($importableFields[$fieldKey])) {
                $mapping[$header] = $fieldKey;
            } else {
                $mapping[$header] = null;
            }
        }

        return $mapping;
    }
}

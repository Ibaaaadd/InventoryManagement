<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\StockMutationController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class)->middleware('administrator');
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('stock-mutations', StockMutationController::class);
    Route::post('stock-mutations/{stockMutation}/approve', [StockMutationController::class, 'approve']);
    Route::post('stock-mutations/{stockMutation}/reject', [StockMutationController::class, 'reject']);
    Route::get('stock-mutations/{stockMutation}/attachment/download', [StockMutationController::class, 'downloadAttachment']);
    Route::get('stock-mutations/{stockMutation}/attachment/view', [StockMutationController::class, 'viewAttachment']);
    Route::get('audits', [AuditController::class, 'index']);
    
    Route::post('export/{model}', [ExportController::class, 'exportModel']);
    Route::get('export/{model}/fields', [ExportController::class, 'getExportableFields']);
    
    Route::post('import/{model}/preview', [ImportController::class, 'preview']);
    Route::post('import/{model}/confirm', [ImportController::class, 'confirm']);
    Route::get('import/{model}/fields', [ImportController::class, 'getImportableFields']);
    
    Route::get('export-import-jobs', [ExportController::class, 'getJobs']);
    Route::get('export-import-jobs/{id}', [ExportController::class, 'getJob']);
    Route::get('export-import-jobs/{id}/download', [ExportController::class, 'downloadExport']);
});

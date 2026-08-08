<?php

use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PublicFormController;
use Illuminate\Support\Facades\Route;

// Dashboard & Form Builder Routes
Route::get('/', [FormBuilderController::class, 'index'])->name('forms.index');
Route::get('/forms/create', [FormBuilderController::class, 'create'])->name('forms.create');
Route::get('/forms/{form}/edit', [FormBuilderController::class, 'edit'])->name('forms.edit');
Route::get('/forms/{form}/submissions', [FormBuilderController::class, 'showSubmissions'])->name('forms.submissions');
Route::get('/forms/{form}/export/csv', [FormBuilderController::class, 'exportCsv'])->name('forms.export.csv');
Route::get('/forms/{form}/versions', [FormBuilderController::class, 'showVersions'])->name('forms.versions');
Route::post('/forms/{form}/versions/{version}/rollback', [FormBuilderController::class, 'rollbackVersion'])->name('forms.versions.rollback');
Route::get('/forms/{form}/analytics', [FormBuilderController::class, 'showAnalytics'])->name('forms.analytics');

// Document Import Routes
Route::get('/import', [ImportController::class, 'showUpload'])->name('import.upload');
Route::post('/import', [ImportController::class, 'upload'])->name('import.upload.post');
Route::post('/import/demo-excel', [ImportController::class, 'loadDemoExcel'])->name('import.demo_excel');
Route::get('/import/download-sample-excel', [ImportController::class, 'downloadSampleExcel'])->name('import.download_sample');
Route::get('/import/{importJob}/preview', [ImportController::class, 'showPreview'])->name('import.preview');
Route::post('/import/{importJob}/commit', [ImportController::class, 'commit'])->name('import.commit');

// Public Form Respondent Fill Routes
Route::get('/f/{slug}', [PublicFormController::class, 'show'])->name('forms.public');
Route::post('/f/{slug}', [PublicFormController::class, 'submit'])->name('forms.public.submit');

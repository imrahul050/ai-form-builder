<?php

use App\Http\Controllers\Api\FormApiController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/forms/{slug}', [FormApiController::class, 'getSchema']);
Route::post('/v1/forms/{slug}/submit', [FormApiController::class, 'submitApi']);

<?php

use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\GenerateDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/generate-word/{id}', [ExportController::class, 'generateWord']);

Route::get('/bookings/{id}/contract', [GenerateDocumentController::class, 'generateContractPdf']);
Route::get('/bookings-contract/{id}', [GenerateDocumentController::class, 'generateContractView']);

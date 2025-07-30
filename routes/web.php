<?php

use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\Api\V1\GoogleController;
use App\Http\Controllers\GenerateDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/generate-word/{id}', [ExportController::class, 'generateWord']);

Route::get('/bookings/{id}/contract', [GenerateDocumentController::class, 'generateContractPdf']);
Route::get('/bookings-contract/{id}', [GenerateDocumentController::class, 'generateContractView']);


Route::get('auth/google', [GoogleController::class, 'googleLogin'])->name('google.auth');
Route::get('auth/google-callback', [GoogleController::class, 'googleLoginCallback'])->name('auth.google-callback');

Route::get('/test', function () {
    return view('test');
});

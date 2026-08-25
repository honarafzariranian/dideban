<?php
use App\Http\Controllers\Api\CRM\CustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('crm')->name('crm.')->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers-stats', [CustomerController::class, 'stats']);
});

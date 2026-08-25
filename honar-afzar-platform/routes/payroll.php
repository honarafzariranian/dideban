<?php
use App\Http\Controllers\Api\Payroll\PayrollController;
use Illuminate\Support\Facades\Route;

Route::prefix('payroll')->name('payroll.')->group(function () {
    Route::apiResource('payrolls', PayrollController::class);
    Route::post('payrolls/{id}/approve', [PayrollController::class, 'approve']);
});

<?php
use App\Http\Controllers\Api\Correspondence\CorrespondenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('correspondence')->name('correspondence.')->group(function () {
    Route::apiResource('letters', CorrespondenceController::class);
    Route::post('letters/{id}/approve', [CorrespondenceController::class, 'approve']);
});

<?php

use App\Http\Controllers\Api\Accounting\JournalEntryController;
use App\Http\Controllers\Api\Accounting\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Accounting API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('accounting')->name('accounting.')->group(function () {
    
    // Journal Entries
    Route::apiResource('journal-entries', JournalEntryController::class);
    Route::post('journal-entries/{id}/submit', [JournalEntryController::class, 'submit']);
    Route::post('journal-entries/{id}/approve', [JournalEntryController::class, 'approve']);
    Route::post('journal-entries/{id}/post', [JournalEntryController::class, 'post']);
    Route::post('journal-entries/{id}/reverse', [JournalEntryController::class, 'reverse']);
    Route::get('trial-balance', [JournalEntryController::class, 'trialBalance']);
    
    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/{id}/approve', [InvoiceController::class, 'approve']);
    Route::get('invoices-stats', [InvoiceController::class, 'stats']);
});

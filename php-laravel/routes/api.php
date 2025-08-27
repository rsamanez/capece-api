<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\EvidenceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::get('/tracking/{trackingNumber}', [TrackingController::class, 'show']);
    
    // Evidence routes
    Route::get('/tracking/{trackingNumber}/evidence', [EvidenceController::class, 'index']);
    Route::post('/tracking/{trackingNumber}/evidence', [EvidenceController::class, 'store']);
    Route::delete('/tracking/{trackingNumber}/evidence/{evidenceId}', [EvidenceController::class, 'destroy']);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString()
    ]);
});

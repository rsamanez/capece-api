<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Get tracking information for a package
     */
    public function show(string $trackingNumber): JsonResponse
    {
        // Validate tracking number format
        if (!$this->isValidTrackingNumber($trackingNumber)) {
            return response()->json([
                'error' => 'invalid_tracking_number',
                'message' => 'Invalid tracking number format',
                'trackingNumber' => $trackingNumber
            ], 400);
        }

        // Get tracking information
        $trackingInfo = $this->trackingService->getTrackingInfo($trackingNumber);

        if (!$trackingInfo) {
            return response()->json([
                'error' => 'tracking_not_found',
                'message' => 'Tracking number not found',
                'trackingNumber' => $trackingNumber
            ], 404);
        }

        return response()->json($trackingInfo);
    }

    /**
     * Validate tracking number format
     */
    private function isValidTrackingNumber(string $trackingNumber): bool
    {
        return preg_match('/^[A-Z0-9]{10,20}$/', $trackingNumber);
    }
}
